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
         * @var array
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
            if(count($this->boundingArea) == 0)
            {
                return new Location();
            }
            else if(count($this->boundingArea) == 1)
            {
                return $this->boundingArea[0];
            }
            else if(count($this->boundingArea) == 2)
            {
                $ret = new Location();
                $ret->latitude = (($this->boundingArea[0]->latitude + $this->boundingArea[1]->latitude) / 2);
                $ret->longitude = (($this->boundingArea[0]->longitude + $this->boundingArea[1]->longitude) / 2);
                $ret->latitude = (($this->boundingArea[0]->altitude + $this->boundingArea[1]->latitude) / 2);
                $ret->accuracy = (($this->boundingArea[0]->accuracy + $this->boundingArea[1]->accuracy) / 2);

                return $ret;
            }
            else
            {
                
            }
            return new Location();
        }

        public function isInLocation(Location $position): bool
        {
            if(count($this->boundingArea) == 0)
            {
                return false;
            }
            else if(count($this->boundingArea) == 1)
            {
                return $this->boundingArea[0];
            }
            else if(count($this->boundingArea) == 2)
            {
                return false;
            }
            else
            {
                
            }
            return false;
        }

        public function distance (LocationReference $location) : float
        {
            return $this->centerPosition()->Distance($location->centerPosition());
        }

        public function direction (LocationReference $location) : XDirection
        {
            return LocationReference::GetDirection($this->centerPosition(), $location->centerPosition());
        }



        public function geoCode() : Address
        {
            return Geocoding::forward($this->centerPosition());
        }

        public function reverseGeocode() : Location
        {
            return $this->address->reverseGeocode();
        }



        public function toString() : string
        {
            $ret = [
                "address"=>[
                    "country"=>$this->address->country,
                    "state"=>$this->address->state,
                    "city"=>$this->address->city,
                    "street"=>$this->address->street,
                    "region"=>$this->address->region,
                    "housenumber"=>$this->address->houseNumber
                ],
                "boundingArea"=>$this->boundingArea
            ];
            return json_encode($ret);
        }

        public static function FromString($string) : LocationReference
        {
            $ret = LocationReference::noWhere();
            try{
                $loc = null;

                if(is_string($string))
                {
                    $d = json_decode($string);

                    if(isset($d->address))
                    {
                        if(isset($d->address->state))
                        {
                            if(is_string($d->address->state))
                            {
                                $ret->address->state = new $d->address->state;
                            }
                            else if($d->address->state instanceof State)
                            {
                                if(isset($d->address->state->Id))
                                {
                                    $ret->address->state = $d->address->state->name;
                                }
                            }
                        }
                        if(isset($d->address->city))
                        {
                            if(is_string($d->address->city))
                            {
                                $ret->address->city = $d->address->city;
                            }
                            else if(is_object($d->address->city))
                            {
                                if(isset($d->address->city->id))
                                {
                                    $ret->address->city = $d->address->city->name;
                                }
                            }
                        }
                        if(isset($d->address->country))
                        {
                            if(is_string($d->address->country))
                            {
                                $ret->address->country = $d->address->country;
                            }
                            else if($d->address->country instanceof Country)
                            {
                                $ret->address->country = $d->address->country->code;
                            }
                        }
                        if(isset($d->address->region))
                        {
                            $ret->address->region = $d->address->region;
                        }
                        if(isset($d->address->houseNumber))
                        {
                            $ret->address->houseNumber = $d->address->houseNumber;
                        }
                        if(isset($d->address->street))
                        {
                            $ret->address->street = $d->address->street;
                        }
                    }
                    if(isset($d->boundingArea))
                    {
                        if(is_array($d->boundingArea))
                        {
                            for($i = 0; $i < count($d->boundingArea); $i++)
                            {
                                $ret = new Location();
                                $ret->latitude = (($d->boundingArea[0]->latitude + $d->boundingArea[1]->latitude) / 2);
                                $ret->longitude = (($d->boundingArea[0]->longitude + $d->boundingArea[1]->longitude) / 2);
                                $ret->latitude = (($d->boundingArea[0]->altitude + $d->boundingArea[1]->latitude) / 2);
                                $ret->accuracy = (($d->boundingArea[0]->accuracy + $d->boundingArea[1]->accuracy) / 2);
                                $d->boundingArea[] = $ret;
                            }
                        }
                    }
                }
                else if(is_object($string))
                {
                    if(isset($string->address))
                    {
                        if(isset($string->address->state))
                        {
                            if(is_string($string->address->state))
                            {
                                $ret->address->state = new State($string->address->state);
                            }
                            else if(is_object($string->address->state))
                            {
                                if(isset($string->address->state->id))
                                {
                                    $ret->address->State = new State($string->address->state->id);
                                }
                            }
                        }
                        if(isset($string->address->city))
                        {
                            if(is_string($string->address->city))
                            {
                                $ret->address->city = new State($string->address->city);
                            }
                            else if(is_object($string->address->City))
                            {
                                if(isset($string->address->City->Id))
                                {
                                    $ret->address->City = new State($string->address->City->Id);
                                }
                            }
                        }
                        if(isset($string->address->country))
                        {
                            if(is_string($string->address->country))
                            {
                                $ret->address->country = $string->address->country;
                            }
                            else if($string->address->country instanceof Country)
                            {
                                $ret->address->country = $string->address->country->code;
                            }
                        }
                        if(isset($string->address->region))
                        {
                            $ret->address->region = $string->address->region;
                        }
                        if(isset($string->address->houseNumber))
                        {
                            $ret->address->houseNumber = $string->address->houseNumber;
                        }
                        if(isset($string->address->Street))
                        {
                            $ret->address->Street = $string->address->Street;
                        }
                    }
                    if(isset($string->boundingArea))
                    {
                        if(is_array($string->boundingArea))
                        {
                            for($i = 0; $i < count($string->boundingArea); $i++)
                            {
                                array_push($ret->boundingArea, 
                                    new Location(
                                        $string->boundingArea[$i]->Latitude, 
                                        $string->boundingArea[$i]->Longitude, 
                                        $string->boundingArea[$i]->Altitude, 
                                        $string->boundingArea[$i]->Accuracy
                                    )
                                );
                            }
                        }
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


        public static function GetDirection(Location $start, Location $end): XDirection 
        {
            $ret = new XDirection();
            
            return $ret;
        }
    }