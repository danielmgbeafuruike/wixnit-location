<?php

    namespace Wixnit\Location;

    class Address
    {
        public Country $country = new Country();
        public string $state = "";
        public string $city = "";
        public string $street = "";
        public string $houseNumber = "";
        public string $region = "";

        function __construct()
        {
            ///TODO to be built later and all
        }

        public function fullAddress() : string
        {
            return $this->houseNumber." ".$this->street." ".((($this->city != null) && ($this->city != "")) ? $this->city : $this->region)." ".(is_object($this->state) ? $this->state->name : $this->state).", ".(is_object($this->country) ? $this->country->name : $this->country);
        }

        public function reverseGeocode(): Location
        {
            return Geocoding::reverse($this);
        }
    }