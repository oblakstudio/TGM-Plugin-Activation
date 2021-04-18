<?php
namespace Oblak\TGMPA\Admin;

use Exception;

use function Oblak\TGMPA\Utils\getPlugin;
use function Oblak\TGMPA\Utils\getPluginData;

class PluginAction
{

    private $plugins;

    private $config;

    public function __construct($plugins, &$config, &$strings)
    {

        $this->plugins = $plugins;
        $this->config  = $config;

        add_action('admin_post_tgmpa_activate',   [$this, 'activatePlugin'],   99);
        add_action('admin_post_tgmpa_deactivate', [$this, 'deactivatePlugin'], 99);


    }

    public function activatePlugin()
    {

        $plugin_slug = $_REQUEST['plugin'];

        $this->securityCheck('activate');

        $plugin  = getPlugin(
            getPluginData($plugin_slug, $this->plugins),
            $this->config
        );

        try {
            $plugin->activate();
        } catch (Exception $e) {
            $this->finalizeAction('error', $e->getMessage());    
        }
        
        $this->finalizeAction('success', __('Plugin activated.'));

    }

    public function deactivatePlugin()
    {

        $plugin_slug = $_REQUEST['plugin'];

        $this->securityCheck('deactivate');

        if ( !check_admin_referer('tgmpa_deactivate', 'nonce') ) :
            $this->finalizeAction('error', 'Nonce Verification failed');
        endif;

        $plugin  = getPlugin(
            getPluginData($plugin_slug, $this->plugins),
            $this->config
        );

        $plugin->deactivate();

        $this->finalizeAction('success', __('Plugin deactivated.'));

    }

    private function securityCheck($action)
    {

        if ( !check_admin_referer("tgmpa_{$action}", 'nonce') ) :
            $this->finalizeAction('error', 'Nonce Verification failed');
        endif;

        if ( !current_user_can('install_plugins') ) :
            $this->finalizeAction('error', __('Sorry, you are not allowed to manage plugins for this site.'));
        endif;

    }

    private function finalizeAction($type, $message)
    {

        $tgmpa_url    = admin_url(sprintf(
            '%s?page=%s',
            $this->config['menu_parent'],
            $this->config['menu_slug']
        ));

        $redirect_url = add_query_arg([
            'tgmpa_result'  => $type,
            'tgmpa_message' => $message,
        ], $tgmpa_url);

        wp_safe_redirect($redirect_url);

        exit;

    }

}