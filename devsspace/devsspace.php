<?php

/*

Plugin Name: Devsspace plugin
Description: Made for demo 
Version:1.0
Author:Liaqat Ali

 **/
//prevents direct access to the file from browser
if (! defined('ABSPATH')) {
    exit;
}

// Run this when the plugin is activated
function my_plugin_activate()
{
    error_log('Devsspace Plugin was activated...');

    // Register the post type first so WP knows about it
    devsspace_register_employee_post_type();

    // Then flush the rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'my_plugin_activate');

// Run this when the plugin is deactivated
function my_plugin_deactivate()
{
    error_log('Devsspace Plugin was deactivated.');
}
register_deactivation_hook(__FILE__, 'my_plugin_deactivate');


//CUSTOM POST TYPE
function devsspace_register_employee_post_type()
{
    $args = array(
        'labels' => array(
            'name' => 'Employees',
            'singular_name' => 'Employee',
            'menu_name' => 'Employees',
            'add_new' => 'Add new Employee',
            'add_new_item' => 'Add new Employee',
            'new_item'      => 'New Employee',
            'edit_item'     => 'Edit Employee',
            'view_item'     => 'View Employee',
            'all_items'     => 'All Employees',
        ),
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
        'rewrite'       => array('slug' => 'employees'),
    );
    register_post_type('employee', $args);
}

add_action('init', 'devsspace_register_employee_post_type');
