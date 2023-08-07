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
		private RC_MAP_setting_google_map_options $rc_map_setting_google_map_options;
		private RC_DataEncryption $data_encryption;

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
	        require_once (RC_MAP_PATH . 'settings/class.RC_MAP_settings_google-map-options.php');
			require_once (RC_MAP_PATH . 'settings/class.RC_Map_settings_additional_options.php');
            require_once(RC_MAP_PATH . 'class.rc-map-settings.php');
            $this->rc_map_settings = new RC_Map_Settings();

            // SHORTCODE
            require_once(RC_MAP_PATH . 'shortcodes/class.rc-map-shortcode.php');
            $rc_poi_shortcode = new RC_Map_Shortcode();

			// TO DECRYPT OPTIONS
	        include_once(RC_MAP_PATH . 'includes/class.RC_DataEncryption.php');
			$this->data_encryption = new RC_DataEncryption();

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
            $api_key = $this->getApiKey();
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
        public static function activate(): void {

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

            if ( isset(RC_MAP_SETTINGS_ADDITIONAL_OPTIONS::$options['rc_map_use_data_tables_js']) &&
                        RC_MAP_SETTINGS_ADDITIONAL_OPTIONS::$options['rc_map_use_data_tables_js'])
			{
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
            settings_errors( 'rc_map_settings-main_options_section' );
            settings_errors( 'rc_map_settings-google-maps_options_section' );

            // HTML
            require( RC_MAP_PATH . 'views/settings-page.php' );

        }

        public function listPoiPage():void {

            if (!current_user_can('activate_plugins')) { return;}

            // HTML
            require( RC_MAP_PATH . 'views/poi-list-page.php' );

        }
	    private function getApiKey(): string
	    {

		    $encrypted_key = RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS::$options['rc_map_api_key'];
		    if ( $encrypted_key === '' ) {
			    $decrypted_key = 'API Not Set';
		    } else {
			    $decrypted_key = $this->data_encryption->decrypt($encrypted_key);
		    }
		    return $decrypted_key;

	    }
	    public static function createSamplePosts():void {
		    // Create an array to store the post objects
		    $sample_posts = array();

		    // Sample post data to create three posts
		    $sample_data = array(
			    array(
				    'post_title' => 'P.J. Clarke\'s On The Hudson',
				    'address' => '250 Vesey St.',
				    'city' => 'New York',
				    'state' => 'NY',
				    'zip_code' => '10080',
				    'url' => '',
				    'phone' => '212.776.4927',
				    'categories' => 'Dinner',
				    'geo_code' => '',
			    ),
			    array(
				    'post_title' => 'Malibu Barbie Café',
				    'address' => '19 Fulton St.',
				    'city' => 'New York',
				    'state' => 'NY',
				    'zip_code' => '10038',
				    'url' => '',
				    'phone' => '',
				    'categories' => 'Dinner',
				    'geo_code' => '',
			    ),
			    array(
				    'post_title' => 'Battery Gardens',
				    'address' => '1 Battery Pl.',
				    'city' => 'New York',
				    'state' => 'NY',
				    'zip_code' => '10004',
				    'url' => '',
				    'phone' => '212.809.5508',
				    'categories' => 'Dinner',
				    'geo_code' => '',
			    ),
			    array(
				    'post_title' => 'Keste Pizza e Vino',
				    'address' => '77 Fulton Street',
				    'city' => 'New York',
				    'state' => 'NY',
				    'zip_code' => '10038',
				    'url' => 'http://kestepizzeria.com',
				    'phone' => '',
				    'categories' => 'Dinner',
				    'geo_code' => '40.7090783, -74.004535',
			    ),
		    );

		    // Loop through the sample data and create posts
		    foreach ($sample_data as $datum) {

			    // Now you have an array of data items for each row, and you can process them accordingly
			    $post_title = $datum['post_title'];
			    // Process other data items as needed
			    $poi_address = $datum['address'];
			    $poi_city = $datum['city'];
			    $poi_state = $datum['state'];
			    $poi_zip_code = $datum['zip_code'];
			    $poi_url = $datum['url'];
			    $poi_phone = $datum['phone'];
			    $poi_category = $datum['categories'];
			    $poi_geo_code = $datum['geo_code'];


			    $post_args = array(
				    'post_title' => $post_title, // Adjust the index based on your data columns
				    // Map other data to appropriate custom post fields
				    'post_type' => 'rc-poi',
				    'post_status' => 'publish',
			    );
			    $post_id = wp_insert_post($post_args);

			    if ($post_id) {
				    // Optionally, update custom fields for the post if needed
				    update_post_meta($post_id, 'rc_poi_location_address', $poi_address);
				    update_post_meta($post_id, 'rc_poi_location_city', $poi_city);
				    update_post_meta($post_id, 'rc_poi_location_state', $poi_state);
				    update_post_meta($post_id, 'rc_poi_location_zip_code', $poi_zip_code);
				    update_post_meta($post_id, 'rc_poi_location_phone', $poi_phone);
				    update_post_meta($post_id, 'rc_poi_location_url', $poi_url);
				    update_post_meta($post_id, 'rc_poi_location_geo_code', $poi_geo_code);


				    if (isset($datum['categories'])) { // Assuming "categories" column is at index 7

					    $tags = explode(',', $datum['categories']);
					    $tags = array_map('trim', $tags);

					    foreach ($tags as $tag) {
						    // Disable term caching to avoid potential issues with term_exists
						    $GLOBALS['wpdb']->cache_terms = false;

						    // Check if the term already exists in the "location_type" taxonomy
						    $existing_term = term_exists($tag, 'poi');

						    // Re-enable term caching
						    $GLOBALS['wpdb']->cache_terms = true;

						    if ($existing_term) {
							    // If the term exists, assign it to the current post
							    // error_log(print_r($existing_term,true));
							    wp_set_object_terms($post_id, get_term($existing_term['term_id'])->term_id, 'poi', true);
						    } else {
							    // If the term doesn't exist, create a new term and assign it to the current post
							    $new_term = wp_insert_term($tag, 'poi');
							    error_log("inside foreach loop NEW term: " . print_r($new_term,true));

							    if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
								    wp_set_object_terms($post_id, $new_term['term_id'], 'poi', true);
							    }
						    }
					    }
				    }
			    }


		    }
		    // Store the array of post objects in a WordPress option for later use
		    include_once (RC_MAP_PATH . 'includes/get_coordinates.php');

	    }
    }

}

if (class_exists('RC_Map')) {

    register_activation_hook(__FILE__, ['RC_Map', 'activate']);
    register_deactivation_hook(__FILE__, ['RC_Map', 'deactivate']);
    register_uninstall_hook(__FILE__, ['RC_Map', 'uninstall']);


    $rc_map = new RC_Map();



}