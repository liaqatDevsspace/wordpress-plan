<?php
/*
Plugin Name: Bookstore
Description: A simple plugin to manage a bookstore.
Version: 1.0.0
Author: Liaqat Ali
text-domain: bookstore
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Activation hook - create PayPal product & plan
require_once __DIR__ . '/includes/paypal-plan-creator.php';
register_activation_hook(__FILE__, 'bookstore_create_paypal_plan_safe');




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

    // Include the main Bookstore gateway class
    // require_once plugin_dir_path( __FILE__ ) . 'bookstore-payment-class.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/bookstore-payment-class.php';


 
}
?>