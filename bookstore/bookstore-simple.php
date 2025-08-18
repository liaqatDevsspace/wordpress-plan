<?php
/*
Plugin Name: Bookstore
Description: Mini Bookstore plugin with CPT, taxonomy, and REST API endpoints.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

//  Register CPT
add_action('init', 'bookstore_register_cpt');
function bookstore_register_cpt() {
    $args = array(
        'label' => 'Books',
        'public' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'has_archive' => true,
    );
    register_post_type('book', $args);
}

// Register Taxonomy
add_action('init', 'bookstore_register_taxonomy');
function bookstore_register_taxonomy() {
    $args = array(
        'label' => 'Genres',
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
    );
    register_taxonomy('genre', 'book', $args);
}

// 3️⃣ REST API Endpoints
add_action('rest_api_init', function () {

    // GET all books (public)
    register_rest_route('bookstore/v1', '/books', array(
        'methods' => 'GET',
        'callback' => 'bookstore_get_books',
        'permission_callback' => '__return_true', // public endpoint
    ));

    // GET single book (public)
    register_rest_route('bookstore/v1', '/books/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'bookstore_get_single_book',
        'permission_callback' => '__return_true', // public endpoint
    ));

    // POST new book (requires user to have edit_posts capability)
    register_rest_route('bookstore/v1', '/books', array(
        'methods' => 'POST',
        'callback' => 'bookstore_create_book',
        'permission_callback' => function () {
            return current_user_can('edit_posts'); // secure endpoint
        }
    ));
});

// 4️⃣ Callback Functions
function bookstore_get_books() {
    $query = new WP_Query(array(
        'post_type' => 'book',
        'posts_per_page' => -1,
    ));

    $books = array();
    while ($query->have_posts()) {
        $query->the_post();
        $books[] = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'content' => get_the_content(),
            'genres' => wp_get_post_terms(get_the_ID(), 'genre', array('fields' => 'names')),
        );
    }
    wp_reset_postdata();
    return rest_ensure_response($books);
}

function bookstore_get_single_book($request) {
    $id = intval($request['id']);
    $post = get_post($id);
    if (!$post || $post->post_type !== 'book') {
        return new WP_Error('not_found', 'Book not found', array('status' => 404));
    }
    return rest_ensure_response(array(
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'genres' => wp_get_post_terms($post->ID, 'genre', array('fields' => 'names')),
    ));
}

function bookstore_create_book($request) {
    $data = $request->get_json_params();
    $post_id = wp_insert_post(array(
        'post_type' => 'book',
        'post_title' => sanitize_text_field($data['title']),
        'post_content' => sanitize_textarea_field($data['content']),
        'post_status' => 'publish',
    ));

    if (is_wp_error($post_id)) {
        return new WP_Error('cannot_create', 'Failed to create book', array('status' => 500));
    }

    if (!empty($data['genres'])) {
        wp_set_post_terms($post_id, array_map('sanitize_text_field', $data['genres']), 'genre');
    }

    return rest_ensure_response(array('id' => $post_id));
}


//custom time interval for cron job
function bookstore_custom_cron_schedules( $schedules ) {
    $schedules['every_two_minutes'] = array(
        'interval' => 120, //in seconds
        'display'  => __( 'Every 2 Minutes' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'bookstore_custom_cron_schedules' );


// 1. Schedule the cron event (runs hourly)
function bookstore_activate_cron() {
    if ( ! wp_next_scheduled( 'bookstore_update_post_status' ) ) {
        wp_schedule_event( time(), 'every_two_minutes', 'bookstore_update_post_status' );
    }
}
add_action( 'wp', 'bookstore_activate_cron' );

// 2. Clear cron on deactivation
function bookstore_deactivate_cron() {
    wp_clear_scheduled_hook( 'bookstore_update_post_status' );
}
register_deactivation_hook( __FILE__, 'bookstore_deactivate_cron' );

// 3. Hook into our cron event
add_action( 'bookstore_update_post_status', 'bookstore_update_post_status_callback' );

// 4. Define the function that updates posts
function bookstore_update_post_status_callback() {
    $args = array(
        'post_type'      => 'book',
        'post_status'    => 'draft',
        'posts_per_page' => 5, // limit to avoid heavy load
    );

    $drafts = get_posts( $args );

    foreach ( $drafts as $draft ) {
        wp_update_post( array(
            'ID'          => $draft->ID,
            'post_status' => 'publish'
        ) );
    }
    error_log( 'Draft posts updated to published status.' );
}