<?php

use JetBrains\PhpStorm\NoReturn;

if (!class_exists('RC_Map_Settings')) {


    class RC_Map_Settings {

	    public static mixed $options;
	    /**
	     * @var false|mixed|null
	     */
	    public static mixed $options_6;
	    public static mixed $options_styles;

	    public function __construct() {
		    self::$options        = get_option( 'rc_map_options' );
		    self::$options_6      = get_option( 'rc_map_options_6' );
		    self::$options_styles = get_option( 'rc_map_group_styles_options' );
		    add_action( 'admin_init', array( $this, 'adminInit' ) );

		    // in the form admin_post_{hidden-html-input-value}
		    add_action( 'admin_post_run_custom_script', [ $this, 'handle_map_import_script' ], 10 );
		    add_action( 'admin_post_generate_code', [ $this, 'handle_map_process_script' ], 10 );
		    add_action( 'admin_post_get_geo_cords', [ $this, 'handle_map_get_geo_cords' ], 10 );

		    // HANDLE UPDATED POST_META AND OPTION
		    add_action( 'updated_post_meta', [ $this, 'handleAfterUpdatePostMeta' ], 10, 4 );
		    add_action( 'updated_option', [ $this, 'handleAfterUpdateOption' ], 10, 4 );

		    // Load Tab One in settings page.
		    include_once( RC_MAP_PATH . 'settings/class.RC_MAP_settings_main-options.php' );
		    $tabOne = new RC_MAP_SETTINGS_MAIN_OPTIONS();

		    // Load Tab Three in settings page.
		    include_once( RC_MAP_PATH . 'settings/class.RC_MAP_settings_snazzy-style-options.php' );
		    $tabThree = new RC_MAP_SETTINGS_SNAZZY_STYLE_OPTIONS();

		    // Load Google Map Tab
		    include_once( RC_MAP_PATH . 'includes/class.RC_DataEncryption.php' );
		    include( RC_MAP_PATH . 'settings/class.RC_MAP_settings_google-map-options.php' );
		    $tabTwo = new RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS( new RC_DataEncryption() );

		    // Load Additional Options Tab
		    include( RC_MAP_PATH . 'settings/class.RC_Map_settings_additional_options.php' );
		    $additionalOptionsTab = new RC_MAP_SETTINGS_ADDITIONAL_OPTIONS();

	    }

	    public function adminInit(): void {

		    // PAGE 4 ***************************** //
		    // THIS HTML IS IN THE settings-page.php file
		    add_settings_section(
			    id: 'rc_map_fourth_section',
			    title: 'Load Map Data',
			    callback: null,
			    page: 'rc_map_page4',
			    args: null

		    );

		    // PAGE 5 ***************************** //
		    // THIS HTML IS IN THE settings-page.php file
		    add_settings_section(
			    id: 'rc_map_fifth_section',
			    title: 'Generate map data',
			    callback: null,
			    page: 'rc_map_page5',
			    args: null
		    );


	    }

	    // UPDATED POST META ALL PAGES
	    public function handleAfterUpdatePostMeta(): void {
		    include_once( RC_MAP_PATH . 'includes/process.php' );
	    }

	    // UPDATED OPTION ALL PAGES
	    public function handleAfterUpdateOption(): void {
		    include_once( RC_MAP_PATH . 'includes/process.php' );
	    }


	    // PAGE 4 HTML ******************************* /
	    // SEE views/settings-page.php
	    // PAGE 5 HTML ******************************* /
	    // SEE views/settings-page.php

	    // Callback function to handle the custom script
	    #[NoReturn] public function handle_map_import_script(): void {

		    // TODO: NEED TO ADD THIS SECURITY CHECK
		    // Verify the nonce for security
//            if ( ! isset( $_POST['custom_action_nonce'] ) || ! wp_verify_nonce( $_POST['custom_action_nonce'], 'custom_action' ) ) {
//                wp_die( 'Invalid nonce.' );
//            }

		    require_once( RC_MAP_PATH . 'includes/import.php' );

		    // Redirect back to the admin page after processing
		    //wp_safe_redirect( admin_url( 'admin.php?page=rc_map_admin&tab=load_map_data_options' ) );
		    // Get the current page's path from REQUEST_URI
		    $current_page_path = $_SERVER['REQUEST_URI'];

		    // Generate the URL for the current page in the WordPress admin area
		    $admin_current_page_url = admin_url( $current_page_path );

		    // Redirect the user to the generated URL
		    wp_safe_redirect( admin_url( 'edit.php?post_type=rc-poi' ) );
		    //wp_safe_redirect($admin_current_page_url);
		    exit(); // It's important to use exit() after the redirect to ensure the script execution stops.


	    }

// Callback function to handle the custom script for processing data

		/**
		 * @return void
		 * purpose: builds html/css/javascript
		 *
		 * hook: admin_post_{custom-script-name}
		 */
		#[NoReturn] public function handle_map_process_script(): void {
			// Verify the nonce for security
			if ( ! isset( $_POST['process_nonce'] ) || ! wp_verify_nonce( $_POST['process_nonce'], 'process_nonce' ) ) {
				wp_die( 'Invalid nonce.' );
			}

			require_once( RC_MAP_PATH . 'includes/process.php' );

			// Redirect back to the admin page after processing
			wp_safe_redirect( admin_url( 'admin.php?page=rc_map_admin&tab=generate_map' ) );
			exit;
		}

		/**
		 * @return void
		 * hook :admin_post_{custom-script-name}
		 * purpose: run a custom php script from WordPress admin dashboard
		 */
		#[NoReturn] public function handle_map_get_geo_cords(): void {
			// Verify the nonce for security
			if ( ! isset( $_POST['get_geo_cords'] ) || ! wp_verify_nonce( $_POST['get_geo_cords'], 'get_geo_cords' ) ) {
				wp_die( 'Invalid nonce.' );
			}

			require_once( RC_MAP_PATH . 'includes/get_coordinates.php' );

			// Redirect back to the admin page after processing

			wp_safe_redirect( admin_url( 'admin.php?page=edit-rc-poi' ) );
			exit;
		}
	}
}
