<?php get_header(); ?>

<div class="coalescence-discrete">archive content</div>
<div id="content">
    <h1>
        <?php if( is_author() ): ?>
            Author: <?php echo $author_name ?>
        <?php elseif( is_category() ): ?>
            Category: <?php single_cat_title(); ?>
        <?php elseif( is_tag() ): ?>
            Tag: <?php single_tag_title(); ?>
        <?php elseif( is_year() ): ?>
            Archive for <?php the_time('Y'); ?>
        <?php elseif( is_month() ): ?>
            Archive for <?php the_time('F Y'); ?>
        <?php else: ?>
            Archive
        <?php endif; ?>
    </h1>

    <?php if ( have_posts() ): ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article>
                <header class="entry-meta">
                    <h2>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <div class="meta">
                        <span class="author"><?php the_author() ?></span>
                        <span class="date"><?php the_time() ?></span>
                        <span class="category"><?php the_category() ?></span>
                    </div>
                </header>

                <div class="entry-content">
                    <?php the_excerpt(); ?>
                </div>

                <footer class="entry-meta">
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

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>

<?php get_footer(); ?>
