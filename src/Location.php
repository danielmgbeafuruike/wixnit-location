<?php

    namespace Wixnit\Location;

    class Location
    {
        public Cordinate $cordinate;
        public Address $address;

        function __construct()
        {
            $this->cordinate = Cordinate::Nowhere();
            $this->address = new Address();
        }
    }
