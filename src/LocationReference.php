<?php

    namespace Wixnit\Location;

    use Exception;
    use Wixnit\Country;
    use Wixnit\Location;
    use Wixnit\State;

    class LocationReference
    {
        /**
         * @param Address
         */
        public ?Address $Address = null;

        /**
         * @var array
         */
        public array $BoundingArea = [];

        /**
         * @param float
         * minimum radius value for calculating location value
         */
        private $minRadius = 100; //distance in meters



        function __construct(Address $address=null, $positions=[])
        {
            $this->Address = $address ?? new Address();

            if(is_array($positions))
            {
                for($i = 0; $i < count($positions); $i++)
                {
                    if($positions[$i] instanceof Location)
                    {
                        $this->BoundingArea[] = $positions[$i];
                    }
                }
            }
        }

        public function centerPosition(): Location
        {
            if(count($this->BoundingArea) == 0)
            {
                return new Location();
            }
            else if(count($this->BoundingArea) == 1)
            {
                return $this->BoundingArea[0];
            }
            else if(count($this->BoundingArea) == 2)
            {
                return new Location(
                    (($this->BoundingArea[0]->Latitude + $this->BoundingArea[1]->Latitude) / 2),
                    (($this->BoundingArea[0]->Longitude + $this->BoundingArea[1]->Longitude) / 2),
                    (($this->BoundingArea[0]->Altitude + $this->BoundingArea[1]->Altitue) / 2)
                    (($this->BoundingArea[0]->Accuracy + $this->BoundingArea[1]->Accuracy) / 2)
                );
            }
            else
            {
                
            }
            return new Location();
        }

        public function isInLocation(Location $position): bool
        {
            if(count($this->BoundingArea) == 0)
            {
                return false;
            }
            else if(count($this->BoundingArea) == 1)
            {
                return $this->BoundingArea[0];
            }
            else if(count($this->BoundingArea) == 2)
            {
                return false;
            }
            else
            {
                
            }
            return false;
        }

        public function Distance (LocationReference $location) : float
        {
            return $this->centerPosition()->Distance($location->centerPosition());
        }

        public function Direction (LocationReference $location) : XDirection
        {
            return LocationReference::GetDirection($this->centerPosition(), $location->centerPosition());
        }



        public function geoCode() : Address
        {
            return Geocoding::forward($this->centerPosition());
        }

        public function reverseGeocode() : Location
        {
            return $this->Address->reverseGeocode();
        }



        public function toString() : string
        {
            $ret = [
                "Address"=>[
                    "Country"=>$this->Address->Country,
                    "State"=>$this->Address->State,
                    "City"=>$this->Address->City,
                    "Street"=>$this->Address->Street,
                    "Region"=>$this->Address->Region,
                    "Housenumber"=>$this->Address->Housenumber
                ],
                "BoundingArea"=>$this->BoundingArea
            ];
            return json_encode($ret);
        }

        public static function fromString($string) : LocationReference
        {
            $ret = LocationReference::noWhere();
            try{
                $loc = null;

                if(is_string($string))
                {
                    $d = json_decode($string);

                    if(isset($d->Address))
                    {
                        if(isset($d->Address->State))
                        {
                            if(is_string($d->Address->State))
                            {
                                $ret->Address->State = new $d->Address->State;
                            }
                            else if($d->Address->State instanceof State)
                            {
                                if(isset($d->Address->State->Id))
                                {
                                    $ret->Address->State = $d->Address->State->Name;
                                }
                            }
                        }
                        if(isset($d->Address->City))
                        {
                            if(is_string($d->Address->City))
                            {
                                $ret->Address->City = $d->Address->City;
                            }
                            else if(is_object($d->Address->City))
                            {
                                if(isset($d->Address->City->Id))
                                {
                                    $ret->Address->City = $d->Address->City->Name;
                                }
                            }
                        }
                        if(isset($d->Address->Country))
                        {
                            if(is_string($d->Address->Country))
                            {
                                $ret->Address->Country = $d->Address->Country;
                            }
                            else if($d->Address->Country instanceof Country)
                            {
                                $ret->Address->Country = $d->Address->Country->Code;
                            }
                        }
                        if(isset($d->Address->Region))
                        {
                            $ret->Address->Region = $d->Address->Region;
                        }
                        if(isset($d->Address->Housenumber))
                        {
                            $ret->Address->Housenumber = $d->Address->Housenumber;
                        }
                        if(isset($d->Address->Street))
                        {
                            $ret->Address->Street = $d->Address->Street;
                        }
                    }
                    if(isset($d->BoundingArea))
                    {
                        if(is_array($d->BoundingArea))
                        {
                            for($i = 0; $i < count($d->BoundingArea); $i++)
                            {
                                $ret->BoundingArea[] =
                                new Location(
                                    $d->BoundingArea[$i]->Latitude, 
                                    $d->BoundingArea[$i]->Longitude, 
                                    $d->BoundingArea[$i]->Altitude, 
                                    $d->BoundingArea[$i]->Accuracy
                                );
                            }
                        }
                    }
                }
                else if(is_object($string))
                {
                    if(isset($string->Address))
                    {
                        if(isset($string->Address->State))
                        {
                            if(is_string($string->Address->State))
                            {
                                $ret->Address->State = new State($string->Address->State);
                            }
                            else if(is_object($string->Address->State))
                            {
                                if(isset($string->Address->State->Id))
                                {
                                    $ret->Address->State = new State($string->Address->State->Id);
                                }
                            }
                        }
                        if(isset($string->Address->City))
                        {
                            if(is_string($string->Address->City))
                            {
                                $ret->Address->City = new State($string->Address->City);
                            }
                            else if(is_object($string->Address->City))
                            {
                                if(isset($string->Address->City->Id))
                                {
                                    $ret->Address->City = new State($string->Address->City->Id);
                                }
                            }
                        }
                        if(isset($string->Address->Country))
                        {
                            if(is_string($string->Address->Country))
                            {
                                $ret->Address->Country = $string->Address->Country;
                            }
                            else if($string->Address->Country instanceof Country)
                            {
                                $ret->Address->Country = $string->Address->Country->Code;
                            }
                        }
                        if(isset($string->Address->Region))
                        {
                            $ret->Address->Region = $string->Address->Region;
                        }
                        if(isset($string->Address->Housenumber))
                        {
                            $ret->Address->Housenumber = $string->Address->Housenumber;
                        }
                        if(isset($string->Address->Street))
                        {
                            $ret->Address->Street = $string->Address->Street;
                        }
                    }
                    if(isset($string->BoundingArea))
                    {
                        if(is_array($string->BoundingArea))
                        {
                            for($i = 0; $i < count($string->BoundingArea); $i++)
                            {
                                array_push($ret->BoundingArea, 
                                    new Location(
                                        $string->BoundingArea[$i]->Latitude, 
                                        $string->BoundingArea[$i]->Longitude, 
                                        $string->BoundingArea[$i]->Altitude, 
                                        $string->BoundingArea[$i]->Accuracy
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
        public static function noWhere() : LocationReference
        {
            $ret = new LocationReference();
            $ret->Address = new Address();
            $ret->BoundingArea = [];
            return $ret;
        }


        public static function GetDirection(Location $start, Location $end): XDirection 
        {
            $ret = new XDirection();
            
            return $ret;
        }
    }