<?php

/*
Plugin Name: Ajax plugin
Description: For fetching custom posts
Version: 1.0.0
Author: Liaqat Ali
*/

add_action('wp_enqueue_scripts', 'run_ajax');

function run_ajax()
{
    if (is_page('ajax-posts')) {
        wp_enqueue_script(
            'ajax-script',
            plugin_dir_url(__FILE__) . 'assets/js/ajax.js',
            array('jquery'),
            "1.2",
            true
        );
        // localizing data so it can be used inside js
        wp_localize_script('ajax-script', 'myData', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('get_books_nonce'),
        ));
    }
}

//

//CUSTOM POST TYPE
function register_book_cpt()
{
    $labels = [
        'name' => 'Books',
        'singular_name' => 'Book',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Book',
        'edit_item' => 'Edit Book',
        'new_item' => 'New Book',
        'view_item' => 'View Book',
        'search_items' => 'Search Books',
        'not_found' => 'No books found',
        'menu_name' => 'Books'
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'books'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-book',
        'show_in_rest' => true, // Enables Gutenberg
    ];

    register_post_type('book', $args);
}
add_action('init', 'register_book_cpt');


//handling ajax request 
add_action('wp_ajax_get_books', 'send_books');
add_action('wp_ajax_nopriv_get_books', 'send_books');

function send_books()
{
    // Security check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'get_books_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $args = array(
        'post_type' => 'book',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $books = array();
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $books[] = array(
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'link' => get_permalink()
            );
        }
        wp_reset_postdata();
    }
    wp_send_json_success($books); // Send JSON response

}


// modifying content with the_content filter
add_filter('the_content', 'modify_content');

function modify_content($content)
{
    if (is_singular('book')) {
        $custom_message = '<div class="custom-message">Thank you for reading this post!</div>';
        $content .= $custom_message; // append message to content
    }
    return $content;
}
