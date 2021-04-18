<?php

namespace Oblak\TGMPA\Admin;

use Oblak\TGMPA\Plugin\PluginInterface;

use function Oblak\TGMPA\Utils\getPlugin;
use function Oblak\TGMPA\Utils\getPluginData;

class PluginPage
{

    private $hook_suffix;

    private $plugins;

    private $config;

    private $strings;

    public function __construct(&$plugins, &$config, &$strings)
    {

        if ( !current_user_can('install_plugins')) return;

        $this->plugins = $plugins;
        $this->config  = $config;
        $this->strings = $strings;

        add_action('admin_notices', [$this, 'displayNotices']);
        add_action('admin_menu', [$this, 'addMenuPage'], $config['menu_priority']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets'], 99, 1);

    }

    public function displayNotices()
    {
        
        if ( !isset($_REQUEST['tgmpa_result']) ) return;

        printf(
            '<div class="notice notice-%s">
                <p><strong>TGMPA: </strong>%s</p>
            </div>',
            $_REQUEST['tgmpa_result'],
            $_REQUEST['tgmpa_message']
        );

    }

    public function addMenuPage()
    {

        $this->hook_suffix = add_submenu_page(
            $this->config['menu_parent'],
            $this->strings['page_title'],
            $this->strings['menu_title'],
            'install_plugins',
            $this->config['menu_slug'],
            [$this, 'pluginInstallPage']
        );

    }

    public function enqueueAssets($hook_suffix)
    {

        if ($hook_suffix != $this->hook_suffix) return;

        $stylesheet = apply_filters('oblak/tgmpa/stylesheet', TGMPA_ASSETS . '/styles/main.css');

        wp_enqueue_style('tgmpa', $stylesheet, null, '1.0.0');

    }

    public function pluginInstallPage()
    {

        $action = $_REQUEST['action'] ?? false;   

        $template_file = apply_filters('oblak/tgmpa/plugin_template', TGMPA_PATH. '/templates/plugin.php');

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo get_admin_page_title(); ?></h1>
            <hr class="wp-header-end">

            <?php
            if ($action) : 
        
                $callback = ($action == 'tgmpa_install') ? 'install' : 'update';
                $slug   = $_REQUEST['plugin'];
                $plugin = getPlugin(
                    getPluginData($slug, $this->plugins),
                    $this->config
                );

                $plugin->$callback();

                $this->outputReturnLink();

                return;

            endif;
            ?>

            <div id="poststuff">

                <?php do_action('oblak/tgmpa/before_plugin_table', $this->config, $this->strings); ?>

                <div class="bootstrap-wrapper">
                    <div class="tgmpa-plugin-table">
                        <div class="row">
                        <?php
                        foreach ($this->plugins as $plugin_data) :
                            $plugin = getPlugin($plugin_data, $this->config);
                            include $template_file;
                        endforeach;
                        ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php
    }

    public function outputReturnLink()
    {
        printf(
            '<p><a href="%s">%s</a></p>',
            admin_url("{$this->config['menu_parent']}?page={$this->config['menu_slug']}"),
            $this->strings['return_link']
        );
    }

}