<?php

    namespace Wixnit\Location\enum;

    enum Direction : string
    {
        case NORTH = "north";
        case SOUTH = "south";
        case EAST = "east";
        case WEST = "west";
        case NORTH_EAST = "north-east";
        case NORTH_WEST = "north-west";
        case SOUTH_EAST = "south-east";
        case SOUTH_WEST = "south-west";
        case UNKNOWN = "unknown";
    }