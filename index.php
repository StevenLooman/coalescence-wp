<?php get_header(); ?>

<div class="coalescence-discrete">content</div>
<div id="content">
    <?php if ( have_posts() ): ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <header>
                    <h2>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <p class="meta">
                        <i class="icon-user"></i> <span class="author"><?php the_author(); ?></span>
                        <i class="icon-calendar"></i> <span class="date"><?php the_date(); ?></span> <span class="time"><?php the_time(); ?></span>
                        <i class="icon-folder-close"></i> <span class="category"><?php the_category(', '); ?></span>
                        <i class="icon-tags"></i> <span class="tags"><?php the_tags(''); ?></span>
                    </p>
                </header>

                <section>
                    <?php the_excerpt(); ?>
                </section>

                <footer>
                </footer>
            </article>
        <?php endwhile; wp_reset_query(); ?>
    <?php else: ?>
        <h2>No posts found</h2>
    <?php endif; ?>

    <?php if ( $wp_query->max_num_pages > 1 ) : ?>
        <div class="prev">
            <?php next_posts_link( __( '&larr; Older posts' ) ); ?>
        </div>
        <div class="next">
            <?php previous_posts_link( __( 'Newer posts &rarr;' ) ); ?>
        </div>
    <?php endif; ?>
</div>

<?php get_sidebar('frontpage'); ?>
<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>

<?php get_footer(); ?>
