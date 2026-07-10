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
    }
