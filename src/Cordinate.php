<?php

    namespace Wixnit\Location;

    use \stdClass;
    use \Exception;

    class Cordinate
    {
        public float $longitude = 0.00;
        public float $latitude = 0.00;
        public float $altitude = 0.00;
        public float $accuracy = 0.0;

        public static function Nowhere()
        {
            $ret = new Location();
            $ret->longitude = 0.00;
            $ret->latitude = 0.00;
            $ret->altitude = 0.00;
            $ret->speed = 0.00;
            return $ret;
        }

        public static function FromLongLat($longitude, $latitude=null): Cordinate
        {
            $ret = new Cordinate();
            
            $ret->longitude = doubleval($longitude);
            $ret->latitude = doubleval($latitude);
            return $ret;
        }

        public static function FromJsonString($string)
        {
            $ret = new Cordinate();
            
            if(is_string($string))
            {
                try{
                    $data = json_decode($string);

                    $ret->longitude = isset($data->longitude) ? doubleval($data->longitude) : 0.00;
                    $ret->latitude = isset($data->latitude) ? doubleval($data->latitude) : 0.00;
                    $ret->altitude = isset($data->altitude) ? doubleval($data->altitude) : 0.00;
                    $ret->accuracy = isset($data->accuracy) ? doubleval($data->accuracy) : 0.00;
                }
                catch(Exception $e){}
            } 
            return $ret;
        }

        public static function FromJsonObject($object)
        {
            $ret = new Location();
            
            if(is_object($object))
            {
                try{
                    $ret->longitude = isset($object->longitude) ? doubleval($object->longitude) : 0.00;
                    $ret->latitude = isset($object->latitude) ? doubleval($object->latitude) : 0.00;
                    $ret->altitude = isset($object->altitude) ? doubleval($object->altitude) : 0.00;
                    $ret->accuracy = isset($object->accuracy) ? doubleval($object->accuracy) : 0.00;
                }
                catch(Exception $e){}
            } 
            return $ret;
        }

        public function distance(Cordinate $cordinate): float|int
        {
            return $this->measureDistance($this->latitude, $this->longitude, $cordinate->latitude, $cordinate->longitude);
        }

        public function toString()
        {
            $ret = new stdClass();
            $ret->longitude = $this->longitude;
            $ret->latitude = $this->latitude;
            $ret->altitude = $this->altitude;
            $ret->accuracy = $this->accuracy;

            return json_encode($ret);
        }

        private function measureDistance($lat1, $lon1, $lat2, $lon2): float|int
        {  // generally used geo measurement function
            $R = 6378.137; // Radius of earth in KM
            $dLat = $lat2 * pi() / 180 - $lat1 * pi() / 180;
            $dLon = $lon2 * pi() / 180 - $lon1 * pi() / 180;
            $a = sin($dLat/2) * sin($dLat/2) +
            cos($lat1 * pi() / 180) * cos($lat2 * pi() / 180) *
            sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $d = $R * $c;
            return $d * 1000; // meters
        }
    }