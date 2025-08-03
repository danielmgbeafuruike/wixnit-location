<?php

    namespace Wixnit\Location;

    use Wixnit\Location\enum\Direction;

    class XDirection
    {
        public $value = 0.0;

        function __construct($value=0.00)
        {
            $this->value = doubleval($value);   
        }

        public static function fromCompassValues($value, $direction): XDirection
        {
            $ret = new XDirection();

            $degrees = ($value instanceof CompassValue) ? $value->degrees : $value;
            $direction = ($value instanceof CompassValue) ? $value->direction : $direction;

        
            if ($direction == Direction::NORTH) 
            {
                $ret->value = 0;
            } 
            else if ($direction == Direction::EAST) 
            {
                $ret->value = 90;
            }
            else if ($direction == Direction::SOUTH) 
            {
                $ret->value = 180;
            } 
            else if ($direction == Direction::WEST) 
            {
                $ret->value = 270;
            } 
            else if ($direction == Direction::NORTH_EAST) 
            {
                $ret->value = $degrees;
            }
            else if ($direction == Direction::SOUTH_EAST) 
            {
                $ret->value = (90 - $degrees) + 90;
            }
            else if ($direction == Direction::SOUTH_WEST) 
            {
                $ret->value = $degrees + 180;
            }
            else if ($direction == Direction::NORTH_WEST) 
            {
                $ret->value = (90 - $degrees) + 180;
            }
            return $ret;
        }

        public function toCompassValues(): CompassValue
        {
            $ret = new CompassValue();

            if ($this->value == 0) 
            {
                $ret->degrees = 0;
                $ret->direction = Direction::NORTH;
            } 
            else if ($this->value < 90) 
            {
                $ret->degrees = $this->value;
                $ret->direction = Direction::NORTH_EAST;
            } 
            else if ($this->value == 90) 
            {
                $ret->degrees = 0;
                $ret->direction = Direction::EAST;
            } 
            else if (($this->value > 90) && ($this->value < 180)) 
            {
                $ret->degrees = (90 - ($this->value - 90));
                $ret->direction = Direction::SOUTH_EAST;
            } 
            else if ($this->value == 180) 
            {
                $ret->degrees = 0;
                $ret->direction = Direction::SOUTH;
            } 
            else if (($this->value > 180) && ($this->value < 270)) 
            {
                $ret->degrees = ($this->value - 180);
                $ret->direction = Direction::SOUTH_EAST;
            } 
            else if ($this->value == 270) 
            {
                $ret->degrees = 0;
                $ret->direction = Direction::WEST;
            } 
            else if (($this->value > 270) && ($this->value < 360)) 
            {
                $ret->degrees = 90 - (($this->value - 90) - 180);
                $ret->direction = Direction::NORTH;
            } 
            else if ($this->value == 360) 
            {
                $ret->degrees = 0;
                $ret->direction = Direction::NORTH;
            }
            return $ret;
        }
    }