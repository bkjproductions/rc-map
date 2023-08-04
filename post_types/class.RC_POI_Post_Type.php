<?php


use JetBrains\PhpStorm\NoReturn;

if (!class_exists('RC_POI_Post_Type ')) {
    class RC_POI_Post_Type

    {
        public RC_POI_Taxonomy_Views $poi_taxonomy;

        public function __construct( )
        {

            // CREATE POST TYPE
            add_action('init', [$this, 'createPostType'], 10);

            // BUILD POST META BOXES
            add_action('add_meta_boxes', [$this, 'addMetaBoxes']);

            // SAVE POST META DATA
            add_action( 'save_post', [$this, 'savePost'], 10, 2 );


            // POST META ADMIN COLUMNS to include sortable meta data
            add_filter( 'manage_rc-poi_posts_columns', [$this, 'rcMapCPTColumns']);


            // POPULATE COLUMN DATA
            add_action( 'manage_rc-poi_posts_custom_column',
                callback: [$this, 'rcMapCustomColumns'],
                priority: 10,
                accepted_args:2);


            // MAKE COLUMNS SORTABLE
            add_action( 'manage_edit-rc-poi_sortable_columns', [$this, 'rcMapSortableColumns'] );

            // CUSTOM SEARCH - JOINS posts meta table with posts table ON rc-poi.post_id = ID
            add_action( 'pre_get_posts', array( $this, 'customSearchQuery' ), 100 );

            // ADD RESET SEARCH BUTTON
            add_action('restrict_manage_posts', [$this,'addResetButtonToSearchForm']);

            // HANDLE DELETE POSTS:
            add_action('wp_ajax_rc_delete_post',[$this,'ajaxDeletePostData']);

            // HANDLE BULK DELETE POSTS: in this form: 'wp_ajax_{action}' [sent from data request]
            add_action('wp_ajax_rc_bulk_delete_post',[$this,'ajaxBulkDeletePostData']);


        }

        // CREATE POST TYPE
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
                'show_in_menu' => false, // we have custom menu - turn this off
                'show_in_admin_bar' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'poi'),
                'capability_type' => 'post',
                'can_export' => true,
                'has_archive' => true,
                'menu_position' => 5,
                //'taxonomies'         => array( 'category', 'post_tag' ),
                'taxonomies'         => array( 'point-of-interest' ),
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-admin-site-alt'
            );

            register_post_type('rc-poi', $args);

        }

        // BUILD POST META BOXES
        public function addMetaBoxes(): void
        {
            add_meta_box(
                id: 'rc_poi_meta_box',
                title: 'Location Details',
                callback: [$this, 'addInnerMetaBoxes'],
                screen: 'rc-poi',
                context: 'normal',
                priority: 'high',

            );
        }

        /**
         * @param $post
         * @return void
         * hook:
         */
        public function addInnerMetaBoxes ($post):void {
            require_once ( RC_MAP_PATH . 'views/rc-poi_meta_box.php');
        }

        /**
         * @return void
         * purpose: Handles ajax delete request from admin list table
         */
        #[NoReturn] public function ajaxDeletePostData (): void
        {
            // Check the AJAX nonce
            check_ajax_referer('rc_rest', 'nonce', true);

            $post_id = intval($_POST['post_id']);
            if (current_user_can('delete_post', $post_id)) {
                // Delete the post
                wp_delete_post($post_id, true);

                // Return a success response
                echo json_encode(array('success' => true));
            } else {
                // Return an error response
                echo json_encode(array('error' => 'Insufficient permissions.'));
            }

            // Make sure to exit the function after processing the AJAX request
            wp_die();
        }

        #[NoReturn] public function ajaxBulkDeletePostData():void{
            // Check the AJAX nonce
            check_ajax_referer('rc_rest', 'nonce', true);

            $post_ids = $_POST['post_ids'] ?? [];
            if (current_user_can('delete_post', $post_ids)) {
                // Delete the post

                foreach ($post_ids as $post_id){
                    wp_delete_post($post_id, true);
                }

                // Return a success response
                echo json_encode(array('success' => true));

            } else {
                // Return an error response
                echo json_encode(array('error' => 'Insufficient permissions.'));
            }

            // Make sure to exit the function after processing the AJAX request
            wp_die();
        }

        /**
         * @param $post_id
         * @return void
         * hook: save_post
         * purpose: Sanitizes and Validates the data.
         */
        public function savePost($post_id): void
        {
            if (is_admin()){
	            $current_screen = get_current_screen();
	            if (!isset($current_screen)) return;

	            if ( $current_screen->id != 'rc-poi' || $current_screen->post_type != 'rc-poi' ) {
		            return;
	            }
            }



            if (!$this->validateUser($post_id)) return;

            if (isset($_POST['action']) && $_POST['action'] == 'editpost') {
                $meta_fields = array(
                    'rc_poi_location_url',
                    'rc_poi_location_address',
                    'rc_poi_location_city',
                    'rc_poi_location_state',
                    'rc_poi_location_zip_code',
                    'rc_poi_location_phone',
                    'rc_poi_location_geo_code',
                );

                foreach ($meta_fields as $field) {
                    $old_value = get_post_meta($post_id, $field, true);
                    $new_value = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : null;

                    if (empty($new_value)) {
                        update_post_meta($post_id, $field, null);
                    } else {
                        update_post_meta($post_id, $field, $new_value, $old_value);
                    }
                }
            }
        }


        // ADD COLUMNS
        public function rcMapCPTColumns($columns)   {

            // Hide or show columns [ ** UPDATE BELOW ****
            unset($columns['date']);
          //$columns['post_id'] = esc_html('ID', 'rc-map');

            $columns['rc_poi_location_geo_code'] = esc_html( 'Geo Code', 'rc-map');
            $columns['rc_poi_location_address'] = esc_html( 'Address', 'rc-map');
          //$columns['rc_poi_location_city'] = esc_html( 'City', 'rc-map');
          //$columns['rc_poi_location_state'] = esc_html( 'State', 'rc-map');
          //$columns['rc_poi_location_zip_code'] = esc_html( 'Zip Code', 'rc-map');
          //$columns['rc_poi_location_phone'] = esc_html( 'Phone', 'rc-map');
          //$columns['rc_poi_location_url'] = esc_html( 'URL', 'rc-map');

            return $columns;
        }


        // POPULATE COLUMNS
        public function rcMapCustomColumns($column, $post_id):void {
            switch( $column ){
                case 'rc_poi_location_geo_code':
                    echo esc_html( get_post_meta( $post_id, 'rc_poi_location_geo_code', true ) );
                    break;
                case 'rc_poi_location_address':
                    echo esc_html( get_post_meta( $post_id, 'rc_poi_location_address', true ) );
                    break;
                case 'rc_poi_location_city':
                    echo esc_html( get_post_meta( $post_id, 'rc_poi_location_city', true ) );
                    break;
                case 'rc_poi_location_state':
                    echo esc_html( get_post_meta( $post_id, 'rc_poi_location_state', true ) );
                    break;
                case 'rc_poi_location_zip_code':
                    echo esc_html( get_post_meta( $post_id, 'rc_poi_location_zip_code', true ) );
                    break;
                case 'rc_poi_location_phone':
                    echo esc_html( get_post_meta( $post_id, 'rc_poi_location_phone', true ) );
                    break;
                case 'rc_poi_location_url':
                    echo esc_url( get_post_meta( $post_id, 'rc_poi_location_url', true ) );
                    break;
            }
        }


        // SORTABLE COLUMNS
        public function rcMapSortableColumns($columns):array {
            //$columns[ 'taxonomy-poi'] = 'taxonomy-poi';
            $columns['rc_poi_location_geo_code'] = 'rc_poi_location_geo_code';
            $columns['rc_poi_location_address'] = 'rc_poi_location_address';
            //$columns['rc_poi_location_city'] = 'rc_poi_location_city';
            //$columns['rc_poi_location_state'] = 'rc_poi_location_state';
            //$columns['rc_poi_location_zip_code'] = 'rc_poi_location_zip_code';
            //$columns['rc_poi_location_phone'] = 'rc_poi_location_phone';
            //$columns['rc_poi_location_url'] = 'rc_poi_location_url';
            return $columns;
        }


        // SEARCHABLE META DATA

        public function customSearchQuery($query) {

            if ( isset( $_GET['reset-search'] )) {
                // Redirect back to the posts list table without the search query
                wp_redirect( admin_url( 'edit.php?post_type=rc-poi' ) );
                exit;
            }

            $post_type = 'rc-poi';

            if (!$query->is_main_query() || $query->get('post_type') !== $post_type) {
                return $query;
            }

            if ( 'rc_poi_location_address' === $query->get('orderby')){


                $query->set('orderby' , 'meta_value');
                $query->set('meta_key' , 'rc_poi_location_address');


            }
            if ( 'rc_poi_location_geo_code' === $query->get('orderby')){
                $query->set('orderby' , 'meta_value');
                $query->set('meta_key' , 'rc_poi_location_geo_code');
            }

            if ( is_search() ) {
                global $wp_query;
                // error_log(print_r($wp_query->get_queried_object(),true));

                // Prevent duplicates in the search results
                add_filter( 'posts_distinct', [$this, 'distinctColumns'],100);

                // Modify the WHERE clause to include custom meta fields in the search
                add_filter( 'posts_where', [$this, 'whereClauseAdjustment'],10 );

                // Join the postmeta table for custom meta fields search
                add_action( 'posts_join', [$this, 'joinDatabaseColumns'],1 );


            }


            $results = get_posts($query);
            //error_log(print_r($results,true));


        }
        public function distinctColumns (): string
        {
            return "DISTINCT";
        }
        public function whereClauseAdjustment($where){
            global $wpdb;
            $search_term = get_search_query();
            //error_log($where);
            if ( !empty($search_term) ) {
                // ADD the unique alias '{plugin}_pm'

                $where = preg_replace(
                    "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                    "(".$wpdb->posts.".post_title LIKE $1) OR (rc_map_pm.meta_value LIKE $1)", $where );
            }
            return $where;
        }
        public function joinDatabaseColumns($join){
            global $wpdb;
            // Check if the wp_postmeta table is already joined
//            if (strpos($join, $wpdb->postmeta) === false) {
//                // If not joined, then add the LEFT JOIN clause with the unique alias '{plugin}_pm'
//                $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' AS rc_map_pm ON ' . $wpdb->posts . '.ID = rc_map_pm.post_id ';
//            }
            $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' AS rc_map_pm ON ' . $wpdb->posts . '.ID = rc_map_pm.post_id ';

            return $join;
        }
        public function addResetButtonToSearchForm(): void
        {
            if (isset($_GET['post_type']) && $_GET['post_type'] === 'rc-poi'){
            ?>
<!--                <input type="search" id="post-search-input" name="s" value="--><?php //echo esc_attr( get_search_query() ); ?><!--" />-->
                <button style="border: tomato solid 1px;" type="submit" name="reset-search" class="button">Reset POI Search Filters</button>

            <?php
            }
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
