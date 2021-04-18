<?php
/**
 * IDE Autocomplete
 * 
 * @var Oblak\TGMPA\Plugin\BasePlugin $plugin
 **/

$bs_column    = floor(12 / $this->config['plugin_columns']);

$admin_action = in_array ($plugin->getAction(), ['update', 'install'])
    ? admin_url("{$this->config['menu_parent']}?page={$this->config['menu_slug']}")
    : admin_url('admin-post.php');

$action_link  = add_query_arg([
    'action' => "tgmpa_{$plugin->getAction()}",
    'plugin' => $plugin->getSlug(),
    'nonce'  => wp_create_nonce("tgmpa_{$plugin->getAction()}")
], $admin_action);

?>
<div class="col-xs-12 col-sm-<?php echo $bs_column; ?>">
    <div class="tgmpa-plugin">
        <div class="plugin-image">
            
            <img src="<?php echo $plugin->getImage(); ?>">

            <div class="plugin-meta">

            <?php if ($plugin->needsUpdate()) : ?>
                <span class="meta update"><?php _e('Update Available'); ?></span>
            <?php endif; ?>

            <?php if ($plugin->isRequired()) : ?>
                <span class="meta required"><?php _e('Required'); ?></span>
            <?php endif; ?>

            <?php if ($plugin->isPremium()) : ?>
                <span class="meta premium">Premium</span>
            <?php endif; ?>

            </div>

            <div class="plugin-actions">

                <a class="activation-link" href="<?php echo $action_link; ?>">
                    <?php echo __(ucfirst($plugin->getAction())); ?>
                </a>

            </div>

        </div>
        <h3 class="plugin-name">
            <span>
                <?php
                printf(
                    '%s%s',
                    $plugin->isActivated() ? _x('Active', 'plugin') . ': ' : '',
                    $plugin->getName()
                );
                ?>
            </span>
            <div class="plugin-info">
                v<?php echo $plugin->getVersion();?> | <?php echo $plugin->getAuthor(); ?>
            </div>
        </h3>
        <div class="theme-actions">
            
        </div>
    </div>
</div>

