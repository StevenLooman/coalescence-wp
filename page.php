<?php get_header(); ?>

<div class="coalescence-discrete">page content</div>
<div id="content">
    <?php if ( have_posts() ): ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <header class="entry-meta">
                    <h1><?php the_title(); ?></h1>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <footer class="entry-meta">
                </footer>

            </article>
        <?php endwhile; ?>
        <?php wp_reset_query(); ?>
    <?php endif; ?>
</div>

<?php get_sidebar('frontpage'); ?>
<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>

<?php get_footer(); ?>
