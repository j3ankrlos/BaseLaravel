<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleUsage extends Model
{
    protected $table = 'module_usage';
    
    protected $fillable = [
        'module_key',
        'display_name',
        'url',
        'icon',
        'color_class',
        'hits'
    ];

    /**
     * Track module visit
     * @param string $key Unique key for the module
     * @param string $displayName Name to show in dashboard
     * @param string $url Link to the module
     * @param string $icon Phosphor icon name
     * @param string $colorClass Tailwind/CSS color class
     */
    public static function track($key, $displayName, $url, $icon, $colorClass = 'text-primary')
    {
        $module = self::firstOrCreate(
            ['module_key' => $key],
            [
                'display_name' => $displayName,
                'url'          => $url,
                'icon'         => $icon,
                'color_class'  => $colorClass,
                'hits'         => 0
            ]
        );

        $module->increment('hits');
    }
}
