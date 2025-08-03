<?php

    namespace Wixnit\Location\enum;

    enum LocationType : string
    {
        case LAST_LOCATION = "last_location";
        case GEO_IP = "geo_ip";
        case GPS_POSITION = "gps_position";
    }