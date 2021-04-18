<?php

namespace Oblak\TGMPA;

use Exception;
use Oblak\TGMPA\Admin\PluginAction;
use Oblak\TGMPA\Admin\PluginPage;

use function Oblak\TGMPA\Utils\doing_it_wrong;

class Bootstrap
{
    /**
     * TGMPA Version
     */
    const VERSION = '1.0.0';

    private static $cache_group = 'oblak-tgmpa';

    /**
     * WordPress version
     * 
     * @var string
     */
    private $wp_version;

    /**
     * Array of Plugin metadata
     * @var array
     */
    public $plugins;

    public function __construct($plugins, $config = [], $strings = [])
    {
        // Bail out if not in Admin area
        if (!is_admin()) return;

        if ( did_action('wp_loaded') ) :
            doing_it_wrong(
                'Init Action called - you need to reinitialize sooner',
                'error'
            );
        endif;

        try {
            $this->setupActivator($plugins, $config, $strings);
        } catch(Exception $e) {
            doing_it_wrong($e->getMessage(), 'error');
        }

        new PluginPage($this->plugins, $this->config, $this->strings);
        new PluginAction($this->plugins, $this->config, $this->strings);
            
    }

    private function setupActivator($plugins, $config, $strings)
    {

        $this->wp_version = $GLOBALS['wp_version'];
        $this->plugins    = $plugins;//$this->configurePlugins($plugins);

        $this->config     = $this->validateConfig($config);
        $this->strings    = $this->parseStrings($strings);

    }

    private function validateConfig($config)
    {
        $default_config = [
            'menu_parent'     => 'themes.php',
            'menu_slug'       => 'install-plugins',
            'always_activate' => false,
            'plugin_columns'  => 4,
            'default_image'   => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
            'menu_priority'   => 99,
        ];

        return wp_parse_args($config, $default_config);
    }

    private function parseStrings($strings)
    {

        $default_strings = [
            'fuck'                => 'Something went terribly wrong',
            'oops'                => 'Something went wrong with the Plugin API',
            'menu_title'          => 'Install plugins',
            'page_title'          => 'Install plugins',
            'install_recommended' => '',
            'install_required'    => '',
            'install_failed'      => '',
            'install_success'     => '',
            'activation_failed'   => '',
            'update_needed'       => '',
            'update_failed'       => '',
            'update_success'      => '',
            'return_link'         => 'Return to the plugins installer',
            'activate_link'       => '',
            'complete'            => '',
        ];

        return wp_parse_args($strings, $default_strings);

    }

}