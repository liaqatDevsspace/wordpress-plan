<?php
/* Page template for editing books via AJAX */
get_header();

// Load all book posts
$books = get_posts([
    'post_type' => 'book',
    'posts_per_page' => -1,
]);
?>

<div class="content-area">
    <main class="site-main">
        <h1>Edit Books (AJAX)</h1>

        <div id="books-list">
            <?php foreach ($books as $book): ?>
                <div class="book-item" data-id="<?php echo esc_attr($book->ID); ?>">
                    <input type="text" class="book-title" value="<?php echo esc_attr($book->post_title); ?>" />
                    <button class="update-book">Update</button>
                    <span class="status-message"></span>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- <?php get_footer(); ?> -->
