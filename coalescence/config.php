<?php

    require_once 'utils.php';
    require_once 'config_actions.php';


    class CoaConfig {

        public function __construct() {
            add_action('admin_menu', array($this, 'load_menu'));
            add_action('admin_head', array($this, 'css'));
        }


        private function get_theme_templates() {
            $templates = array();
            $directory = dir(get_template_directory() . '/theme/');
            while (($entry = $directory->read()) !== false) {
                if (substr($entry, -4) == 'html' || substr($entry, -3) == 'htm') {
                    array_push($templates, array(
                        'href' => get_stylesheet_directory_uri() . '/theme/' . $entry,
                        'title' => $entry,
                    ));
                }
            }
            return $templates;
        }


        private function get_content_templates() {
            $templates = array(
                'home',
                'blog',
                'page',
                'single',
                'archive',
                'error404',
                'search-results',
                'default',
            );
            return $templates;
        }


        private function get_content_pages() {
            $pages = array();
            foreach (get_pages() as $page) {
                array_push($pages, array(
                    'href' => $page->guid,
                    'title' => $page->post_title,
                ));
            }

            array_push($pages, array(
                'href' => null,
                'title' => '---',
            ));

            foreach (get_posts() as $post) {
                array_push($pages, array(
                    'href' => $post->guid,
                    'title' => $post->post_title,
                ));
            }

            return $pages;
        }

        private function checks() {
            $theme = wp_get_theme();
            if ($theme->Name != "Coalescence") {
                new CoaMessage('error', 'Coalescence theme is not activated. Please activate it first before you can use this plugin.');
            }
        }

        function conf() {
            if (!current_user_can('manage_options'))  {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            $this->checks();

            // gather all javascript
            $coalescence_js = get_stylesheet_directory_uri() . '/coalescence/config_page.js';
            $coalescence_rules_js = get_stylesheet_directory_uri() . '/coalescence/config_page_rules.js';

            // read rules
            $rules = get_option('coalescence_rules');

            // get all available templates
            $theme_templates = $this->get_theme_templates();
            $content_templates = $this->get_content_templates();

            // read all the pages
            $pages = $this->get_content_pages();

            // rules.xml link
            $rules_xml_file = admin_url('themes.php?page=coalescence-config&coalescence_export_rules=1');

            // include page
            require 'config_page.php';
        }

        function load_menu() {
            add_submenu_page('themes.php', __('Coalescence'), __('Coalescence'), 'manage_options', 'coalescence-config', array($this, 'conf'));
        }


        function css() {
            $coalescence_css = get_stylesheet_directory_uri() .  '/coalescence/config_page.css';
            echo('<link rel="stylesheet" type="text/css" href="' . $coalescence_css . '">');
        }
    }


    new CoaConfig();

?>
