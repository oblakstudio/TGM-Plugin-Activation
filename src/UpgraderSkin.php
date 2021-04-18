<?php
namespace Oblak\TGMPA;

use Exception;
use WP_Error;
use WP_Upgrader_Skin;

class UpgraderSkin extends WP_Upgrader_Skin
{

    /**
	 * @param string|WP_Error $string
	 * @param mixed  ...$args Optional text replacements.
	 */
    public function feedback( $string, ...$args )
    {

        if (is_wp_error($string)) :
            throw new Exception($string->get_error_message());
        endif;

    }

    public function error($errors)
    {

        if (is_wp_error($errors)) :
            throw new Exception($errors->get_error_message());
        endif;

    }

}