<?php
get_header();

echo '<h1 class="book-header">Books</h1>';


if ( have_posts() ) {
    echo "<div class='book-archive'>";
    echo "<ul class='book-list'>";

    while ( have_posts() ) {
        the_post();
        echo '<li class="book-item">';
        echo '<h2>' . get_the_title() . '</h2>';
        the_content();
        echo '<a href="' . get_permalink() . '">' . "Read more" . '</a>';
        echo '</li>';
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo '<p>No books found.</p>';
}


get_footer();