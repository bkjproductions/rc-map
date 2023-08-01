<?php

if (!class_exists('RC_Map_Shortcode')) {
    class RC_Map_Shortcode
    {
        public function __construct()
        {
            add_shortcode('rc_map', [$this, 'addShortcode']);
        }

        public function addShortcode($atts = [], $content = null, $tag = '')
        {

            $attrs = array_change_key_case((array)$atts, CASE_LOWER);

            //extract atts to variables
            extract(shortcode_atts([
                'id' => '',
                'orderby' => 'date'
            ],
                $atts,
                $tag,
            ));

            // build an array for all the ids passed in via shortcode
            if (!empty($id)) {
                $id = array_map('absint', explode(',', $id));
            }
            ob_start(); // take all html output and put it into buffer
            // here use require, not require-one
            require(RC_MAP_PATH . 'views/rc-map-shortcode.php');

            return ob_get_clean(); // return buffer to html
        }
    }
}