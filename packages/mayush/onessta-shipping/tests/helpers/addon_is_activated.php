<?php

if (!function_exists('addon_is_activated')) {
    function addon_is_activated($identifier, $default = null)
    {
        return $default ?? false;
    }
}
