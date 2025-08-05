<?php

/**
 * Plugin Name: Devsspace Contact Form
 * Description: A simple contact form plugin
 * Version: 1.0
 * Author: Liaqat Ali
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the shortcode
add_shortcode('devssspace_contact_form', 'devssspace_contact_form_shortcode');

function devssspace_contact_form_shortcode()
{
    $output = '';

    // Check if the form is submitted and nonce is valid
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['devssspace_name'], $_POST['devssspace_message'], $_POST['devss_nonce_field']) &&
        wp_verify_nonce($_POST['devss_nonce_field'], 'devss_form_action')
    ) {
        // Sanitize input fields
        $name = sanitize_text_field($_POST['devssspace_name']);
        $message = sanitize_text_field($_POST['devssspace_message']);
        return "<p>Thank you " . esc_html($name) . ". Your message has been sent successfully!</p>";
    }

    // Display the form
    $output .= '
        <form method="post" action="">
            <p>
                <label for="devssspace_name">Your Name</label><br>
                <input type="text" name="devssspace_name" required>
            </p>
            <p>
                <label for="devssspace_message">Your Message</label><br>
                <textarea name="devssspace_message" required></textarea>
            </p>';

    // Add nonce field
    $output .= wp_nonce_field('devss_form_action', 'devss_nonce_field', true, false);

    $output .= '
            <p>
                <input type="submit" value="Send">
            </p>
        </form>';

    return $output;
}
