<?php

    namespace Wixnit\Location;

    class Location
    {
        public Cordinate $cordinate = Cordinate::Nowhere();
        public Address $address = new Address();
    }