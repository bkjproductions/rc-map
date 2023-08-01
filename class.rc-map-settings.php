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

        }

        public function adminInit(): void
        {
            // More about settings API: http://presscoders.com/wordpress-settings-api-explained/
            register_setting('rc_map_group', 'rc_map_options', [$this, 'rcMapValidate']);
            register_setting('rc_map_group_6', 'rc_map_options_6', [$this, 'rcMapValidate_6']);
            register_setting('rc_map_group_styles', 'rc_map_group_styles_options', [$this, 'rcMapValidateStyles']);


            // PAGE 1 ***************************** //

            add_settings_section(
                id: 'rc_map_main_section',
                title: 'Main POI Details?',
                callback: [$this, 'displayAllTabbedData'],
                page: 'rc_map_page1'
            );


            add_settings_field(
                'rc_map_shortcode',
                'Shortcode',
                array($this, 'rcMapShortcodeCallback'),
                'rc_map_page1',
                'rc_map_main_section'
            );

            add_settings_field(
                id: 'rc_map_title',
                title: 'Map Title',
                callback: array($this, 'rcMapTitleCallback'),
                page: 'rc_map_page1',
                section: 'rc_map_main_section',
                args: null
            );

            add_settings_field(
                id: 'rc_map_latitude',
                title: 'Main POI Latitude',
                callback: array($this, 'rcMapLatitude'),
                page: 'rc_map_page1',
                section: 'rc_map_main_section',
                args: null
            );
            add_settings_field(
                id: 'rc_map_longitude',
                title: 'Main POI Longitude',
                callback: array($this, 'rcMapLongitude'),
                page: 'rc_map_page1',
                section: 'rc_map_main_section',
                args: null
            );

            add_settings_field(
                'rc_map_style',
                'Map Style',
                array($this, 'rcMapStyleCallback'),
                'rc_map_page1',
                'rc_map_main_section',
                null
            );
            // PAGE 2 ***************************** //

            add_settings_section(
                id: 'rc_map_second_section',
                title: 'Google MAP Configuration',
                callback: [$this, 'displayAllTabbedData'],
                page: 'rc_map_page2',
                args: null

            );

            add_settings_field(
                id: 'rc_map_api_key',
                title: 'Google Maps API Key',
                callback: array($this, 'rcMapApiKeyCallback'),
                page: 'rc_map_page2',
                section: 'rc_map_second_section',
                args: null
            );

            add_settings_field(
                'rc_map_zoom',
                'Map Zoom Level',
                array($this, 'rcMapZoomCallback'),
                'rc_map_page2',
                'rc_map_second_section'
            );

            add_settings_field(
                'rc_map_center_latitude',
                'Map Center: Latitude',
                array($this, 'rcMapCenterLatitudeCallback'),
                'rc_map_page2',
                'rc_map_second_section'
            );
            add_settings_field(
                'rc_map_center_longitude',
                'Map Center: Longitude',
                array($this, 'rcMapCenterLongitudeCallback'),
                'rc_map_page2',
                'rc_map_second_section'
            );


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

        // PAGE 1 HTML ******************************** /
        public function rcMapShortcodeCallback(): void
        {
            ?>
            <span>Use the shortcode [rc_map] to display the slider in any page/post/widget</span>
            <?php
        }

        public function rcMapTitleCallback(): void
        {
            ?>

            <input
                    type="text"
                    name="rc_map_options[rc_map_title]"
                    id="rc_map_title"
                    value="<?php echo isset(self::$options['rc_map_title']) ? esc_attr(self::$options['rc_map_title']) : ''; ?>"
            >
            <?php
        }

        public function rcMapLatitude(): void
        {
            ?>

            <input
                    type="text"
                    name="rc_map_options[rc_map_latitude]"
                    id="rc_map_latitude"
                    value="<?php echo isset(self::$options['rc_map_latitude']) ? esc_attr(self::$options['rc_map_latitude']) : ''; ?>"
            >
            <?php
        }

        public function rcMapLongitude(): void
        {
            ?>

            <input
                    type="text"
                    name="rc_map_options[rc_map_longitude]"
                    id="rc_map_longitude"
                    value="<?php echo isset(self::$options['rc_map_latitude']) ? esc_attr(self::$options['rc_map_longitude']) : ''; ?>"
            >
            <?php
        }

        // PAGE 2 HTML ******************************** /

        public function rcMapApiKeyCallback(): void
        {

            ?>
            <input
                    style="width: 30rem"
                    type="text"

                    name="rc_map_options[rc_map_api_key]"
                    id="rc_map_api_key"
                    value="<?php echo isset(self::$options['rc_map_api_key']) ? esc_attr(self::$options['rc_map_api_key']) : ''; ?>"
            >
            <?php
        }

        public function rcMapZoomCallback(): void
        {
            ?>
            <input
                    style="width: 5rem;"
                    type="number"
                    step="0.5"
                    min="1"
                    max="30"
                    name="rc_map_options[rc_map_zoom]"
                    id="rc_map_zoom"
                    value="<?php echo isset(self::$options['rc_map_zoom']) ? esc_attr(self::$options['rc_map_zoom']) : ''; ?>"
            >
            <?php
        }

        public function rcMapCenterLongitudeCallback(): void
        {
            ?>
            <input
                    style="width: 30rem"
                    type="text"

                    name="rc_map_options[rc_map_center_longitude]"
                    id="rc_map_api_key"
                    value="<?php echo isset(self::$options['rc_map_center_longitude']) ? esc_attr(self::$options['rc_map_center_longitude']) : ''; ?>"
            >
            <?php
        }

        public function rcMapCenterLatitudeCallback(): void
        {
            ?>
            <input
                    style="width: 30rem"
                    type="text"

                    name="rc_map_options[rc_map_center_latitude]"
                    id="rc_map_api_key"
                    value="<?php echo isset(self::$options['rc_map_center_latitude']) ? esc_attr(self::$options['rc_map_center_latitude']) : ''; ?>"
            >
            <?php
        }

        public function rcMapStyleCallback(): void
        {
            $options = self::$options_styles;
            $selected_style = self::$options['rc_map_style'];


            ?>

            <select
                    id="rc_map_style"
                    name="rc_map_options[rc_map_style]">
                <?php

                foreach ($options as $key => $value):
                    if (!$key) continue;
                    ?>
                    <option  <?php echo ($selected_style == $key) ? 'selected'  : '' ?> value="<?php echo esc_attr($key); ?>"
                    >
                    <?php echo ucfirst($key)?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div>Changing map design may require clearing cache</div>
            <?php
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

        public function displayAllTabbedData(): void
        {
            // These fields need to load
            //$map_style_json  = get_option('rc_map_load_style');

            ?>
            <input type="hidden"
                   id="rc_map_zoom"
                   name="rc_map_options[rc_map_zoom]"
                   value="<?php echo isset(self::$options['rc_map_zoom']) ? esc_attr(self::$options['rc_map_zoom']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_api_key]"
                    id="rc_map_api_key"
                    value="<?php echo isset(self::$options['rc_map_api_key']) ? esc_attr(self::$options['rc_map_api_key']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_title]"
                    id="rc_map_title"
                    value="<?php echo isset(self::$options['rc_map_title']) ? esc_attr(self::$options['rc_map_title']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_style]"
                    id="rc_map_style"
                    value="<?php echo isset(self::$options['rc_map_style']) ? esc_attr(self::$options['rc_map_style']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_longitude]"
                    id="rc_map_longitude"
                    value="<?php echo isset(self::$options['rc_map_longitude']) ? esc_attr(self::$options['rc_map_longitude']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_latitude]"
                    id="rc_map_latitude"
                    value="<?php echo isset(self::$options['rc_map_latitude']) ? esc_attr(self::$options['rc_map_latitude']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_center_longitude]"
                    id="rc_map_latitude"
                    value="<?php echo isset(self::$options['rc_map_center_longitude']) ? esc_attr(self::$options['rc_map_center_longitude']) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_center_latitude]"
                    id="rc_map_latitude"
                    value="<?php echo isset(self::$options['rc_map_center_latitude']) ? esc_attr(self::$options['rc_map_center_latitude']) : ''; ?>"
            >
            <!--            <input type="hidden"-->
            <!--                   id="rc_map_load_style"-->
            <!--                   name="rc_map_load_style"-->
            <!--                   value="--><?php //echo esc_attr($map_style_json) ?? ''; ?><!--"-->
            <!--            >-->

            <?php
        }
    }
}
