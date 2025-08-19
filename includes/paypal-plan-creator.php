<?php

if(!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function bookstore_create_paypal_plan_safe() {
   error_log("Creating PayPal Plan...");
    $settings = get_option('woocommerce_bookstore_gateway_settings', []);

    if (!empty($settings['paypal_plan_id'])) {
        error_log('PayPal plan already exists: ' . $settings['paypal_plan_id']);
        return;
    }

    $client_id     = $settings['client_id'] ?? '';
    $client_secret = $settings['client_secret'] ?? '';

    if (!$client_id || !$client_secret) {
        error_log('PayPal Client ID/Secret missing, cannot create plan.');
        return;
    }

    // OAuth token
    $token_request = wp_remote_post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
        ],
        'body' => ['grant_type' => 'client_credentials']
    ]);
    $token_body   = json_decode(wp_remote_retrieve_body($token_request));
    $access_token = $token_body->access_token ?? '';
    if (!$access_token) return;

    // Create product
    $product_request = wp_remote_post('https://api-m.sandbox.paypal.com/v1/catalogs/products', [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
        'body' => json_encode([
            'name'        => 'Bookstore Subscription',
            'description' => 'Monthly subscription for Bookstore',
            'type'        => 'SERVICE',
            'category'    => 'SOFTWARE'
        ])
    ]);
    $product_body = json_decode(wp_remote_retrieve_body($product_request), true);
    $product_id   = $product_body['id'] ?? '';
    if (!$product_id) return;

    // Create plan
    $plan_request = wp_remote_post('https://api-m.sandbox.paypal.com/v1/billing/plans', [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ],
        'body' => json_encode([
            'product_id' => $product_id,
            'name'       => 'Monthly Plan',
            'description'=> 'Monthly subscription for Bookstore',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit'  => 'MONTH',
                        'interval_count' => 1
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence'    => 1,
                    'total_cycles'=> 0,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value'    => '10.00',
                            'currency_code' => 'USD'
                        ]
                    ]
                ]
            ],
            'payment_preferences' => [
                'auto_bill_outstanding'     => true,
                'setup_fee'                 => [
                    'value' => '0',
                    'currency_code' => 'USD'
                ],
                'setup_fee_failure_action'  => 'CONTINUE',
                'payment_failure_threshold' => 3
            ]
        ])
    ]);
    $plan_body = json_decode(wp_remote_retrieve_body($plan_request), true);
    $plan_id   = $plan_body['id'] ?? '';
    if (!$plan_id) return;

    // Save plan and product IDs in settings
update_option('bookstore_paypal_ids', [
    'paypal_product_id' => $product_id,
    'paypal_plan_id'    => $plan_id,
]);




    error_log('PayPal Plan created successfully: ' . $plan_id);
}