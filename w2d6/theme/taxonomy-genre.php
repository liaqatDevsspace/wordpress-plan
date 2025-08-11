<?php get_header(); ?>

<div class="taxonomy-archive">

    <h1>
        <?php
        // Display the current taxonomy term title
        single_term_title();
        ?>
    </h1>

    <?php
    // Show term description if available
    the_archive_description('<div class="term-description">', '</div>');
    ?>

    <?php if ( have_posts() ) : ?>

        <ul class="posts-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <div class="excerpt"><?php the_excerpt(); ?></div>
                </li>
            <?php endwhile; ?>
        </ul>

        <?php
        // Pagination (if needed)
        the_posts_pagination();
        ?>

    <?php else : ?>

        <p>No posts found in this genre.</p>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
