<?php

    namespace Wixnit\Location;

    use Wixnit\Location;

    /**
     * implimentation of several geo coding and reverse geo coding to improve the rate of geocoding success
     */
    class Geocoding
    {
        public static function forward(Location $position) : Address
        {
            return new Address();
        } 

        public static function reverse(Address $address) : Location
        {
            return new Location();
        }

        private function google_geoCode($lat=0.00, $long=0.00)
        {
            
        }

        private function google_reverseGeocode($housenumber, $street, $city, $state, $region, $country)
        {

        }

        /**
         * Using another geo coding API from another supplyer
         */
        private function geoCode_1($lat=0.00, $long=0.00)
        {
            
        }

        private function reverseGeocode_1($housenumber, $street, $city, $state, $region, $country)
        {

        }

        private function geoCode_2($lat=0.00, $long=0.00)
        {
            
        }

        private function reverseGeocode_2($housenumber, $street, $city, $state, $region, $country)
        {

        }

        private function geoCode_3($lat=0.00, $long=0.00)
        {
            
        }

        private function reverseGeocode_3($housenumber, $street, $city, $state, $region, $country)
        {

        }
    }