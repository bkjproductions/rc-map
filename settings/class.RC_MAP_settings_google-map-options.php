<?php
/*
 * Google Map Options Tab
 *
 */

if (!class_exists('RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS')){
    class RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS
    {
        public static mixed $options;

        public function __construct(public RC_DataEncryption $data_encryption)
        {
            // This is the static method to retrieve all options in this option group
            self::$options = get_option('rc_map_settings-google-map_options');

            // Register the settings
            add_action('admin_init', array($this, 'adminInit'));


            // hook into update for encryption
            add_filter( 'pre_update_option_rc_map_api_key', [ $this, 'encryptData' ] );

        }

        /**
         * @return void
         * purpose: Registers the setting, including the option group, the sections and fields
         */
        public function adminInit(): void
        {
            // More about settings API: http://presscoders.com/wordpress-settings-api-explained/
            register_setting(
                option_group: 'rc_map_settings-google-map_options_group',
                option_name: 'rc_map_settings-google-map_options',
                args: [ 'sanitize_callback' =>[$this, 'rcMapGoogleMapOptionsValidate']]);

            // Load Defaults
	        $rc_map_options_configured = get_option('rc_map_settings-google-map_options', '0'); // Use '0' as the default value
	        if ($rc_map_options_configured === '0') {
		        add_option('rc_map_settings-google-map_options', $this->rcMapGoogleOptionsDefault()); // Add the option to the database with the value '0'
	        }

            // Part of settings API - Add a section
            add_settings_section(
                id: 'rc_map_settings-google-maps_options_section',
                title: 'Google Map Configuration',
                callback: [$this, 'rcMapGoogleMapConfiguration'],
                page: 'rc_map_settings-google-maps_options_page',
                args: null
            );

            // Part of settings API - Add a field for Google api key
            add_settings_field(
                id: 'rc_map_api_key',
                title: 'Google Maps API Key',
                callback: array($this, 'rcMapApiKeyCallback'),
                page: 'rc_map_settings-google-maps_options_page',
                section: 'rc_map_settings-google-maps_options_section',
                args: ['option_name' => 'rc_map_api_key']
            );
            // Part of settings API - Add a field for Google zoom level
            add_settings_field(
                'rc_map_zoom',
                'Map Zoom Level',
                array($this, 'rcMapZoomCallback'),
                'rc_map_settings-google-maps_options_page',
                'rc_map_settings-google-maps_options_section'
            );
            // Part of settings API - Add a field for Google marker scale
            add_settings_field(
                'rc_map_marker-scale',
                'Map Marker Scale',
                array($this, 'rcMapMarkerScaleCallback'),
                'rc_map_settings-google-maps_options_page',
                'rc_map_settings-google-maps_options_section'
            );
            // Part of settings API - Add a field for Google center - latitude
            add_settings_field(
                id:'rc_map_center_latitude',
                title:'Map Center: Latitude',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-google-maps_options_page',
                section: 'rc_map_settings-google-maps_options_section',
                args: ['option_name' => 'rc_map_center_latitude']
            );
            // Part of settings API - Add a field for Google center - longitude
            add_settings_field(
                id:'rc_map_center_longitude',
                title:'Map Center: Longitude',
                callback: [$this, 'rcMapTextInputCallback'],
                page: 'rc_map_settings-google-maps_options_page',
                section: 'rc_map_settings-google-maps_options_section',
                args: ['option_name' => 'rc_map_center_longitude']
            );
            // Part of settings API - Add a field for snazzy map selected style

            add_settings_field(
                'rc_map_style',
                'Map Style',
                array($this, 'rcMapStyleCallback'),
                'rc_map_settings-google-maps_options_page',
                'rc_map_settings-google-maps_options_section',
                null
            );
        }
        public function rcMapGoogleMapConfiguration():void { ?>
            <div>
            Changing map settings may require clearing cache.
            </div>
        <?php }


        public function rcMapApiKeyCallback(): void
        {   // This needs to be encrypted.

            // enable the running process.php
            update_option('rc_map_configured', 1);

            if(is_array(self::$options)){
	            // Access the 'rc_map_api_key' element only if self::$options is an array
	            $encrypted_key = self::$options['rc_map_api_key'];
	            if ( $encrypted_key === '' ) {
		            $decrypted_key = 'Not Set';
	            } else {
		            $decrypted_key = $this->decryptData( self::$options['rc_map_api_key'] );
	            }
            }else {
	            $decrypted_key = 'Not Set';
            }


            ?>
         


            <label for="rc_map_api_key" style="display: flex;flex">Encrypted in the database.</label>
            <input type="text" style="width: 25rem;"  name="rc_map_settings-google-map_options[rc_map_api_key]" id="rc_map_api_key"
                   value="<?php echo esc_attr( $decrypted_key ) ?>"/>
        
            <?php
        }
        public function rcMapTextInputCallback($args): void
        {

            $option_name = $args['option_name']
            ?>
            <input
                style="width: 25rem;"
                type="text"
                name="rc_map_settings-google-map_options[<?= $option_name ?>]"
                id="<?= $option_name ?>"
                value="<?php echo isset(self::$options[$args['option_name']]) ? esc_attr(self::$options[$args['option_name']]) : ''; ?>"
            >
            <?php
        }

        /**
         * @return void
         * purpose: sets number field for zoom level
         */
        public function rcMapZoomCallback(): void
        {
            ?>
            <input
                style="width: 5rem;"
                type="number"
                step="0.1"
                min="1"
                max="30"
                name="rc_map_settings-google-map_options[rc_map_zoom]"
                id="rc_map_zoom"
                value="<?php echo isset(self::$options['rc_map_zoom']) ? esc_attr(self::$options['rc_map_zoom']) : ''; ?>"
            >

            <?php
        }
        /**
         * @return void
         * purpose: sets number field for zoom level
         */
        public function rcMapMarkerScaleCallback(): void
        {
            ?>
            <input
                style="width: 5rem;"
                type="number"
                step="0.1"
                min="0.2"
                max="2"
                name="rc_map_settings-google-map_options[rc_map_marker-scale]"
                id="rc_map_marker-scale"
                value="<?php echo isset(self::$options['rc_map_marker-scale']) ? esc_attr(self::$options['rc_map_marker-scale']) : ''; ?>"
            >

            <?php
        }


        /**
         * @return void
         * Populates select box with loaded map styles
         */
        public function rcMapStyleCallback(): void
        {

            $options = RC_MAP_SETTINGS_SNAZZY_STYLE_OPTIONS::$options;
            $selected_style = self::$options['rc_map_style'] ?? 0;

            if ($selected_style === 0 ){
                ?>
                <span> No default style loaded</span>
                <?php
            }else {
            ?>

            <select
                id="rc_map_style"
                name="rc_map_settings-google-map_options[rc_map_style]">
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
            <?php }
        }
        /**
         * @param $input
         * @return array
         * Sanitizes the input from the user for database entry. returns the value to WordPress after verification.
         */
        public function rcMapGoogleMapOptionsValidate($input): array
        {
            // Use switch for different types of fields: text|url|number
            $new_input = [];

            foreach ($input as $key => $value) {

                switch ($key) {
                    case 'rc_map_api_key':
                        $encryptedValue = $this->encryptData($value);
                        $new_input[$key] = sanitize_text_field($encryptedValue);
                        break;
                    case 'rc_map_zoom':
                        if (!$value) break;
                        $pattern = '/^(?:[1-9]|[1-4]\d|50)(?:\.\d+)?$/';  // 1-50 including decimals
                        if (!preg_match($pattern, $value)) {
                            $value = 'Please enter a valid zoom level.';
                            add_settings_error('rc_map_settings-google-maps_options_section','field error',$value,'error');
                        }else{
                            $new_input[$key] = sanitize_text_field($value);
                        }
                        break;
                    case 'rc_map_center_latitude':
                        $pattern = '/^(-?\d+(\.\d+)?|-?\.\d+)$/';
                        if (!preg_match($pattern,$value)){
                            $value = 'Please enter a valid latitude value';
                            add_settings_error('rc_map_settings-google-maps_options_section','field warning',$value,'warning');
                        }
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                    case 'rc_map_center_longitude':
                        $pattern = '/^(-?\d+(\.\d+)?|-?\.\d+)$/';
                        if (!preg_match($pattern,$value)){
                            $value = 'Please enter a valid longitude value.';
                            add_settings_error('rc_map_settings-google-maps_options_section','field warning',$value,'warning');
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

        public function rcMapGoogleOptionsDefault ():array {
            return [
                    'rc_map_api_key' => null,
                    'rc_map_zoom' => 14.5,
                    'rc_map_marker-scale' => 1.3,
                    'rc_map_center_latitude' => 40.71293,
                    'rc_map_center_longitude' => -74.01314,
                    'rc_map_style' => 'default'
            ];
        }
        /**** ENCRYPTION FUNCTIONS */

        function encryptData( $input ): string {

            $submitted_key = sanitize_text_field( $input );

            return $this->data_encryption->encrypt( $submitted_key );

        }

        public function decryptData( $input ): string {

            if ( $input ) {
                return $this->data_encryption->decrypt( $input );
            }

            return 'Not Set';
        }
    }
}