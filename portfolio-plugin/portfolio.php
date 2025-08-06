<?php
global $wpdb;
/*
Plugin Name: Portfolio
Description: Basic plugin for adding project posts
Version: 1.0
Author: Liaqat Ali
*/

// if this file is called directly, abort!
if (!defined('ABSPATH')) {
    exit;
}


//settings page
function portfolio_settings_page()
{
    add_menu_page('Portfolio Settings', 'Portfolio', 'manage_options', 'portfolio-settings', 'render_portfolio_page');
}

add_action('admin_menu', 'portfolio_settings_page');

function render_portfolio_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
?>
    <div class="wrap">
        <h1>Portfolio Plugin Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="portfolio_title">Section Title</label></th>
                    <td><input type="text" id="portfolio_title" name="portfolio_title" value="My Projects" class="regular-text" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="projects_per_page">Projects Per Page</label></th>
                    <td><input type="number" id="projects_per_page" name="projects_per_page" value="6" class="small-text" /></td>
                </tr>

                <tr>
                    <th scope="row"><label for="portfolio_layout">Layout Style</label></th>
                    <td>
                        <select name="portfolio_layout" id="portfolio_layout">
                            <option value="grid">Grid</option>
                            <option value="list">List</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Show Filter By Technology?</th>
                    <td>
                        <label><input type="checkbox" name="show_filter" checked /> Yes</label>
                    </td>
                </tr>
            </table>

        </form>
    </div>
<?php
}

// CUSTOM POST TYPE
function register_project_cpt()
{
    $labels = [
        'name' => 'Projects',
        'singular_name' => 'Project',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Project',
        'edit_item' => 'Edit Project',
        'new_item' => 'New Project',
        'view_item' => 'View Project',
        'search_items' => 'Search Projects',
        'not_found' => 'No projects found',
        'menu_name' => 'Projects'
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'projects'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-portfolio',
        'show_in_rest' => true, // Enables Gutenberg
    ];

    register_post_type('project', $args);
}
add_action('init', 'register_project_cpt');

//  Add Meta Box
add_action('add_meta_boxes', 'add_project_meta_box');
function add_project_meta_box()
{
    add_meta_box(
        'project_details',              // ID
        'Project Details',              // Title shown in editor
        'render_project_meta_box',      // Function to render the fields
        'project',                      // Post type
        'normal',                       // Position (normal, side)
        'default'                       // Priority
    );
}

// 2. Render Meta Box HTML
function render_project_meta_box($post)
{
    // Retrieve existing values (if any)
    $client = get_post_meta($post->ID, '_project_client', true);
    $url    = get_post_meta($post->ID, '_project_url', true);
    $tech   = get_post_meta($post->ID, '_project_tech', true);

    // Add security nonce
    wp_nonce_field('save_project_meta_box', 'project_meta_box_nonce');

    echo '<p><label for="project_client">Client Name:</label><br>';
    echo '<input type="text" name="project_client" id="project_client" value="' . esc_attr($client) . '" size="40"></p>';

    echo '<p><label for="project_url">Project URL:</label><br>';
    echo '<input type="url" name="project_url" id="project_url" value="' . esc_url($url) . '" size="40"></p>';

    echo '<p><label for="project_tech">Technologies Used:</label><br>';
    echo '<input type="text" name="project_tech" id="project_tech" value="' . esc_attr($tech) . '" size="40"></p>';
}

// 3. Save Meta Box Data
add_action('save_post', 'save_project_meta_fields');
function save_project_meta_fields($post_id)
{
    // Check if nonce is set and valid
    if (!isset($_POST['project_meta_box_nonce']) || !wp_verify_nonce($_POST['project_meta_box_nonce'], 'save_project_meta_box')) {
        return;
    }

    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save Client Name
    if (isset($_POST['project_client'])) {
        update_post_meta($post_id, '_project_client', sanitize_text_field($_POST['project_client']));
    }

    // Save Project URL
    if (isset($_POST['project_url'])) {
        update_post_meta($post_id, '_project_url', esc_url_raw($_POST['project_url']));
    }

    // Save Technologies Used
    if (isset($_POST['project_tech'])) {
        update_post_meta($post_id, '_project_tech', sanitize_text_field($_POST['project_tech']));
    }
}


// 🔽 Display meta fields on the frontend
add_filter('the_content', 'show_project_meta_fields');
function show_project_meta_fields($content)
{
    // Only modify content for single project posts
    if (get_post_type() === 'project' && is_singular('project')) {
        $client = get_post_meta(get_the_ID(), '_project_client', true);
        $url    = get_post_meta(get_the_ID(), '_project_url', true);
        $tech   = get_post_meta(get_the_ID(), '_project_tech', true);
        $meta_output = "<div class='project-meta-wrapper'>";
        $meta_output .= "<h6>Client: " . esc_html($client) . "</h6>";
        $meta_output .= "<p>URL: <a href='" . esc_url($url) . "' target='_blank'>" . esc_html($url) . "</a></p>";
        $meta_output .= "<p>Technologies: " . esc_html($tech) . "</p>";
        $meta_output .= "</div>";
        return   $content . $meta_output;
    }

    return $content;
}

//styling
add_action('wp_enqueue_scripts', 'portfolio_enqueue_styles');
function portfolio_enqueue_styles()
{
    if (is_singular('project')) {
        wp_enqueue_style('portfolio-style', plugin_dir_url(__FILE__) . 'style.css');
    }
}


//display posts on portfolio page
function render_project_list_shortcode()
{
    ob_start(); // Start output buffering

    $args = array(
        'post_type'      => 'project',
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="project-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="project-card">';
            echo '<a href="' . get_permalink() . '">';
            echo '<h3>' . get_the_title() . '</h3>';
            if (has_post_thumbnail()) {
                the_post_thumbnail('medium');
            }

            echo '<p>' . get_the_excerpt() . '</p>';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No projects found.</p>';
    }

    return ob_get_clean(); // Return the buffered content
}
add_shortcode('project_list', 'render_project_list_shortcode');
