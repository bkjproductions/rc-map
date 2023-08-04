<?php
/*
 * Load Snazzy Map Styles
 *
 */

if (!class_exists('RC_MAP_SETTINGS_SNAZZY_STYLE_OPTIONS')){

	class RC_MAP_SETTINGS_SNAZZY_STYLE_OPTIONS {
		public static mixed $options;
		public function __construct() {

			// This is the static method to retrieve all options in this option group
			self::$options = get_option('rc_map_settings-styles_options');

			// Register the settings
			add_action('admin_init', array($this, 'adminInit'));
		}
		public function adminInit(): void {
			// More about settings API: http://presscoders.com/wordpress-settings-api-explained/
			register_setting( 'rc_map_settings-styles_options_group', 'rc_map_settings-styles_options', [ $this, 'rcMapValidateStyles' ] );


			// Load Defaults
			$rc_map_options_configured = get_option('rc_map_settings-styles_options', '0'); // Use '0' as the default value

            if ($rc_map_options_configured === '0') {
				add_option('rc_map_settings-styles_options', $this->rcMapStylesOptionsDefault()); // Add the option to the database with the value '0'
			}

			// PAGE 3 ***************************** //
			add_settings_section(
				id: 'rc_map_settings-styles_options_section',
				title: 'Load Map Styles',
				callback: null,
				page: 'rc_map_settings-styles_options_page',
				args: null
			);

			add_settings_field(
				id: 'key',
				title: 'Unique Map Name',
				callback: [ $this, 'rcMapStyleKeyCallback' ],
				page: 'rc_map_settings-styles_options_page',
				section: 'rc_map_settings-styles_options_section',
				args: []
			);
			add_settings_field(
				id: 'value',
				title: 'Snazzy JSON',
				callback: [ $this, 'rcMapStyleValueCallback' ],
				page: 'rc_map_settings-styles_options_page',
				section: 'rc_map_settings-styles_options_section',
				args: []
			);


		}

		// PAGE 3 HTML ******************************** /

		public function rcMapStyleKeyCallback(): void
		{ ?>
			<div>
				<input type="text" name="rc_map_settings-styles_options[key]" id="rc_map_settings-styles_options[key]"/>
			</div>


		<?php }

		public function rcMapStyleValueCallback(): void
		{ ?>

			<textarea name="rc_map_settings-styles_options[value]" id="rc_map_settings-styles_options[value]" cols="60"
			          rows="30"><?php echo "Paste Snazzy JSON Here."; ?></textarea>

			<?php
		}

		private function rc_map_options_map_styles_options()
		{
			$options = get_option('rc_map_settings-styles_options');

			if (!is_array($options)) {
				$options = array();
			}

			return $options;
		}

		public function rcMapValidateStyles($input): array
		{
			error_log("RUNNING IN STYLE VALIDATION");
            //error_log(print_r($input,true));

			// Get existing map styles
			$map_styles = $this->rc_map_options_map_styles_options() ?? 0;

            if (isset($input['key'])) {
	            // Append to style
	            $map_styles[ $input['key'] ] = $input['value'];
            }
            //error_log($map_styles['key']);


            return $map_styles;

		}
		public function rcMapStylesOptionsDefault(): array {
			$files = $this->getFiles();
			$input = [];

			foreach ($files as $file) {
				// Get the filename without the extension to use as the array key
				$filename = pathinfo($file, PATHINFO_FILENAME);

				// Read the file content
				$file_content = file_get_contents($file);

				// Parse the JavaScript array as JSON
				$js_array = json_decode($file_content, true);

				// If the JSON decoding is successful and it's an array, add it to the input array
				if (is_array($js_array)) {
					$input = array(
						'key' => $filename,
						'value' => $js_array,
					);
				}
			}

			error_log("input: " . print_r($input, true));
			return $input;
		}


		function getFiles(): array {
			$directory_path = RC_MAP_PATH . 'settings/Styles/';

			return glob( $directory_path . '*.txt');
		}


	}

}