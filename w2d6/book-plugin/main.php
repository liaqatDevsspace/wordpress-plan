<?php
/*
Plugin Name: Book Plugin
Description: A plugin to manage books(CPTs).
Version: 1.0.0
Author: Liaqat Ali
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

//registering genre & author taxonomy
function w2d6_register_book_taxonomies()
{
    $genre_labels = array(
        'name' => _x('Genres', 'taxonomy general name', 'book-plugin'),
        'singular_name' => _x('Genre', 'taxonomy singular name', 'book-plugin'),
        'search_items' => __('Search Genres', 'book-plugin'),
        'all_items' => __('All Genres', 'book-plugin'),
        'parent_item' => __('Parent Genre', 'book-plugin'),
        'parent_item_colon' => __('Parent Genre:', 'book-plugin'),
        'edit_item' => __('Edit Genre', 'book-plugin'),
        'update_item' => __('Update Genre', 'book-plugin'),
        'add_new_item' => __('Add New Genre', 'book-plugin'),
        'new_item_name' => __('New Genre Name', 'book-plugin'),
        'menu_name' => __('Genre', 'book-plugin'),
    );

    $genre_args = array(
        'hierarchical' => true,
        'labels' => $genre_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'genre'),
        'show_in_rest' => true,
    );

    register_taxonomy('genre', array('book'), $genre_args);

    //author toxonomy
    $author_labels = array(
        'name' => _x('Authors', 'taxonomy general name', 'book-plugin'),
        'singular_name' => _x('Author', 'taxonomy singular name', 'book-plugin'),
        'search_items' => __('Search Authors', 'book-plugin'),
        'popular_items' => __('Popular Authors', 'book-plugin'),
        'all_items' => __('All Authors', 'book-plugin'),
        'edit_item' => __('Edit Author', 'book-plugin'),
        'update_item' => __('Update Author', 'book-plugin'),
        'add_new_item' => __('Add New Author', 'book-plugin'),
        'new_item_name' => __('New Author Name', 'book-plugin'),
        'separate_items_with_commas' => __('Separate authors with commas', 'book-plugin'),
        'add_or_remove_items' => __('Add or remove authors', 'book-plugin'),
        'choose_from_most_used' => __('Choose from the most used authors', 'book-plugin'),
        'menu_name' => __('Authors', 'book-plugin'),
    );

    $author_args = array(
        'hierarchical' => false, // non-hierarchical like tags
        'labels' => $author_labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array('slug' => 'book-author'), // avoid WP's built-in /author/ conflict
        'show_in_rest' => true,
    );

    register_taxonomy('book-author', array('book'), $author_args);
}



// Hook into the 'init' action to register the taxonomies

add_action('init', 'w2d6_register_book_taxonomies');

register_activation_hook(__FILE__, function () {
    // Flush rewrite rules on activation
    w2d6_register_book_taxonomies(); // so slugs exist
    flush_rewrite_rules();
});


//creating book custom post type
function register_books_cpt()
{
    register_post_type('book', array(
        'labels' => array(
            'name' => __('Books', 'book-plugin'),
            'singular_name' => __('Book', 'book-plugin'),
            'add_new' => __('Add New', 'book-plugin'),
            'add_new_item' => __('Add New Book', 'book-plugin'),
            'edit_item' => __('Edit Book', 'book-plugin'),
            'new_item' => __('New Book', 'book-plugin'),
            'view_item' => __('View Book', 'book-plugin'),
            'search_items' => __('Search Books', 'book-plugin'),
            'not_found' => __('No books found', 'book-plugin'),
            'not_found_in_trash' => __('No books found in Trash', 'book-plugin'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true, // Enable REST API support

    ));
}
add_action('init', 'register_books_cpt');

//adding custom meta box for book details
// Hook to add the meta box
add_action('add_meta_boxes', function () {
    add_meta_box(
        'book_isbn_meta_box', // ID
        __('Book ISBN', 'book-plugin'), // Title
        'render_book_isbn_meta_box', // Callback
        'book', // CPT
        'side', // Context (side column)
        'default' // Priority
    );
});

// Render the field
function render_book_isbn_meta_box($post)
{
    $isbn = get_post_meta($post->ID, '_book_isbn', true);

    // Nonce for security
    wp_nonce_field('save_book_isbn', 'book_isbn_nonce');

    echo '<label for="book_isbn_field">' . __('ISBN Number:', 'book-plugin') . '</label>';
    echo '<input type="text" id="book_isbn_field" name="book_isbn_field" value="' . esc_attr($isbn) . '" style="width:100%;" />';
}

// Save the ISBN when the post is saved
add_action('save_post_book', function ($post_id) {
    // Check nonce
    if (!isset($_POST['book_isbn_nonce']) || !wp_verify_nonce($_POST['book_isbn_nonce'], 'save_book_isbn')) {
        return;
    }

    // Avoid autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save ISBN
    if (isset($_POST['book_isbn_field'])) {
        update_post_meta($post_id, '_book_isbn', sanitize_text_field($_POST['book_isbn_field']));
    }
});
