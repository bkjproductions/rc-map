<?php

use JetBrains\PhpStorm\NoReturn;

if (!class_exists('RC_Map_Settings')) {


    class RC_Map_Settings
    {

        public static mixed $options;
        /**
         * @var false|mixed|null
         */
        public static mixed $options_6;
        public static mixed $options_styles;

        public function __construct()
        {
            self::$options = get_option('rc_map_options');
            self::$options_6 = get_option('rc_map_options_6');
            self::$options_styles = get_option('rc_map_group_styles_options');
            add_action('admin_init', array($this, 'adminInit'));

            // in the form admin_post_{hidden-html-input-value}
            add_action('admin_post_run_custom_script', [$this, 'handle_map_import_script'], 10);
            add_action('admin_post_generate_code', [$this, 'handle_map_process_script'], 10);
            add_action('admin_post_get_geo_cords', [$this, 'handle_map_get_geo_cords'], 10);

            // HANDLE UPDATED POST_META
            add_action('updated_post_meta', [$this, 'handleAfterUpdatePostMeta'], 10, 4);
            add_action('updated_option', [$this, 'handleAfterUpdateOption'], 10, 4);


            // Load each setting/option
            include_once (RC_MAP_PATH . 'settings/class.RC_MAP_settings_main-options.php');
            include_once (RC_MAP_PATH . 'includes/class.RC_DataEncryption.php');
            include_once (RC_MAP_PATH . 'settings/class.RC_MAP_settings_google-map-options.php');
            $tabOne = new RC_MAP_SETTINGS_MAIN_OPTIONS();
            $tabTwo = new RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS(new RC_DataEncryption());

        }

        public function adminInit(): void
        {
            // More about settings API: http://presscoders.com/wordpress-settings-api-explained/
            register_setting('rc_map_group_6', 'rc_map_options_6', [$this, 'rcMapValidate_6']);
            register_setting('rc_map_group_styles', 'rc_map_group_styles_options', [$this, 'rcMapValidateStyles']);



            // PAGE 3 ***************************** //
            add_settings_section(
                id: 'rc_map_third_section',
                title: 'Load Map Styles',
                callback: null,
                //callback: [$this, 'displayAllTabbedData'],
                page: 'rc_map_page3',
                args: null

            );

            add_settings_field(
                id: 'key',
                title: 'Unique Map Name',
                callback: [$this, 'rcMapStyleKeyCallback'],
                page: 'rc_map_page3',
                section: 'rc_map_third_section',
                args: []
            );
            add_settings_field(
                id: 'value',
                title: 'Snazzy JSON',
                callback: [$this, 'rcMapStyleValueCallback'],
                page: 'rc_map_page3',
                section: 'rc_map_third_section',
                args: []
            );


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

            // PAGE 6 ***************************** //
            //
            add_settings_section(
                id: 'rc_map_sixth_section',
                title: 'Additional Options',
                callback: null,
                page: 'rc_map_page6',
                args: null

            );

            add_settings_field(
                id: 'rc_map_use_data_tables_js',
                title: 'Use DataTables.js in admin',
                callback: [$this, 'rcCheckBoxCallback'],
                page: 'rc_map_page6',
                section: 'rc_map_sixth_section',
                args: [
                    'theName' => 'rc_map_use_data_tables_js'
                ]

            );
        }

        // UPDATED POST META ALL PAGES
        public function handleAfterUpdatePostMeta(): void
        {
            include_once(RC_MAP_PATH . 'includes/process.php');

        }

        // UPDATED OPTION ALL PAGES
        public function handleAfterUpdateOption(): void
        {
            include_once(RC_MAP_PATH . 'includes/process.php');


        }



        // PAGE 3 HTML ******************************** /

        public function rcMapStyleKeyCallback(): void
        { ?>
            <div>
                <input type="text" name="rc_map_group_styles_options[key]" id="rc_map_group_styles_options[key]"/>
            </div>


        <?php }

        public function rcMapStyleValueCallback(): void
        { ?>

            <textarea name="rc_map_group_styles_options[value]" id="rc_map_group_styles_options[value]" cols="60"
                      rows="30"><?php echo "Paste Snazzy JSON Here !"; ?></textarea>

            <?php
        }

        private function rc_map_options_map_styles_options()
        {
            $options = get_option('rc_map_group_styles_options');

            if (!is_array($options)) {
                $options = array();
            }

            return $options;
        }

        public function rcMapValidateStyles($input): array
        {
            //error_log("RUNNING IN STYLE VALIDATION");

            // Get existing map styles
            $map_styles = $this->rc_map_options_map_styles_options();

            // Append to style
            $map_styles[$input['key']] = $input['value'];

            return $map_styles;

        }
        // PAGE 4 HTML ******************************* /
        // SEE views/settings-page.php
        // PAGE 5 HTML ******************************* /
        // SEE views/settings-page.php

        // PAGE 6 HTML ******************************* /
        public function rcCheckBoxCallback($args): void
        {
            $option_name = "rc_map_options_6[" . $args['theName'] . ']';
//            $option_name = "rc_map_options[rc_map_use_data_tables_js]";

            $checked = isset(self::$options_6[$args['theName']]) ? '1' : '0';

            ?>
            <input type="checkbox" id="<?php echo esc_attr($option_name); ?>"
                   name="<?php echo esc_attr($option_name); ?>" value="1" <?php checked($checked, 1); ?> >

            <?php
        }

        // Callback function to handle the custom script
        #[NoReturn] public function handle_map_import_script(): void
        {

            // TODO: NEED TO ADD THIS SECURITY CHECK
            // Verify the nonce for security
//            if ( ! isset( $_POST['custom_action_nonce'] ) || ! wp_verify_nonce( $_POST['custom_action_nonce'], 'custom_action' ) ) {
//                wp_die( 'Invalid nonce.' );
//            }

            require_once(RC_MAP_PATH . 'includes/import.php');

            // Redirect back to the admin page after processing
            //wp_safe_redirect( admin_url( 'admin.php?page=rc_map_admin&tab=load_map_data_options' ) );
            wp_safe_redirect(admin_url('edit.php?post_type=rc-poi'));
            exit;
        }

// Callback function to handle the custom script for processing data

        /**
         * @return void
         * purpose: builds html/css/javascript
         *
         * hook: admin_post_{custom-script-name}
         */
        #[NoReturn] public function handle_map_process_script(): void
        {
            // Verify the nonce for security
            if (!isset($_POST['process_nonce']) || !wp_verify_nonce($_POST['process_nonce'], 'process_nonce')) {
                wp_die('Invalid nonce.');
            }

            require_once(RC_MAP_PATH . 'includes/process.php');

            // Redirect back to the admin page after processing
            wp_safe_redirect(admin_url('admin.php?page=rc_map_admin&tab=generate_map'));
            exit;
        }

        /**
         * @return void
         * hook :admin_post_{custom-script-name}
         * purpose: run a custom php script from WordPress admin dashboard
         */
        #[NoReturn] public function handle_map_get_geo_cords(): void
        {
            // Verify the nonce for security
            if (!isset($_POST['get_geo_cords']) || !wp_verify_nonce($_POST['get_geo_cords'], 'get_geo_cords')) {
                wp_die('Invalid nonce.');
            }

            require_once(RC_MAP_PATH . 'includes/get_coordinates.php');

            // Redirect back to the admin page after processing
            wp_safe_redirect(admin_url('admin.php?page=rc_map_admin&tab=generate_map'));
            exit;
        }

        public function rcMapValidate_6($input): array
        {


            if (!$input) return [];
            // Use switch for different types of fields: text|url|number
            $new_input = array();

            foreach ($input as $key => $value) {
                switch ($key) {
                    case 'rc_map_use_data_tables_js':
                        if (empty($value)) {
                            $value = 0;
                        }
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                    default:
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                }
            }
            return $new_input;
        }

        public function rcMapValidate($input): array
        {
            // Use switch for different types of fields: text|url|number
            $new_input = array();

            foreach ($input as $key => $value) {
                switch ($key) {
                    case 'rc_map_title':
                        if (empty($value)) {
                            $value = 'Please enter a value.';
                        }
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                    default:
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                }
            }
            return $new_input;
        }

    }
}
