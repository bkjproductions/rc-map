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
        public function __construct()
        {
            $this->defineConstants();


            // POST TYPE
            require_once ( RC_MAP_PATH . '/post_types/class.RC_POI_Post_Type.php' );
            $rc_poi_post_type = new RC_POI_Post_Type();
        }
        /**
         * Define global constants here
         */
        public function defineConstants(): void
        {
            define ( 'RC_MAP_PATH' , plugin_dir_path( __FILE__ ));
            define ( 'RC_MAP_URL' , plugin_dir_url( __FILE__ ));
            define ( 'RC_MAP_VERSION' , '1.0.0' );

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
    }

}

if (class_exists('RC_Map')) {

    register_activation_hook(__FILE__, ['RC_Map', 'activate']);
    register_deactivation_hook(__FILE__, ['RC_Map', 'deactivate']);
    register_uninstall_hook(__FILE__, ['RC_Map', 'uninstall']);

    $rc_map = new RC_Map();

}