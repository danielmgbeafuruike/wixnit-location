<?php

    namespace Wixnit\Location;

    use Wixnit\Location;

    class Address
    {
        public string $State = "";
        public string $Country = "";
        public string $City = "";
        public string $Street = "";
        public string $Housenumber = "";
        public string $Region = "";

        function __construct()
        {
            ///TODO to be built later and all
        }

        public function fullAddress() : string
        {
            return $this->Housenumber." ".$this->Street." ".((($this->City != null) && ($this->City != "")) ? $this->City : $this->Region)." ".(is_object($this->State) ? $this->State->Name : $this->State).", ".(is_object($this->Country) ? $this->Country->Name : $this->Country);
        }

        public function reverseGeocode(): Location
        {
            return Geocoding::reverse($this);
        }
    }