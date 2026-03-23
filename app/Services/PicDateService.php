<?php

namespace App\Services;

use Carbon\Carbon;

class PicDateService
{
    const START_DATE = '1971-09-27';

    /**
     * Get Vuelta and FPic from a standard date
     */
    public static function fromDate($date)
    {
        $start = Carbon::parse(self::START_DATE);
        $target = Carbon::parse($date);
        
        $diffDays = $start->diffInDays($target, false);
        
        if ($diffDays < 0) return ['vuelta' => 0, 'pic' => 0];

        $vuelta = floor($diffDays / 1000);
        $pic = $diffDays % 1000;

        return [
            'vuelta' => (int)$vuelta,
            'pic' => (int)$pic,
            'total_days' => (int)$diffDays
        ];
    }

    /**
     * Get standard date from Vuelta and FPic
     */
    public static function toDate($vuelta, $pic)
    {
        $start = Carbon::parse(self::START_DATE);
        $totalDays = ($vuelta * 1000) + $pic;
        
        return $start->addDays($totalDays);
    }
}
