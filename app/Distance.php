<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    /**
     * this function will calculate the nearest distance / radius
     * https://www.movable-type.co.uk/scripts/latlong.html
     * http://php.net/manual/en/function.deg2rad.php
     * https://developers.google.com/maps/solutions/store-locator/clothing-store-locator?csw=1
     *
     * @param Double $fromLat : current latitude
     * @param Double $fromLon : selected latitude
     * @param Double $toLat : current longtitude
     * @param Double $fromLon: selected longtitude
     * @param Decimal $precision
     */
    private static function calculate($fromLat, $fromLon, $toLat, $toLon, $precision = 0) {
        $earthRadius = 6371; //this is constant earth radius
        $dLat = deg2rad($toLat - $fromLat); //convert latitude to degrees
        $dLon = deg2rad($toLon - $fromLon);

        $a = sin($dLat/2) * sin($dLat/2) +  cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $d = $earthRadius * $c;

        return round($d, $precision);
    }

    public static function toKm($fromLat, $fromLon, $toLat, $toLon, $precision = 0) {
        return self::calculate($fromLat, $fromLon, $toLat, $toLon);
    }

    public static function toMiles($fromLat, $fromLon, $toLat, $toLon, $precision = 0) {
        $distance = self::calculate($fromLat, $fromLon, $toLat, $toLon, $precision);

        return round($distance * 0.621371192, $precision);
    }
}
