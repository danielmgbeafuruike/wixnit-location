<?php

    namespace Wixnit\Location;

    /**
     * implimentation of several geo coding and reverse geo coding to improve the rate of geocoding success
     *
     * Note on naming: forward(Location):Address does what most geocoding APIs call
     * "reverse geocoding" (coordinates -> address), and reverse(Address):Location does
     * what most APIs call "forward geocoding" (address -> coordinates). Kept as-is since
     * Address::reverseGeocode() and LocationReference::geoCode() already depend on it.
     *
     * Default provider is OpenStreetMap's Nominatim (free, no API key required, subject
     * to its usage policy of ~1 request/second and a descriptive User-Agent). If a Google
     * Maps API key is configured via setGoogleApiKey(), Google is tried first and Nominatim
     * is used as the fallback.
     */
    class GeoCoding
    {
        private static ?string $googleApiKey = null;
        private static string $userAgent = "wixnit-location/1.0";

        public static function setGoogleApiKey(?string $key): void
        {
            self::$googleApiKey = $key;
        }

        public static function setUserAgent(string $userAgent): void
        {
            self::$userAgent = $userAgent;
        }

        public static function forward(Location $position) : Address
        {
            if(self::$googleApiKey !== null)
            {
                $address = self::google_geoCode($position->cordinate->latitude, $position->cordinate->longitude);
                if($address !== null)
                {
                    return $address;
                }
            }

            $address = self::nominatim_geoCode($position->cordinate->latitude, $position->cordinate->longitude);
            return $address ?? new Address();
        }

        public static function reverse(Address $address) : Location
        {
            if(self::$googleApiKey !== null)
            {
                $location = self::google_reverseGeocode($address->houseNumber, $address->street, $address->city, $address->state, $address->region, $address->country->name);
                if($location !== null)
                {
                    return $location;
                }
            }

            $location = self::nominatim_reverseGeocode($address->houseNumber, $address->street, $address->city, $address->state, $address->region, $address->country->name);
            return $location ?? new Location();
        }



        private static function google_geoCode($lat=0.00, $long=0.00): ?Address
        {
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng="
                .urlencode($lat.",".$long)."&key=".urlencode(self::$googleApiKey);

            $data = self::httpGetJson($url);

            if($data === null || !isset($data->status) || $data->status !== "OK" || empty($data->results))
            {
                return null;
            }

            return self::addressFromGoogleResult($data->results[0]);
        }

        private static function google_reverseGeocode($housenumber, $street, $city, $state, $region, $country): ?Location
        {
            $query = trim("$housenumber $street $city $state $region $country");

            if($query === "")
            {
                return null;
            }

            $url = "https://maps.googleapis.com/maps/api/geocode/json?address="
                .urlencode($query)."&key=".urlencode(self::$googleApiKey);

            $data = self::httpGetJson($url);

            if($data === null || !isset($data->status) || $data->status !== "OK" || empty($data->results))
            {
                return null;
            }

            $result = $data->results[0];

            $location = new Location();
            $location->cordinate->latitude = (float) $result->geometry->location->lat;
            $location->cordinate->longitude = (float) $result->geometry->location->lng;
            $location->address = self::addressFromGoogleResult($result);

            return $location;
        }

        private static function addressFromGoogleResult($result): Address
        {
            $address = new Address();

            foreach($result->address_components as $component)
            {
                $types = $component->types;

                if(in_array("street_number", $types))
                {
                    $address->houseNumber = $component->long_name;
                }
                else if(in_array("route", $types))
                {
                    $address->street = $component->long_name;
                }
                else if(in_array("locality", $types))
                {
                    $address->city = $component->long_name;
                }
                else if(in_array("administrative_area_level_1", $types))
                {
                    $address->state = $component->long_name;
                }
                else if(in_array("administrative_area_level_2", $types))
                {
                    $address->region = $component->long_name;
                }
                else if(in_array("country", $types))
                {
                    $address->country = Country::ByCode($component->short_name);
                }
            }

            return $address;
        }



        /**
         * Using OpenStreetMap's Nominatim service - free, no API key required
         */
        private static function nominatim_geoCode($lat=0.00, $long=0.00): ?Address
        {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat="
                .urlencode($lat)."&lon=".urlencode($long);

            $data = self::httpGetJson($url);

            if($data === null || !isset($data->address))
            {
                return null;
            }

            return self::addressFromNominatimResult($data->address);
        }

        private static function nominatim_reverseGeocode($housenumber, $street, $city, $state, $region, $country): ?Location
        {
            $query = trim("$housenumber $street $city $state $region $country");

            if($query === "")
            {
                return null;
            }

            $url = "https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q="
                .urlencode($query);

            $data = self::httpGetJson($url);

            if($data === null || empty($data))
            {
                return null;
            }

            $result = $data[0];

            $location = new Location();
            $location->cordinate->latitude = (float) $result->lat;
            $location->cordinate->longitude = (float) $result->lon;

            if(isset($result->address))
            {
                $location->address = self::addressFromNominatimResult($result->address);
            }

            return $location;
        }

        private static function addressFromNominatimResult($components): Address
        {
            $address = new Address();

            $address->houseNumber = $components->house_number ?? "";
            $address->street = $components->road ?? "";
            $address->city = $components->city ?? $components->town ?? $components->village ?? "";
            $address->state = $components->state ?? "";
            $address->region = $components->county ?? "";

            if(isset($components->country_code))
            {
                $address->country = Country::ByCode($components->country_code);
            }

            return $address;
        }



        /**
         * Minimal HTTP GET + JSON decode helper shared by every provider above.
         * Returns null on any network, HTTP, or decode failure rather than throwing,
         * so callers can fall through to the next provider (or an empty default).
         */
        private static function httpGetJson(string $url)
        {
            $headers = ["User-Agent: ".self::$userAgent];

            if(function_exists("curl_init"))
            {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if($response === false || $httpCode >= 400)
                {
                    return null;
                }
            }
            else if(ini_get("allow_url_fopen"))
            {
                $context = stream_context_create([
                    "http" => [
                        "method" => "GET",
                        "header" => implode("\r\n", $headers),
                        "timeout" => 5,
                        "ignore_errors" => true
                    ]
                ]);

                $response = @file_get_contents($url, false, $context);

                if($response === false)
                {
                    return null;
                }
            }
            else
            {
                return null;
            }

            $decoded = json_decode($response);
            return $decoded;
        }
    }
