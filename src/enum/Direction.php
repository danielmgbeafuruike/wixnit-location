<?php

    namespace Wixnit\Location\enum;

    enum Direction : string
    {
        case North = "north";
        case South = "south";
        case East = "east";
        case West = "west";
        case NorthEast = "north-east";
        case NorthWest = "north-west";
        case SouthEast = "south-east";
        case SouthWest = "south-west";
        case Unknown = "unknown";
    }