<?php

    namespace Wixnit\Location;

    /**
     * Utilities for working with raw GPS data: converting between decimal-degree
     * and degrees/minutes/seconds notation, and parsing NMEA 0183 sentences -
     * the standard text protocol most GPS receivers/modules output.
     */
    class GPS
    {
        /**
         * Converts decimal degrees (e.g. 6.5244) into a "D°M'S\"H" string,
         * where H is the hemisphere letter for the given axis.
         */
        public static function toDMS(float $decimalDegrees, bool $isLatitude = true): string
        {
            $hemisphere = $isLatitude
                ? ($decimalDegrees >= 0 ? "N" : "S")
                : ($decimalDegrees >= 0 ? "E" : "W");

            $absolute = abs($decimalDegrees);
            $degrees = floor($absolute);
            $minutesFull = ($absolute - $degrees) * 60;
            $minutes = floor($minutesFull);
            $seconds = round(($minutesFull - $minutes) * 60, 2);

            return sprintf("%d°%d'%s\"%s", $degrees, $minutes, $seconds, $hemisphere);
        }

        /**
         * Parses a "D°M'S\"H" (or plain "D M S H") string back into decimal degrees.
         */
        public static function fromDMS(string $dms): float
        {
            $pattern = '/(-?\d+(?:\.\d+)?)[°\s]+(\d+(?:\.\d+)?)[\'\s]+(\d+(?:\.\d+)?)["\s]*([NSEW]?)/i';

            if(!preg_match($pattern, trim($dms), $matches))
            {
                return 0.00;
            }

            $degrees = floatval($matches[1]);
            $minutes = floatval($matches[2]);
            $seconds = floatval($matches[3]);
            $hemisphere = strtoupper($matches[4]);

            $decimal = abs($degrees) + ($minutes / 60) + ($seconds / 3600);

            if(in_array($hemisphere, ["S", "W"]))
            {
                $decimal *= -1;
            }

            return $decimal;
        }

        /**
         * Parses a single NMEA sentence ($GPGGA/$GNGGA or $GPRMC/$GNRMC style)
         * into a Cordinate. Returns null if the sentence is malformed, fails its
         * checksum, or reports no valid fix.
         */
        public static function fromNMEA(string $sentence): ?Cordinate
        {
            $sentence = trim($sentence);

            if(!self::checksumValid($sentence))
            {
                return null;
            }

            $fields = explode(",", $sentence);
            $type = substr($fields[0], -3); // strips the 2-letter talker ID (GP, GN, GL...)

            if($type === "GGA")
            {
                return self::fromGGA($fields);
            }
            else if($type === "RMC")
            {
                return self::fromRMC($fields);
            }

            return null;
        }

        private static function fromGGA(array $fields): ?Cordinate
        {
            // $--GGA,time,lat,N/S,lon,E/W,fixQuality,numSatellites,hdop,altitude,M,...
            if(count($fields) < 10 || $fields[2] === "" || $fields[4] === "")
            {
                return null;
            }

            if(intval($fields[6]) === 0) // fix quality 0 = no fix
            {
                return null;
            }

            $ret = new Cordinate();
            $ret->latitude = self::nmeaCoordToDecimal($fields[2], $fields[3]);
            $ret->longitude = self::nmeaCoordToDecimal($fields[4], $fields[5]);
            $ret->altitude = isset($fields[9]) ? doubleval($fields[9]) : 0.00;

            return $ret;
        }

        private static function fromRMC(array $fields): ?Cordinate
        {
            // $--RMC,time,status,lat,N/S,lon,E/W,speed,course,date,...
            if(count($fields) < 7 || $fields[3] === "" || $fields[5] === "")
            {
                return null;
            }

            if($fields[2] !== "A") // A = active/valid fix, V = void
            {
                return null;
            }

            $ret = new Cordinate();
            $ret->latitude = self::nmeaCoordToDecimal($fields[3], $fields[4]);
            $ret->longitude = self::nmeaCoordToDecimal($fields[5], $fields[6]);

            return $ret;
        }

        /**
         * NMEA encodes coordinates as DDMM.MMMM (latitude) / DDDMM.MMMM (longitude)
         * plus a hemisphere letter, rather than plain decimal degrees.
         */
        private static function nmeaCoordToDecimal(string $raw, string $hemisphere): float
        {
            if($raw === "")
            {
                return 0.00;
            }

            $dotPosition = strpos($raw, ".");
            $degreesLength = $dotPosition - 2;

            $degrees = floatval(substr($raw, 0, $degreesLength));
            $minutes = floatval(substr($raw, $degreesLength));

            $decimal = $degrees + ($minutes / 60);

            if(in_array(strtoupper($hemisphere), ["S", "W"]))
            {
                $decimal *= -1;
            }

            return $decimal;
        }

        private static function checksumValid(string $sentence): bool
        {
            if(strlen($sentence) < 4 || $sentence[0] !== '$' || strpos($sentence, "*") === false)
            {
                return false;
            }

            list($body, $checksum) = explode("*", substr($sentence, 1), 2);

            $calculated = 0;
            for($i = 0; $i < strlen($body); $i++)
            {
                $calculated ^= ord($body[$i]);
            }

            return strtoupper(trim($checksum)) === strtoupper(sprintf("%02X", $calculated));
        }
    }
