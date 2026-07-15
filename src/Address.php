<?php

    namespace Wixnit\Location;

    class Address
    {
        public Country $country;
        public string $state = "";
        public string $city = "";
        public string $street = "";
        public string $houseNumber = "";
        public string $region = "";

        function __construct()
        {
            $this->country = new Country();
        }

        public function fullAddress() : string
        {
            $locality = ($this->city != "") ? $this->city : $this->region;

            return trim($this->houseNumber." ".$this->street." ".$locality." ".$this->state).", ".$this->country->name;
        }

        public function reverseGeocode(): Location
        {
            return GeoCoding::reverse($this);
        }

        public static function From(Country $country, string $state, string $city, string $street="", string $houseNum="", string $region=""): Address
        {
            $ret = new Address();
            $ret->country = $country;
            $ret->state = $state;
            $ret->city = $city;
            $ret->street = $street;
            $ret->houseNumber = $houseNum;
            $ret->region = $region;
            return $ret;
        }
    }
