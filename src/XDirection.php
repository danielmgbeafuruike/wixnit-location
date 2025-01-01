<?php

    namespace Wixnit\Location;

    use Wixnit\Location\enum\Direction;

    class XDirection
    {
        public $Value = 0.0;

        function __construct($value=0.00)
        {
            $this->Value = doubleval($value);   
        }

        public static function fromCompassValues($value, $direction): XDirection
        {
            $ret = new XDirection();

            $degrees = ($value instanceof CompassValue) ? $value->Degrees : $value;
            $direction = ($value instanceof CompassValue) ? $value->Direction : $direction;

        
            if ($direction == Direction::North) 
            {
                $ret->Value = 0;
            } 
            else if ($direction == Direction::East) 
            {
                $ret->Value = 90;
            }
            else if ($direction == Direction::South) 
            {
                $ret->Value = 180;
            } 
            else if ($direction == Direction::West) 
            {
                $ret->Value = 270;
            } 
            else if ($direction == Direction::NorthEast) 
            {
                $ret->Value = $degrees;
            }
            else if ($direction == Direction::SouthEast) 
            {
                $ret->Value = (90 - $degrees) + 90;
            }
            else if ($direction == Direction::SouthWest) 
            {
                $ret->Value = $degrees + 180;
            }
            else if ($direction == Direction::NorthWest) 
            {
                $ret->Value = (90 - $degrees) + 180;
            }
            return $ret;
        }

        public function toCompassValues(): CompassValue
        {
            $ret = new CompassValue();

            if ($this->Value == 0) 
            {
                $ret->Degrees = 0;
                $ret->Direction = Direction::North;
            } 
            else if ($this->Value < 90) 
            {
                $ret->Degrees = $this->Value;
                $ret->Direction = Direction::NorthEast;
            } 
            else if ($this->Value == 90) 
            {
                $ret->Degrees = 0;
                $ret->Direction = Direction::East;
            } 
            else if (($this->Value > 90) && ($this->Value < 180)) 
            {
                $ret->Degrees = (90 - ($this->Value - 90));
                $ret->Direction = Direction::SouthEast;
            } 
            else if ($this->Value == 180) 
            {
                $ret->Degrees = 0;
                $ret->Direction = Direction::South;
            } 
            else if (($this->Value > 180) && ($this->Value < 270)) 
            {
                $ret->Degrees = ($this->Value - 180);
                $ret->Direction = Direction::SouthEast;
            } 
            else if ($this->Value == 270) 
            {
                $ret->Degrees = 0;
                $ret->Direction = Direction::West;
            } 
            else if (($this->Value > 270) && ($this->Value < 360)) 
            {
                $ret->Degrees = 90 - (($this->Value - 90) - 180);
                $ret->Direction = Direction::North;
            } 
            else if ($this->Value == 360) 
            {
                $ret->Degrees = 0;
                $ret->Direction = Direction::North;
            }
            return $ret;
        }
    }