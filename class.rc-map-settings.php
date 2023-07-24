<?php

if( ! class_exists( 'RC_Map_Settings' )) {
    class RC_Map_Settings
    {

        public static $options;

        public function __construct()
        {
            self::$options = get_option('rc_map_options');
            add_action('admin_init', array($this, 'adminInit'));
        }

        public function adminInit():void {
                // More about settings API: http://presscoders.com/wordpress-settings-api-explained/
            register_setting( 'rc_map_group', 'rc_map_options',[$this, 'rcMapValidate' ]);


            // PAGE 1 ***************************** //

            add_settings_section(
                id:'rc_map_main_section',
                title: 'How does it work?',
                callback: [$this, 'displayAllTabbedData'],
                page:'rc_map_page1'
            );


            add_settings_field(
                'rc_map_shortcode',
                'Shortcode',
                array( $this, 'rcMapShortcodeCallback' ),
                'rc_map_page1',
                'rc_map_main_section'
            );

            add_settings_field(
               id: 'rc_map_title',
               title: 'Map Title',
               callback: array( $this, 'rcMapTitleCallback' ),
               page: 'rc_map_page1',
               section: 'rc_map_main_section',
               args: null
            );

            add_settings_field(
                'rc_map_style',
                'Map Style',
                array( $this, 'rcMapStyleCallback' ),
                'rc_map_page1',
                'rc_map_main_section',
                array(
                    'items' => array(
                        'style-1',
                        'style-2'
                    ),
                    'label_for' => 'rc_map_style'
                )
            );
            // PAGE 2 ***************************** //

            add_settings_section(
                id:'rc_map_second_section',
                title: 'Google API Configuration',
                callback:[ $this, 'displayAllTabbedData'],
                page: 'rc_map_page2',
                args: null

            );

            add_settings_field(
                id:'rc_map_api_key',
                title: 'Google Maps API Key',
                callback: array ( $this, 'rcMapApiKeyCallback' ),
                page: 'rc_map_page2',
                section: 'rc_map_second_section',
                args: null
            );

            add_settings_field(
                'rc_map_zoom',
                'Map Zoom Level',
                array( $this, 'rcMapZoomCallback' ),
                'rc_map_page2',
                'rc_map_second_section'
            );






        }

        // PAGE 1 HTML ******************************** /
        public function rcMapShortcodeCallback() :void{
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
                    value="<?php echo isset( self::$options['rc_map_title'] ) ? esc_attr( self::$options['rc_map_title'] ) : ''; ?>"
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
                    value="<?php echo isset( self::$options['rc_map_api_key'] ) ? esc_attr( self::$options['rc_map_api_key'] ) : ''; ?>"
            >
            <?php
        }
        public function rcMapZoomCallback(): void
        {
            ?>
            <input
                    style="width: 5rem;"
                    type="number"
                    min="1"
                    max="30"
                    name="rc_map_options[rc_map_zoom]"
                    id="rc_map_zoom"
                    value="<?php echo isset( self::$options['rc_map_zoom'] ) ? esc_attr( self::$options['rc_map_zoom'] ) : ''; ?>"
            >
            <?php
        }
        public function rcMapStyleCallback( $args ) : void{
            ?>
            <select
                    id="rc`_map_style"
                    name="rc_map_options[rc_map_style]">
                <?php
                foreach( $args['items'] as $item ):
                    ?>
                    <option value="<?php echo esc_attr( $item ); ?>"
                        <?php
                        isset( self::$options['rc_map_style'] ) ? selected( $item, self::$options['rc_map_style'], true ) : '';
                        ?>
                    >
                        <?php echo esc_html( ucfirst( $item ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }

        public function rcMapValidate( $input ): array
        {
            // Use switch for different types of fields: text|url|number
            $new_input = array();
            foreach( $input as $key => $value ){
                switch ($key){
                    case 'rc_map_title':
                        if( empty( $value )){
                            $value = 'Please enter a value.';
                        }
                        $new_input[$key] = sanitize_text_field( $value );
                        break;
                    default:
                        $new_input[$key] = sanitize_text_field( $value );
                        break;
                }
            }
            return $new_input;
        }
        public function displayAllTabbedData():void {
            // These fields need to load
            ?>
            <input type="hidden"
                   id="rc_map_zoom"
                   name="rc_map_options[rc_map_zoom]"
                   value="<?php echo isset( self::$options['rc_map_zoom'] ) ? esc_attr( self::$options['rc_map_zoom'] ) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_api_key]"
                    id="rc_map_api_key"
                    value="<?php echo isset( self::$options['rc_map_api_key'] ) ? esc_attr( self::$options['rc_map_api_key'] ) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_title]"
                    id="rc_map_title"
                    value="<?php echo isset( self::$options['rc_map_title'] ) ? esc_attr( self::$options['rc_map_title'] ) : ''; ?>"
            >
            <input
                    type="hidden"
                    name="rc_map_options[rc_map_style]"
                    id="rc_map_style"
                    value="<?php echo isset( self::$options['rc_map_style'] ) ? esc_attr( self::$options['rc_map_style'] ) : ''; ?>"
            >
            <?php
        }
    }
}
