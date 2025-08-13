<?php
/*
Plugin Name: Bookstore
Description: A simple plugin to manage a bookstore.
Version: 1.0.0
Author: Liaqat Ali
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//stop the code if woocommerce is not active
if(!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'bookstore_woocommerce_notice');
    return;
}

function bookstore_woocommerce_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Bookstore requires WooCommerce to be installed and activated.', 'bookstore'); ?></p>
    </div>
    <?php
}

//checking if woocomerce classes exist
add_action('plugins_loaded','bookstore_payment_init',11); //11 is priority no

function bookstore_payment_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    // Include the main Bookstore class
    require_once plugin_dir_path( __FILE__ ) . 'bookstore-payment-class.php';

 
}
?>