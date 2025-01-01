<?php

    namespace Wixnit\Location;

    class IPGeolocation
    {
        //used to obtain the location of a user and all
        public static function getAddress($ip) : Address
        {
            return new Address();
        }
    }