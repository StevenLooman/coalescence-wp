<?php get_header(); ?>

<div class="coalescence-discrete">search content</div>
<div id="content">
    <div id="searchresults">
        <h1><?php printf( __( 'Search Results for: %s' ), '' . get_search_query() . '' ); ?></h1>
        <?php if ( have_posts() ) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article>
                    <header class="entry-meta">
                        <h2>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="meta">
                            <i class="icon-user"></i> <span class="author"><?php the_author(); ?></span>
                            <i class="icon-calendar"></i> <span class="date"><?php the_date(); ?></span> <span class="time"><?php the_time(); ?></span>
                            <i class="icon-folder-close"></i> <span class="category"><?php the_category(', '); ?></span>
                            <i class="icon-tags"></i> <span class="tags"><?php the_tags(''); ?></span>
                        </div>
                    </header>

                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                    </div>

                    <footer class="entry-meta">
                    </footer>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <h2>Nothing Found</h2>
            <p>Sorry, but nothing matched your search criteria. Please try again with some different keywords.</p>
        <?php endif; ?>

        <?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div class="prev">
                <?php previous_posts_link( __( 'Prev' ) ); ?>
            </div>
            <div class="next">
                <?php next_posts_link( __( 'Next' ) ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_sidebar('search'); ?>
<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>

<?php get_footer(); ?>
