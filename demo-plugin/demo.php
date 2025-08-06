<?php

/*
Plugin Name: Demo
Description: Demo plugin
Version:1.0.0
Author: Liaqat Ali
*/

//adding options page

function options_page()
{
?>
    <div class="wrap">
        <form method="POST" action="options.php">
            <h2><?php _e('Demo Options'); ?></h2>
            <p class="submit">
                <input type="submit" value="<?php esc_attr_e('Update Options'); ?>" />
            </p>
        </form>
    </div>
<?php }


function options_add_page()
{
    add_options_page(
        'Demo Options',
        'Demo Options',
        'manage_options',
        'demo_options',
        'options_page'
    );
    //registering setting
    register_setting('my-settings', 'my-plugin-options'); //(group,setting name)
}

add_action('admin_menu', 'options_add_page');

//setting default setting/option values
function setting_values_defaults()
{
    $defaults = array(
        'name' => 'DEFAULT',
        'email' => 'default@example.com',
        'color' => '#000000'
    );

    add_option("my-plugin-options", $defaults, "", true);
}

register_activation_hook(__FILE__, 'setting_values_defaults');

add_action('admin_notices', function () {
    $options = get_option('my-plugin-options');
    echo '<pre>hell';
    print_r($options);
    echo '</pre>';
});
