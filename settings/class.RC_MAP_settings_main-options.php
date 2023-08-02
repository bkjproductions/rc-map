<?php
 /*
  * Main Options Tab
  *
  */

if (!class_exists('RC_MAP_SETTINGS_MAIN_OPTIONS')){

    class RC_MAP_SETTINGS_MAIN_OPTIONS {
        public static mixed $options;

        public function __construct()
        {
            // This is the static method to retrieve all options in this option group
            self::$options = get_option('rc_map_settings-main_options');

            // Register the settings
            add_action('admin_init', array($this, 'adminInit'));
        }

        /**
         * @return void
         * purpose: Registers the setting, including the option group, the sections and fields
         */
        public function adminInit(): void
        {
            // More about settings API: http://presscoders.com/wordpress-settings-api-explained/
            register_setting(
                option_group: 'rc_map_settings-main_options_group',
                option_name:  'rc_map_settings-main_options',
                args: [$this, 'rcMapMainOptionsValidate']);

            // Part of settings API - Add a section
            add_settings_section(
                id: 'rc_map_settings-main_options_section',
                title: 'Main POI Details',
                callback: null,
                page: 'rc_map_settings-main_options_page',
                args: null
            );

            // Part of settings API - Add a field for description of plug-in.
            add_settings_field(
                'rc_map_shortcode',
                'Shortcode:',
                [$this, 'rcMapShortcodeCallback'],
                'rc_map_settings-main_options_page',
                'rc_map_settings-main_options_section'
            );

            // Part of settings API - Add a field for the principal POI
            add_settings_field(
                id:'rc_map_principal-name',
                title: 'Principal Name:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_principal-name']
            );
            // Part of settings API - Add a field for the street address
            add_settings_field(
                id:'rc_map_address',
                title: 'Street Address:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_address']
            );
            // Part of settings API - Add a field for the principal city
            add_settings_field(
                id:'rc_map_city',
                title: 'City:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_city']
            );
            // Part of settings API - Add a field for the principal state
            add_settings_field(
                id:'rc_map_state',
                title: 'State:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_state']
            );
            // Part of settings API - Add a field for the principal zip code
            add_settings_field(
                id:'rc_map_zip-code',
                title: 'Zip Code:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_zip-code']
            );
            // Part of settings API - Add a field for the principal phone
            add_settings_field(
                id:'rc_map_phone',
                title: 'Phone:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_phone']
            );
            // Part of settings API - Add a field for the principal icon url
            add_settings_field(
                id:'rc_map_principal-icon-url',
                title: 'Icon URL:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_principal-icon-url']
            );
            // Part of settings API - Add a field for the principal icon url
            add_settings_field(
                id:'rc_map_principal-latitude',
                title: 'Latitude:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_principal-latitude']
            );
            // Part of settings API - Add a field for the principal icon url
            add_settings_field(
                id:'rc_map_principal-longitude',
                title: 'Longitude:',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-main_options_page',
                section: 'rc_map_settings-main_options_section',
                args: ['option_name' => 'rc_map_principal-longitude']
            );
        }

        /**
         * @param $input
         * @return array
         * Sanitizes the input from the user for database entry. returns the value to WordPress after verification.
         */
        public function rcMapMainOptionsValidate($input): array
        {
                // Use switch for different types of fields: text|url|number
                $new_input = [];

                foreach ($input as $key => $value) {

                    switch ($key) {
                        case 'rc_map_principal-name':
                            if (empty($value)) {
                                $value = 'Please enter a value.';
                            }
                            $new_input[$key] = sanitize_text_field($value);
                            break;
                        case 'rc_map_zip-code':
                            if (!$value) break;
                            $pattern = '/^\d{5}(-\d{4})?$/';
                            if (!preg_match($pattern, $value)) {
                                $value = 'Please enter a valid zip code.';
                                add_settings_error('rc_map_settings-main_options_section','invalid zip_code',$value,'error');
                            }else{
                                $new_input[$key] = sanitize_text_field($value);
                            }
                            break;
                        case 'rc_map_principal-icon-url':
                            if (!$value) break;
                            $pattern = '/^https:\/\/.*\./';

                            if (!preg_match($pattern, $value)) {
                                $value = 'Please enter a valid url.';
                                add_settings_error('rc_map_settings-main_options_section','invalid url',$value,'error');
                            }else{
                                $new_input[$key] = sanitize_url($value);
                            }
                            break;
                        case 'rc_map_principal-latitude':
                            $pattern = '/^(-?\d+(\.\d+)?|-?\.\d+)$/';
                            if (!preg_match($pattern,$value)){
                                $value = 'Please enter a valid latitude value';
                                add_settings_error('rc_map_settings-main_options_section','missing fields',$value,'warning');
                            }
                            $new_input[$key] = sanitize_text_field($value);
                            break;
                        case 'rc_map_principal-longitude':
                            $pattern = '/^(-?\d+(\.\d+)?|-?\.\d+)$/';
                            if (!preg_match($pattern,$value)){
                                $value = 'Please enter a valid longitude value.';
                                add_settings_error('rc_map_settings-main_options_section','missing fields',$value,'warning');
                            }
                            $new_input[$key] = sanitize_text_field($value);
                            break;
                        default:
                            if (!$value) break;
                            $new_input[$key] = sanitize_text_field($value);
                            break;
                    }
                }

                return $new_input;
        }

        /**
         * @return void
         * purpose: Adds HTML for shortcode field
         */
        public function rcMapShortcodeCallback(): void
        {
            ?>
            <span>Use the shortcode [rc_map] to display the map in any page/post/widget</span>
            <?php
        }

        public function rcMapTextInputCallback($args): void
        {
            error_log(print_r($args,true));
            $option_name = $args['option_name']
        ?>
        <input
                style="width: 25rem;"
                type="text"
                name="rc_map_settings-main_options[<?= $option_name ?>]"
                id="<?= $option_name ?>"
                value="<?php echo isset(self::$options[$args['option_name']]) ? esc_attr(self::$options[$args['option_name']]) : ''; ?>"
        >
        <?php
        }


    }

}