<?php

namespace App\Utility;

use App\Models\Addon;
use App\Models\Color;

class ProductUtility
{
    public static function get_attribute_options($collection)
    {
        $options = array();
        if (
            $collection->get('colors_active') &&
            $collection->has('colors') &&
            is_array($collection->get('colors')) &&
            count($collection->get('colors')) > 0
        ) {
            $colors_active = 1;
            array_push($options, $collection->get('colors'));
        }

        if (isset($collection['choice_no']) && $collection['choice_no']) {
            foreach ($collection['choice_no'] as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                $options_values = isset($collection[$name]) ? $collection[$name] : (request()[$name] ?? []);
                if (is_array($options_values) || is_object($options_values)) {
                    foreach ($options_values as $key => $eachValue) {
                        array_push($data, $eachValue);
                    }
                }
                array_push($options, $data);
            }
        }

        return $options;
    }

    public static function get_combination_string($combination, $collection)
    {
        $str = '';
        foreach ($combination as $key => $item) {
            if ($key > 0) {
                $str .= '-' . str_replace(' ', '', $item);
            } else {
                if ($collection->get('colors_active') && $collection->has('colors') && is_array($collection->get('colors')) && count($collection->get('colors')) > 0) {
                    $color_name = Color::where('code', $item)->first()->name;
                    $str .= $color_name;
                } else {
                    $str .= str_replace(' ', '', $item);
                }
            }
        }
        return $str;
    }
}
