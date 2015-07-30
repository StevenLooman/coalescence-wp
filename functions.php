<?php

    if (!isset($content_width)) {
        $content_width = 900;
    }

    function coalescence_setup() {
        add_theme_support('menus');
        register_nav_menu('primary', 'Primary menu');
        register_nav_menu('secondary', 'Secondary menu');

        add_theme_support('automatic-feed-links');
        add_theme_support('post-thumbnails');

        add_theme_support('custom-header', array(
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => false,
        ));

        add_editor_style('editor-style.css');
    }
    add_action('after_setup_theme', 'coalescence_setup');


    function coalescence_widgets_init() {
        register_sidebar(array(
            'id' => 'frontpage',
            'name' => 'Front page sidebar',
        ));
        register_sidebar(array(
            'id' => 'error404',
            'name' => '404 sidebar',
        ));
        register_sidebar(array(
            'id' => 'left',
            'name' => 'Left sidebar',
        ));
        register_sidebar(array(
            'id' => 'right',
            'name' => 'Right sidebar',
        ));
        register_sidebar(array(
            'id' => 'search',
            'name' => 'Search page sidebar',
        ));
    }
    add_action('widgets_init', 'coalescence_widgets_init');


    require_once 'coalescence/config.php';

?>
