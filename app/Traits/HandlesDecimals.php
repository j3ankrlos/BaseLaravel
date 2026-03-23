<?php

namespace App\Traits;

trait HandlesDecimals
{
    /**
     * Convert a string with a comma separator to a float.
     * Example: "1,50" -> 1.50
     */
    public function parseDecimal($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Replace comma with dot and remove other non-numeric chars except minus
        $cleaned = str_replace(',', '.', $value);
        
        return (float) $cleaned;
    }
}
