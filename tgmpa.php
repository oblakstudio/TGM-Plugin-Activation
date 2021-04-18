<?php

namespace Oblak\TGMPA;

use function Oblak\TGMPA\Utils\parseConfig;

!defined('TGMPA_PATH') && define('TGMPA_PATH', dirname(__FILE__));
!defined('TGMPA_ASSETS') && define('TGMPA_ASSETS', 'https://cdn.tgmpa.app/');

/**
 * 
 * @param  array|string $plugins Array of plugin metadata, or a json encoded string
 * @param  array|string $config  TGMPA config array, or a json encoded string
 * @param  array        $strings Array of localization strings
 */
function tgmpa($plugins, $config = [], $strings = [])
{

    $plugins = parseConfig($plugins);
    $config = parseConfig($config);

    new Bootstrap($plugins, $config, $strings);

}