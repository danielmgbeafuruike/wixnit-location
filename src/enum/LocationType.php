<?php

    namespace Wixnit\Location\enum;

    enum LocationType : string
    {
        case LastLocation = "last_location";
        case GeoIP = "geo_ip";
        case GPSPosition = "gps_position";
    }