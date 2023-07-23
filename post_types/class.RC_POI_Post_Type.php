<?php


if (!class_exists('RC_POI_Post_Type ')) {
    class RC_POI_Post_Type
    {
        public function __construct()
        {
            add_action('init', [$this, 'createPostType'], 10);

        }

        public function createPostType(): void
        {
            $labels = array(
                'name' => _x('Points of Interest', 'rc-map'),
                'singular_name' => _x('POI', 'rc-map'),
                'search_items' => __('Search POIs', 'rc-map'),

            );

            $args = array(
                'labels' => $labels,
                'description' => 'Point of Interest custom post type.',
                // 'supports'           => array( 'title', 'editor', 'author', 'thumbnail' ),
                'supports' => array('title', 'editor', 'thumbnail'),
                'hierarchical' => false,
                'show_ui' => true,
                'exclude_from_search' => false,
                'public' => true,
                'publicly_queryable' => true,
                'show_in_menu' => true, // we have custom menu - turn this off
                'show_in_admin_bar' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'poi'),
                'capability_type' => 'post',
                'can_export' => true,
                'has_archive' => true,
                'menu_position' => 5,
                'taxonomies'         => array( 'category', 'post_tag' ),
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-admin-site-alt'
            );

            register_post_type('rc-poi', $args);

        }
        private function validateUser($post_id):bool {
            if( isset( $_POST['rc_map_nonce'] ) ){
                if( ! wp_verify_nonce( $_POST['rc_map_nonce'], 'rc_map_nonce' ) ){
                    return false;
                }
            }

            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
                return false;
            }

            if( isset( $_POST['post_type'] ) && $_POST['post_type'] === 'rc-map' ){
                if( ! current_user_can( 'edit_page', $post_id ) ){
                    return false;
                }elseif( ! current_user_can( 'edit_post', $post_id ) ){
                    return false;
                }
            }
            return true;
        }
    }
}
