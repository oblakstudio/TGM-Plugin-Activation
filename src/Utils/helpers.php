<?php

namespace Oblak\TGMPA\Utils;

use Oblak\TGMPA\Plugin\PluginInterface;
use stdClass;

function doing_it_wrong($message = '', $notice_type = 'error')
{
    if ( defined('TGMPA_SILENCED') && (TGMPA_SILENCED == true) ) return;

    add_action('admin_notices', function() use ($message, $notice_type) {
        printf(
            '<div class="notice notice-%s">
                <p><strong>TGMPA ERROR: </strong>%s</p>
            </div>',
            $notice_type,
            $message
        );
    });
}

/**
 * 
 * @param  array|string $json_or_array JSON string containing configuration data, or a configuration array
 * @return array                       Parsed configuration array 
 */
function parseConfig($json_or_array)
{
    return( !is_array($json_or_array) )
        ? json_decode($json_or_array, true)
        : $json_or_array;
}

/**
 * 
 * @param  mixed $plugin_data 
 * @param  mixed $config 
 * @return PluginInterface 
 */
function getPlugin($plugin_data, &$config)
{

    $plugin_class = apply_filters('oblak/tgmpa/plugin_class', 'Oblak\\TGMPA\\Plugin\\BasePlugin');

    return new $plugin_class($plugin_data, $config);

}

function getPluginData($plugin_slug, $plugin_array)
{

    $plugin_data = reset(array_filter($plugin_array, function ($plugin) use ($plugin_slug) {
        return $plugin['slug'] === $plugin_slug;
    }));

    return ( is_array ($plugin_data) )
        ? $plugin_data
        : false;

}

function injectUpdateData($plugin_slug, $package_url)
{
    $transient = get_site_transient('update_plugins');

    $injectable = new stdClass;
    $injectable->plugin = $plugin_slug;
    $injectable->package = $package_url;

    $transient->response[$plugin_slug] = $injectable;

    set_site_transient('update_plugins', $transient);

}