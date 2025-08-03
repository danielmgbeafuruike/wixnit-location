<?php

    namespace Wixnit\Location;

    use Wixnit\App\Model;

    class LocationLog extends Model
    {
        public string $userReference;
        public Location $location;
    }