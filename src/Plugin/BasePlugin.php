<?php

namespace Oblak\TGMPA\Plugin;

use Exception;
use Plugin_Upgrader;

use function \get_plugins;
use function Oblak\TGMPA\Utils\injectUpdateData;

class BasePlugin implements PluginInterface
{

    /**
     * Array of arrays of plugin data, keyed by plugin file name. See `get_plugin_data()`.
     * @var array[]
     */
    private static $wp_plugins = null;

    /**
     * TGMPA Config array
     * @var array
     */
    private static $tgmpa_config = null;

    private $name;

    private $slug;

    private $version;

    private $source;

    private $url;

    private $author;

    private $premium;

    private $image;

    private $required;

    private $repo_data;

    public function __construct($args, &$tgmpa_config)
    {

        foreach ($args as $var => $value) :

            if ( isset($args[$var]) ) :
                $this->$var = $value;
                continue;
            endif;

            $this->$var = null;

        endforeach;

        if ( is_null(self::$wp_plugins) ) :
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
            self::$wp_plugins = get_plugins();
        endif;

        if ( is_null(self::$tgmpa_config) ) :
            self::$tgmpa_config = &$tgmpa_config;
        endif;

        $this->repo_data = null;

    }

    public function getName()
    {
        return $this->name;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getVersion()
    {
        return ( is_null($this->version) )
            ? $this->maybeGetVersion()
            : $this->version;
    }


    public function getSource()
    {
        return ( is_null($this->source) )
            ? 'repo'
            : $this->source;
    }

    public function getUrl()
    {
        return ( $this->getSource() === 'repo' )
            ? "https://wordpress.org/plugins/{$this->getSlug()}"
            : '';
    }

    public function getImage()
    {
    
        if ( !is_null($this->image)) :
            return $this->image;
        endif;

        return ( $this->getSource() === 'repo' ) 
            ? $this->getRepoImage()
            : self::$tgmpa_config['default_image'];

    }

    public function getAuthor()
    {
        return ( is_null($this->author))
            ? $this->maybeGetAuthor()
            : $this->author;
    }

    public function isPremium()
    {
        return (bool)$this->premium;
    }

    public function isRequired()
    {
        return (bool)$this->required;
    }

    public function install($activate = false)
    {

        if ( !class_exists('Plugin_Upgrader') ) :
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        endif;

        if ( $this->getSource() === 'repo') :

            $repo_data = $this->getRepoData();

            if (!isset($repo_data['download_link'])) :
                throw new Exception('Waka waka');
            endif;

            $package_url = $repo_data['download_link'];

            $upgrader = new Plugin_Upgrader();
            
            $result = $upgrader->install($package_url);

            return;

        endif;

        $source = $this->getSource();

        if (!filter_var($source, FILTER_VALIDATE_URL)) :

            $upgrader = new Plugin_Upgrader();
            
            $result = $upgrader->install($source);

            return;

        endif;

    }

    public function update($activate = false)
    {

        if ( !class_exists('Plugin_Upgrader') ) :
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        endif;

        $plugin_data = $this->getPluginData();
        $plugin_slug = array_key_first($plugin_data);

        if ( $this->getSource() === 'repo') :

            $upgrader = new Plugin_Upgrader();
            
            $result = $upgrader->upgrade($plugin_slug);

            return;

        endif;

        $package_url = $this->getSource();

        injectUpdateData($plugin_slug, $package_url);

        $upgrader = new Plugin_Upgrader();

        $result = $upgrader->upgrade($plugin_slug);

        return;

    }

    public function activate($force = false)
    {

        $wp_slug = array_key_first($this->getPluginData());

        $result = activate_plugin($wp_slug);

        if ( is_wp_error($result) ) :
            throw new Exception($result->get_error_message());
        endif;

        return true;

    }

    public function uninstall($force = false)
    {

    }

    public function deactivate($force = false)
    {

        $wp_slug = array_key_first($this->getPluginData());

        deactivate_plugins($wp_slug);

    }

    public function isInstalled()
    {

        $filtered = $this->getPluginData();

        return !empty($filtered);
        
    }

    public function isActivated()
    {

        if (!$this->isInstalled()) :
            return false;
        endif;

        $plugin_name = array_key_first($this->getPluginData());

        return is_plugin_active($plugin_name);

    }

    public function needsUpdate()
    {
        $data = $this->getPluginData();

        if ( empty($data) ) :
            return false;
        endif;

        // Check if the plugin is not in wp repo
        if ( $this->getSource() !== 'repo' ) :
            return apply_filters("oblak/tgmpa/needs_update_{$this->getSlug()}", $this->getSlug(), $data);
        endif;

        $repo_updates = get_site_transient('update_plugins');

        return ( isset($repo_updates->response[array_key_first($data)]) );

    }

    public function getAction()
    {

        if ( !$this->isInstalled() ) :
            return 'install';
        endif;

        if ( $this->needsUpdate() ) :
            return 'update';
        endif;

        return ( $this->isActivated() )
            ? 'deactivate'
            : 'activate';

    }

    public function getPluginData()
    {
        return array_filter(self::$wp_plugins, function($data, $plugin) {
            return strpos($plugin, $this->getSlug()) !== false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function getRepoImage()
    {

        $headers = wp_remote_head("https://ps.w.org/{$this->getSlug()}/assets/icon-256x256.png");

        if (is_wp_error($headers)) :
            return self::$tgmpa_config['default_image'];
        endif;

        $code = $headers['response']['code'];

        return ($code == 200) 
            ? "https://ps.w.org/{$this->getSlug()}/assets/icon-256x256.png"
            : "https://s.w.org/plugins/geopattern-icon/{$this->getSlug()}.svg";

    }

    private function maybeGetAuthor()
    {

        if ( $this->getSource() !== 'repo' ) :
            return 'Unknown';
        endif;

        if ($this->isInstalled()) :

            $plugin_data = reset($this->getPluginData());

            return sprintf(
                '<a href="%s">%s</a>',
                $plugin_data['AuthorURI'],
                $plugin_data['Author']
            );
        
        endif;

        if ( !is_null($this->repo_data)) :
            return $this->repo_data['author'];
        endif;

        $this->repo_data = $this->getRepoData();

        return $this->repo_data['author'];

    }

    private function maybeGetVersion()
    {

        if ( $this->getSource() !== 'repo' ) :
            return 'Unknown';
        endif;

        if ($this->isInstalled()) :

            $plugin_data = reset($this->getPluginData());

            return $plugin_data['Version'];
        
        endif;

        if ( !is_null($this->repo_data)) :
            return $this->repo_data['version'];
        endif;

        $this->repo_data = $this->getRepoData();

        return $this->repo_data['version'];

    }

    private function getRepoData()
    {
        $repo_data = wp_remote_get("https://api.wordpress.org/plugins/info/1.0/{$this->getSlug()}.json");

        if (is_wp_error($repo_data)) :
            return [];
        endif;

        return json_decode($repo_data['body'], true);;
    }
    
}