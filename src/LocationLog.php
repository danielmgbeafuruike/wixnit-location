<?php

    namespace Wixnit\Location;

    use Wixnit\App\Model;
    use Wixnit\Location;

    class LocationLog extends Model
    {
        public string $UserReference;
        public Location $Location;
    }