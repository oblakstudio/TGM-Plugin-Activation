<?php

namespace Oblak\TGMPA\Plugin;

interface PluginInterface
{

    /**********
     * GETTERS
     **********/

    /**
     * Gets the plugin name
     * @return string
     */
    public function getName();

    /**
     * Gets the plugin slug
     * @return string
     */
    public function getSlug();

    /**
     * Gets the plugin version
     * @return string
     */
    public function getVersion();

    /**
     * Gets the plugin source url
     * @return string
     */
    public function getSource();

    /**
     * Gets the plugin url
     * @return string
     */
    public function getUrl();

    /**
     * Gets the plugin image
     * @return string
     */
    public function getImage();

    /**
     * Checks if the plugin is a paid (premium) plugin
     * @return bool
     */
    public function isPremium();

    /**
     * Checks if the plugin is required to use the theme
     * @return bool
     */
    public function isRequired();

    /**
     * Checks if the plugin is installed
     * @return bool
     */
    public function isInstalled();

    /**
     * Checks if the plugin is activated
     * @return bool
     */
    public function isActivated();

    /**
     * Checks if the plugin needs update
     * @return bool
     */
    public function needsUpdate();

    /**
     * Retrieves the most logical action for the plugin
     * 
     * Action can be: install, activate, deactivate
     *
     * @return string Action to perform
     */
    public function getAction();

    /************
     * ACTIVATORS
     ************/

    /**
     * Performs the installation of the plugin
     * 
     * @param  bool $activate Should the plugin be activated after installation
     * @return bool           True if plugin was installed, false if not           
     */
    public function install($activate = false);

    public function update($activate = false);

    public function activate($force = false);

    public function deactivate($force = false);

}