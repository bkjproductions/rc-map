<?php


/*
Plugin Name: RC Map
Plugin URI: http://robertocannella.com/wordpress
Description: Point of Interest with Google Map.js plugin
Version: 1.0
Author: Roberto Cannella
Author URI: http://robertocannella.com_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
Requires: 6.2
Text Domain: rc-map
Domain Path: /languages
*/

/*
{RC Map} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{RC Map} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {RC Map}. If not, see {ttp://robertocannella.com/wordpress}.
*/

if (!defined('ABSPATH')) {
    die("-not allowed");
    exit;
}

/**
 *  Main RC_Map class
 */
if (!class_exists('RC_Map')) {
    class RC_Map{

        private RC_Map_Settings $rc_map_settings;

        public function __construct()
        {
            // DEFINE CONSTANTS
            $this->defineConstants();


            // BUILD POST TYPE
            require_once(RC_MAP_PATH . '/post_types/class.RC_POI_Post_Type.php');
            $rc_poi_post_type = new RC_POI_Post_Type();

            // BUILD TAXONOMY TYPE
            require_once(RC_MAP_PATH . '/post_types/class.RC_POI_Term_Type.php');
            $rc_poi_term_type = new RC_POI_Term_Type();

            // ADMIN MENU
            add_action('admin_menu', array($this, 'addMenu'));

            // SETTINGS PAGE + STATIC METHOD options
            require_once(RC_MAP_PATH . 'class.rc-map-settings.php');
            $this->rc_map_settings = new RC_Map_Settings();

            // SHORTCODE
            require_once(RC_MAP_PATH . 'shortcodes/class.rc-map-shortcode.php');
            $rc_poi_shortcode = new RC_Map_Shortcode();


            // ENQUEUE ADMIN SCRIPTS
            add_action('admin_enqueue_scripts',[$this, 'enqueueDatatables'],1);
            add_action('admin_enqueue_scripts',[$this, 'enqueueScriptsAndStyles'],2);

            // ENQUEUE FRONTEND SCRIPTS
            add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendScripts']);



        }

        function enqueueScriptsAndStyles(): void {
            // Get the current screen object
            $current_screen = get_current_screen();
            // Check if we are on the specific page ('map-js_page_edit-rc-poi')
            if ($current_screen && $current_screen->id === 'map-js_page_edit-rc-poi') {
                // Enqueue the scripts and styles
                wp_enqueue_script('rc-index-js', RC_MAP_URL . 'src/js/index.js', [], '1-' .time(), false);
                wp_enqueue_style('rc-styles-js', RC_MAP_URL . 'src/css/styles.css', array(), '1-' .time());

                wp_localize_script('rc-index-js', 'globalSiteData', [
                    'siteUrl' => get_site_url(),
                    'nonceX' => wp_create_nonce('rc_rest'),
                    'ajax_url' => admin_url('admin-ajax.php')
                ]);
            }


        }
        function enqueueFrontendScripts():void {
            // Load Google Map file

            wp_enqueue_script('rc-google-map-js', RC_MAP_URL . 'src/js/initMap.js', [], '1-' . time(), true);
            // Enqueue Google Maps API with the initMap callback
            $api_key = esc_html(RC_Map_Settings::$options['rc_map_api_key']);
            wp_enqueue_script(
                'google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&callback=initMap',
                ['rc-google-map-js'],
                null,
                true
            );

            wp_enqueue_style('rc-map-css', RC_MAP_URL . 'src/css/rc-map.css',[], '1-' . time());
        }
        function enqueueDatatables(): void
        {
            $query = new WP_Query([
                'post-type' => 'rc-poi',
                'posts_per_page' => 1
            ]);
            if ($query->have_posts()) {
                wp_enqueue_script('jquery'); // Make sure jQuery is loaded first
                wp_enqueue_script('datatables', RC_MAP_URL . 'vendor/DataTables/datatables.js', array('jquery'), '1.13.5', true);
                wp_enqueue_style('datatables', RC_MAP_URL . 'vendor/DataTables/datatables.css', array(), '1.13.5');
            }
        }
        /**
         * Define global constants here
         */
        public function defineConstants(): void
        {
            define ( 'RC_MAP_PATH' , plugin_dir_path( __FILE__ ));
            define ( 'RC_MAP_URL' , plugin_dir_url( __FILE__ ));
            define ( 'RC_MAP_VERSION' , '1.0.0' );
            define ( 'RC_TEXT_DOMAIN', 'rc-map');

        }
        /**
         * Activation
         */
        public static function activate(){

            // Code to register custom post types, taxonomies, and rewrite rules
            // ...

            // flush_rewrite_rules() does not work great when activating plugin, use
            // update_option to clear table
            flush_rewrite_rules(); // Flush the rewrite rules after modifications
            //  update_option( ' rewrite_rules' );// Flush the rewrite rules after modifications

        }
        /**
         * Deactivations
         */
        public static function deactivate(){

            // Code to unregister custom post types, taxonomies, and rewrite rules
            // ...
            flush_rewrite_rules(); // Flush the rewrite rules after modifications
            unregister_post_type('rc-map' );
        }
        /**
         * Uninstall
         */
        public static function uninstall(){

        }
        /**
         * Build menus
         */
        public function addMenu(){
            add_menu_page(
                page_title: 'Map.js Options',
                menu_title: 'Map.js',
                capability: 'manage_options', // More on roles : https://wordpress.org/documentation/article/roles-and-capabilities/#capability-vs-role-table
                menu_slug: 'rc_map_admin',
                callback: array( $this, 'rcMapSettingsPage' ),
                icon_url: 'dashicons-admin-site-alt',
                position: 10
            );

            // CUSTOM WP_LIST_TABLE Class

            if ( isset($this->rc_map_settings::$options_6['rc_map_use_data_tables_js']) &&
                    $this->rc_map_settings::$options_6['rc_map_use_data_tables_js']
                ){
                add_submenu_page(
                    parent_slug: 'rc_map_admin',
                    page_title: __('Manage POIs', RC_TEXT_DOMAIN),
                    menu_title: 'POIs',
                    capability: 'manage_options',
                    menu_slug: 'edit-rc-poi',
                    callback: [$this, 'listPoiPage'],
                    position: null
                );
            }else {
                add_submenu_page(
                    parent_slug: 'rc_map_admin',
                    page_title: 'Manage POIs',
                    menu_title: 'Manage POIs',
                    capability: 'manage_options',
                    menu_slug: 'edit.php?post_type=rc-poi',
                    callback: null,
                    position: null
                );
            }




            add_submenu_page(
                parent_slug: 'rc_map_admin',
                page_title: 'Add New POI',
                menu_title: 'Add New POI',
                capability: 'manage_options',
                menu_slug: 'post-new.php?post_type=rc-poi',
                callback: null,
                position: null
            );

            add_submenu_page(
                parent_slug: 'rc_map_admin',
                page_title: 'Location Types',
                menu_title: 'Location Types',
                capability: 'manage_options',
                menu_slug: 'edit-tags.php?taxonomy=poi&post_type=rc-poi',
                callback: null,
                position: null
            );

        }
        public function rcMapSettingsPage():void {

            if (!current_user_can('manage_options')) { return;}

            if( isset( $_GET['settings-updated'] ) ){
                add_settings_error( 'rc_map_options', 'rc_map_message', 'Settings Saved', 'success' );
            }

            settings_errors( 'rc_map_options' );

            // HTML
            require( RC_MAP_PATH . 'views/settings-page.php' );

        }

        public function listPoiPage():void {

            if (!current_user_can('activate_plugins')) { return;}

            // HTML
            require( RC_MAP_PATH . 'views/poi-list-page.php' );

        }
    }

}

if (class_exists('RC_Map')) {

    register_activation_hook(__FILE__, ['RC_Map', 'activate']);
    register_deactivation_hook(__FILE__, ['RC_Map', 'deactivate']);
    register_uninstall_hook(__FILE__, ['RC_Map', 'uninstall']);


    $rc_map = new RC_Map();



}