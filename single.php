<?php get_header(); ?>

<div class="coalescence-discrete">single content</div>
<div id="content">
    <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
        <article>
            <header>
                <h1><?php the_title(); ?></h1>
                <p class="meta">
                    <i class="icon-user"></i> <span class="author"><?php the_author(); ?></span>
                    <i class="icon-calendar"></i> <span class="date"><?php the_date(); ?></span> <span class="time"><?php the_time(); ?></span>
                    <i class="icon-folder-close"></i> <span class="category"><?php the_category(', '); ?></span>
                    <i class="icon-tags"></i> <span class="tags"><?php the_tags(''); ?></span>
                </p>
            </header>

            <section>
                <?php the_content(); ?>
            </section>

            <footer>
            </footer>
        </article>

        <?php comments_template( '', true ); ?>
    <?php endwhile; wp_reset_query(); ?>
</div>

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>

<?php get_footer(); ?>
