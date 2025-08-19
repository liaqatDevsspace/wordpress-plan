    <?php

    if(!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }

    class Bookstore_Payment_Gateway extends WC_Payment_Gateway{

        //declaring properties
        public $testmode;
        public $test_publishable_key;
        public $test_private_key;
        public $publishable_key;
        public $private_key;
        public $instructions;
        public $client_id;
        public $client_secret;
        public $plan_id;
        public $paypal_product_id;
        public $paypal_plan_id;
        public function __construct(){
            // our gateway's ID
            $this->id = 'bookstore_gateway';
            $this->icon=apply_filters( 'woocommerce_bookpayment_icon',  plugin_dir_path( __FILE__ )."assets/images/book-icon.png" );
            $this->has_fields = false;
            $this->method_title = __('Bookstore Payment Gateway', 'bookstore');
            $this->method_description = __('A custom payment gateway for the Bookstore plugin.', 'bookstore');

            $this->init_form_fields();
            $this->init_settings();
            $this->supports = array(
                'products',
            );

            //load the settings
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->testmode = $this->get_option('testmode');
            $this->test_publishable_key = $this->get_option('test_publishable_key');
            $this->test_private_key = $this->get_option('test_private_key');
            $this->publishable_key = $this->get_option('publishable_key');
            $this->private_key = $this->get_option('private_key');
            $this->instructions = $this->get_option('instructions');
            $this->client_id = $this->get_option('client_id');
            $this->client_secret = $this->get_option('client_secret');
            $this->plan_id = $this->get_option('plan_id');
            $this->paypal_product_id = $this->get_option('paypal_product_id');
            $this->paypal_plan_id = $this->get_option('paypal_plan_id');

            //saves the settings
            add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields(){
            //getting the stored values for product and plan IDs
            $paypal_ids = get_option('bookstore_paypal_ids', []);
    $product_id = $paypal_ids['paypal_product_id'] ?? '';
    $plan_id    = $paypal_ids['paypal_plan_id'] ?? '';

            $this->form_fields=array(
                "enabled"=>array(
                    'title'=>"Enable/Disable",
                    'label'=>"Enable Bookstare gateway",
                    'default'=>"no",
                    'type'=> 'checkbox',
                    'description' => '',
                ),
                'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'Credit Card',
                'desc_tip'    => true,
            ),
                "description"=>array(
                    'title'=>'Description',
                    'type'        => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default'     => 'Credit Card',
            ),
            "instructions"=>array(
                'title'       => 'Instructions',    
                'type'        => 'textarea',
                'description' => 'This controls the instructions which the user sees during checkout.',
                'default'     => 'Please pay using your credit card.',
                'desc_tip'    => true,)
                ,

                'testmode' => array(
                'title'       => 'Test mode',
                'label'       => 'Enable Test Mode',
                'type'        => 'checkbox',
                'description' => 'Place the payment gateway in test mode using test API keys.',
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'test_publishable_key' => array(
                'title'       => 'Test Publishable Key',
                'type'        => 'text'
            ),
            'test_private_key' => array(
                'title'       => 'Test Private Key',
                'type'        => 'password',
            ),
            'publishable_key' => array(
                'title'       => 'Live Publishable Key',
                'type'        => 'text'
            ),
            'private_key' => array(
                'title'       => 'Live Private Key',
                'type'        => 'password'
            ),
            'client_id' => array(
                'title'       => 'Client ID',   
                'type'        => 'text',
                'description' => 'Your client ID for the payment gateway.',
                'default'     => '',
                'desc_tip'    => true),
            'client_secret' => array(
                'title'       => 'Client Secret',   
                'type'        => 'password',
                'description' => 'Your client secret for the payment gateway.',
                'default'     => '',
                'desc_tip'    => true),
        
            'paypal_product_id' => array(
                'title'       => __('PayPal Product ID', 'bookstore'),
                'type'        => 'text',
                'description' => __('Auto-generated when the plugin is activated. Used to identify the product in PayPal.'),
                'default'     =>$product_id, // load stored value
                'custom_attributes' => array('readonly' => 'readonly'),
            ),
            'paypal_plan_id' => array(
                'title'       => __('PayPal Plan ID', 'bookstore'),
                'type'        => 'text',
                'description' => __('Auto-generated when the plugin is activated. Used when creating subscriptions.'),
                'default'     => $plan_id, // load stored value
                'custom_attributes' => array('readonly' => 'readonly'),
            ),
            

        );

        //thank you page
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

        }

        // Display instructions on the thank you page
    public function thankyou_page( $order_id ) {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) );
        }
    }


        public function process_payment($order_id){
            $order = wc_get_order($order_id);
            // Mark as on-hold (we're awaiting the payment)
            $order->update_status('on-hold', __('Awaiting bookstore payment', 'bookstore'));

            //call api

            // Reduce stock levels
        wc_reduce_stock_levels($order_id);

            // Add order note
        $order->add_order_note(__('Stock reduced for ordered items.', 'bookstore'));

        WC()->cart->empty_cart(); // Empty the cart

            // Return thank you page redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }
        public function is_available() {
    return ( "yes" === $this->enabled );
}
    }

    add_filter('woocommerce_payment_gateways', 'add_bookstore_gateway_to_woo');

    function add_bookstore_gateway_to_woo($gateways) {
        $gateways[] = 'Bookstore_Payment_Gateway';
        return $gateways;
    }

