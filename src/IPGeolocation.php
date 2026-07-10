<?php

    namespace Wixnit\Location;

    /**
     * Resolves an IP address to an approximate Address using ip-api.com's free
     * JSON endpoint (no API key required for non-commercial use, rate-limited to
     * 45 requests/minute per source IP: https://ip-api.com/docs/api:json).
     * A different provider can be swapped in via setEndpoint(), as long as it
     * accepts the IP as a URL suffix and returns a compatible JSON shape.
     */
    class IPGeolocation
    {
        private static string $endpoint = "http://ip-api.com/json/";
        private static string $userAgent = "wixnit-location/1.0";

        public static function setEndpoint(string $endpoint): void
        {
            self::$endpoint = $endpoint;
        }

        //used to obtain the location of a user and all
        public static function getAddress($ip) : Address
        {
            $address = new Address();

            if(!self::isPublicIp($ip))
            {
                return $address;
            }

            $response = self::httpGet(self::$endpoint.urlencode($ip)."?fields=status,countryCode,region,regionName,city");

            if($response === null)
            {
                return $address;
            }

            $data = json_decode($response);

            if($data === null || !isset($data->status) || $data->status !== "success")
            {
                return $address;
            }

            $address->city = $data->city ?? "";
            $address->state = $data->regionName ?? "";
            $address->region = $data->region ?? "";

            if(isset($data->countryCode) && $data->countryCode !== "")
            {
                $address->country = Country::ByCode($data->countryCode);
            }

            return $address;
        }

        /**
         * Reject private/reserved/loopback ranges - these can't be geolocated
         * and shouldn't be sent to a third-party lookup service.
         */
        private static function isPublicIp($ip): bool
        {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        private static function httpGet(string $url): ?string
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

                return ($response === false || $httpCode >= 400) ? null : $response;
            }

            if(ini_get("allow_url_fopen"))
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
                return ($response === false) ? null : $response;
            }

            return null;
        }
    }
