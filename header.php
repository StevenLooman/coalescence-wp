<?php require 'coalescence/init.php'; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <title>
          <?php
            if (!is_home()) {
                wp_title( '|', true, 'right' );
            }
            bloginfo( 'name' );
          ?>
        </title>
        <?php
            if (is_singular() && get_option('thread_comments')) {
                wp_enqueue_script('comment-reply');
            }
        ?>
        <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>">
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
        <header id="site-header">
            <div class="coalescence-discrete">site header image</div>
            <div id="site-header-image">
                <img src="<?php header_image(); ?>" />
            </div>
            <div class="coalescence-discrete">site title</div>
            <div id="site-title">
                <h1>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                </h1>
            </div>

            <div class="coalescence-discrete">site description</div>
            <div id="site-description">
                <?php bloginfo( 'description' ); ?>
            </div>

            <div class="coalescence-discrete">primary menu</div>
            <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => 'nav',
                    'container_id'   => 'menu_primary'
                ) );
            ?>
            <div class="coalescence-discrete">secondary menu</div>
            <?php
                if (has_nav_menu('secondary')) {
                    wp_nav_menu( array(
                        'theme_location' => 'secondary',
                        'container'      => 'nav',
                        'container_id'   => 'menu_secondary'
                    ) );
                } else {
                    ?> No menu for secondary theme location, not rendering secondary menu <?php
                }
            ?>

            <div class="coalescence-discrete">primary menu - Bootstrap</div>
            <?php
                require_once 'wp_bootstrap_navwalker.php';
                if (has_nav_menu('primary')) {
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'nav nav-tabs nav-stacked',
                        'walker'         => new wp_bootstrap_navwalker()
                    ) );
                } else {
                    ?> No menu for primary theme location, not rendering Bootstrap menu <?php
                }
            ?>

            <div class="coalescence-discrete">Breadcrumbs</div>
            <?php require_once 'breadcrumbs.php'; ?>
        </header>
