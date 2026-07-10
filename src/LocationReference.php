<?php

    namespace Wixnit\Location;

    use Exception;

    class LocationReference
    {
        /**
         * @param Address
         */
        public ?Address $address = null;

        /**
         * @var array<Location>
         */
        public array $boundingArea = [];

        /**
         * @param float
         * minimum radius value for calculating location value
         */
        private $minRadius = 100; //distance in meters



        function __construct(Address $address=null, $positions=[])
        {
            $this->address = $address ?? new Address();

            if(is_array($positions))
            {
                for($i = 0; $i < count($positions); $i++)
                {
                    if($positions[$i] instanceof Location)
                    {
                        $this->boundingArea[] = $positions[$i];
                    }
                }
            }
        }

        public function centerPosition(): Location
        {
            $count = count($this->boundingArea);

            if($count == 0)
            {
                return new Location();
            }
            else if($count == 1)
            {
                return $this->boundingArea[0];
            }

            $latitude = 0.0;
            $longitude = 0.0;
            $altitude = 0.0;
            $accuracy = 0.0;

            foreach($this->boundingArea as $point)
            {
                $latitude += $point->cordinate->latitude;
                $longitude += $point->cordinate->longitude;
                $altitude += $point->cordinate->altitude;
                $accuracy += $point->cordinate->accuracy;
            }

            $ret = new Location();
            $ret->cordinate->latitude = $latitude / $count;
            $ret->cordinate->longitude = $longitude / $count;
            $ret->cordinate->altitude = $altitude / $count;
            $ret->cordinate->accuracy = $accuracy / $count;

            return $ret;
        }

        /**
         * Checks whether $position falls within this LocationReference's bounding area.
         * - 0 points: always false, there is no area to be "in"
         * - 1 point: true if $position is within $minRadius metres of that point
         * - 2 points: treated as opposite corners of a bounding box
         * - 3+ points: treated as a polygon (ray casting point-in-polygon test)
         */
        public function isInLocation(Location $position): bool
        {
            $count = count($this->boundingArea);

            if($count == 0)
            {
                return false;
            }
            else if($count == 1)
            {
                return $this->boundingArea[0]->cordinate->distance($position->cordinate) <= $this->minRadius;
            }
            else if($count == 2)
            {
                $lat1 = $this->boundingArea[0]->cordinate->latitude;
                $lat2 = $this->boundingArea[1]->cordinate->latitude;
                $lon1 = $this->boundingArea[0]->cordinate->longitude;
                $lon2 = $this->boundingArea[1]->cordinate->longitude;

                $minLat = min($lat1, $lat2);
                $maxLat = max($lat1, $lat2);
                $minLon = min($lon1, $lon2);
                $maxLon = max($lon1, $lon2);

                return ($position->cordinate->latitude >= $minLat && $position->cordinate->latitude <= $maxLat
                    && $position->cordinate->longitude >= $minLon && $position->cordinate->longitude <= $maxLon);
            }
            else
            {
                return $this->pointInPolygon($position);
            }
        }

        private function pointInPolygon(Location $position): bool
        {
            $inside = false;
            $x = $position->cordinate->longitude;
            $y = $position->cordinate->latitude;
            $vertexCount = count($this->boundingArea);

            for($i = 0, $j = $vertexCount - 1; $i < $vertexCount; $j = $i++)
            {
                $xi = $this->boundingArea[$i]->cordinate->longitude;
                $yi = $this->boundingArea[$i]->cordinate->latitude;
                $xj = $this->boundingArea[$j]->cordinate->longitude;
                $yj = $this->boundingArea[$j]->cordinate->latitude;

                $intersects = (($yi > $y) != ($yj > $y))
                    && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

                if($intersects)
                {
                    $inside = !$inside;
                }
            }

            return $inside;
        }

        public function distance (LocationReference $location) : float
        {
            return $this->centerPosition()->cordinate->distance($location->centerPosition()->cordinate);
        }

        public function direction (LocationReference $location) : XDirection
        {
            return LocationReference::GetDirection($this->centerPosition(), $location->centerPosition());
        }



        public function geoCode() : Address
        {
            return GeoCoding::forward($this->centerPosition());
        }

        public function reverseGeocode() : Location
        {
            return $this->address->reverseGeocode();
        }



        public function toString() : string
        {
            $ret = [
                "address"=>[
                    "country"=>$this->address->country->code,
                    "state"=>$this->address->state,
                    "city"=>$this->address->city,
                    "street"=>$this->address->street,
                    "region"=>$this->address->region,
                    "houseNumber"=>$this->address->houseNumber
                ],
                "boundingArea"=>array_map(function(Location $point) {
                    return [
                        "cordinate"=>[
                            "latitude"=>$point->cordinate->latitude,
                            "longitude"=>$point->cordinate->longitude,
                            "altitude"=>$point->cordinate->altitude,
                            "accuracy"=>$point->cordinate->accuracy
                        ]
                    ];
                }, $this->boundingArea)
            ];
            return json_encode($ret);
        }

        public static function FromString($string) : LocationReference
        {
            $ret = LocationReference::NoWhere();

            try
            {
                $d = is_string($string) ? json_decode($string) : $string;

                if($d === null)
                {
                    return $ret;
                }

                if(isset($d->address))
                {
                    $addr = $d->address;

                    if(isset($addr->state) && is_string($addr->state))
                    {
                        $ret->address->state = $addr->state;
                    }
                    if(isset($addr->city) && is_string($addr->city))
                    {
                        $ret->address->city = $addr->city;
                    }
                    if(isset($addr->country))
                    {
                        if(is_string($addr->country))
                        {
                            $ret->address->country = Country::ByCode($addr->country);
                        }
                        else if(is_object($addr->country) && isset($addr->country->code))
                        {
                            $ret->address->country = Country::ByCode($addr->country->code);
                        }
                    }
                    if(isset($addr->region) && is_string($addr->region))
                    {
                        $ret->address->region = $addr->region;
                    }
                    if(isset($addr->houseNumber) && is_string($addr->houseNumber))
                    {
                        $ret->address->houseNumber = $addr->houseNumber;
                    }
                    if(isset($addr->street) && is_string($addr->street))
                    {
                        $ret->address->street = $addr->street;
                    }
                }

                if(isset($d->boundingArea) && is_array($d->boundingArea))
                {
                    foreach($d->boundingArea as $point)
                    {
                        $location = new Location();

                        if(isset($point->cordinate))
                        {
                            $c = $point->cordinate;
                            $location->cordinate->latitude = isset($c->latitude) ? doubleval($c->latitude) : 0.00;
                            $location->cordinate->longitude = isset($c->longitude) ? doubleval($c->longitude) : 0.00;
                            $location->cordinate->altitude = isset($c->altitude) ? doubleval($c->altitude) : 0.00;
                            $location->cordinate->accuracy = isset($c->accuracy) ? doubleval($c->accuracy) : 0.00;
                        }

                        $ret->boundingArea[] = $location;
                    }
                }
            }
            catch(Exception $e)
            {
                throw(new Exception("tying to create location from invalid location object", 103, $e));
            }
            return $ret;
        }


        /**
         * @return LocationReference
         * fully constructs a Location object that points nowhere
         */
        public static function NoWhere() : LocationReference
        {
            $ret = new LocationReference();
            $ret->address = new Address();
            $ret->boundingArea = [];
            return $ret;
        }


        /**
         * Computes the compass bearing (great-circle initial bearing) from $start to $end.
         */
        public static function GetDirection(Location $start, Location $end): XDirection
        {
            $lat1 = deg2rad($start->cordinate->latitude);
            $lat2 = deg2rad($end->cordinate->latitude);
            $deltaLon = deg2rad($end->cordinate->longitude - $start->cordinate->longitude);

            $y = sin($deltaLon) * cos($lat2);
            $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($deltaLon);

            $bearing = fmod((rad2deg(atan2($y, $x)) + 360), 360);

            return new XDirection($bearing);
        }
    }
