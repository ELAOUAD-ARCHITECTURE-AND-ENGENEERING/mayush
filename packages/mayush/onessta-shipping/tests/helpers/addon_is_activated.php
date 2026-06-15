<?php

if (!function_exists('addon_is_activated')) {
    function addon_is_activated($identifier, $default = null)
    {
        return config("addons.active.{$identifier}", $default ?? false);
    }
}
