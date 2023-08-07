<?php
/*
 * Additional Options Tab
 *
 */

if (!class_exists('RC_MAP_SETTINGS_ADDITIONAL_OPTIONS')){
	class RC_MAP_SETTINGS_ADDITIONAL_OPTIONS {
		public static mixed $options;

		public function __construct() {
			self::$options = get_option('rc_map_settings-additional_options');

			add_action( 'admin_init', array( $this, 'adminInit' ) );

		}

		public function adminInit(): void {
			// More about settings API: http://presscoders.com/wordpress-settings-api-explained/
			register_setting(
				option_group: 'rc_map_settings-additional_options_group',
				option_name: 'rc_map_settings-additional_options',
				args: [ 'sanitize_callback' =>[$this, 'rcMapAdditionalOptionsValidate']]);

			// Load Defaults
			$rc_additional_options_configured = get_option('rc_map_settings-additional_options', '0'); // Use '0' as the default value
			if ($rc_additional_options_configured === '0') {
				add_option('rc_map_settings-additional_options', $this->rcAdditionalOptionsDefault()); // Add the option to the database with the value '0'
			}


			add_settings_section(
				id: 'rc_map_settings-additional_options_section',
				title: 'Additional Options',
				callback: null,
				page: 'rc_map_settings-additional_options_page',
				args: null

			);

			add_settings_field(
				id: 'rc_map_use_data_tables_js',
				title: 'Use DataTables.js in admin',
				callback: [$this, 'rcCheckBoxCallback'],
				page: 'rc_map_settings-additional_options_page',
				section: 'rc_map_settings-additional_options_section',
				args: [
					'theName' => 'rc_map_use_data_tables_js'
				]

			);
			add_settings_field(
				id: 'rc_map_show_generate_map_tab',
				title: 'Show Generate Map Tab',
				callback: [$this, 'rcCheckBoxCallback'],
				page: 'rc_map_settings-additional_options_page',
				section: 'rc_map_settings-additional_options_section',
				args: [
					'theName' => 'rc_map_show_generate_map_tab'
				]

			);
		}

		// PAGE 6 HTML ******************************* /
		public function rcCheckBoxCallback($args): void
		{
			$option_name = "rc_map_settings-additional_options[" . $args['theName'] . ']';

			$checked = isset(self::$options[$args['theName']]) ? '1' : '0';

			?>
			<input type="checkbox" id="<?php echo esc_attr($option_name); ?>"
			       name="<?php echo esc_attr($option_name); ?>" value="1" <?php checked($checked, 1); ?> >

			<?php
		}
		public function rcMapAdditionalOptionsValidate($input): array
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
		public function rcAdditionalOptionsDefault ():array {
			return [
				'rc_map_use_data_tables_js' => 0,
				'rc_map_show_generate_map_tab' => 1
			];
		}

	}
}