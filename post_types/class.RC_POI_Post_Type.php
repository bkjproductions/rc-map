<?php


if (!class_exists('RC_POI_Post_Type ')) {
    class RC_POI_Post_Type
    {
        public function __construct()
        {


            // CREATE POST TYPE
            add_action('init', [$this, 'createPostType'], 10);

            // BUILD META BOXES
            add_action('add_meta_boxes', [$this, 'addMetaBoxes']);

            // SAVE META DATA
            add_action( 'save_post', [$this, 'savePost'], 10, 2 );

            // ADMIN COLUMNS to include sortable meta data
            add_filter( 'manage_rc-poi_posts_columns', [$this, 'rcMapCPTColumns']);

            // POPULATE COLUMN DATA
            add_action( 'manage_rc-poi_posts_custom_column',
                callback: [$this, 'rcMapCustomColumns'],
                priority: 10,
                accepted_args:2);

            // MAKE COLUMNS SORTABLE
            add_filter( 'manage_edit-rc-poi_sortable_columns', [$this, 'rcMapSortableColumns'] );

            // CUSTOM SEARCH - JOINS posts meta table with posts table ON rc-poi.post_id = ID
            add_action( 'pre_get_posts', array( $this, 'customSearchQuery' ) );
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
                'show_in_menu' => true, // we have custom menu - turn this off
                'show_in_admin_bar' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'poi'),
                'capability_type' => 'post',
                'can_export' => true,
                'has_archive' => true,
                'menu_position' => 5,
                //'taxonomies'         => array( 'category', 'post_tag' ),
                'taxonomies'         => array( 'category' ),
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-admin-site-alt'
            );

            register_post_type('rc-poi', $args);

        }
        // BUILD META BOXES
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
        public function addInnerMetaBoxes ($post):void {
            require_once ( RC_MAP_PATH . 'views/rc-poi_meta_box.php');
        }

        // SAVE POST
        public function savePost($post_id ): void{

            if (!$this->validateUser($post_id)) return;


            if( isset( $_POST['action'] ) && $_POST['action'] == 'editpost' ){
                $old_poi_url = get_post_meta( $post_id, 'rc_poi_location_url', true ); // single: return as string, false, return array
                $old_poi_address = get_post_meta( $post_id, 'rc_poi_location_address', true ); // single: return as string, false, return array
                $old_poi_city = get_post_meta( $post_id, 'rc_poi_location_city', true ); // single: return as string, false, return array
                $old_poi_state = get_post_meta( $post_id, 'rc_poi_location_state', true ); // single: return as string, false, return array
                $old_poi_zip_code = get_post_meta( $post_id, 'rc_poi_location_zip_code', true ); // single: return as string, false, return array
                $old_poi_phone = get_post_meta( $post_id, 'rc_poi_location_phone', true ); // single: return as string, false, return array
                $old_poi_geo_code = get_post_meta( $post_id, 'rc_poi_location_geo_code', true ); // single: return as string, false, return array


                $new_poi_url= $_POST['rc_poi_location_url'];
                $new_poi_address = $_POST['rc_poi_location_address'];
                $new_poi_city = $_POST['rc_poi_location_city'];
                $new_poi_state = $_POST['rc_poi_location_state'];
                $new_poi_zip_code = $_POST['rc_poi_location_zip_code'];
                $new_poi_phone = $_POST['rc_poi_location_phone'];
                $new_poi_geo_code = $_POST['rc_poi_location_geo_code'];


                if( empty( $new_poi_url )){
                    update_post_meta( $post_id, 'rc_poi_location_url', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_url', sanitize_text_field( $new_poi_url ), $old_poi_url );
                }
                if( empty( $new_poi_address )){
                    update_post_meta( $post_id, 'rc_poi_location_address', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_address', sanitize_text_field( $new_poi_address ), $old_poi_address );
                }
                if( empty( $new_poi_city )){
                    update_post_meta( $post_id, 'rc_poi_location_city', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_city', sanitize_text_field( $new_poi_city ), $old_poi_city );
                }
                if( empty( $new_poi_state )){
                    update_post_meta( $post_id, 'rc_poi_location_state', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_state', sanitize_text_field( $new_poi_state ), $old_poi_state );
                }
                if( empty( $new_poi_zip_code )){
                    update_post_meta( $post_id, 'rc_poi_location_zip_code', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_zip_code', sanitize_text_field( $new_poi_zip_code ), $old_poi_zip_code );
                }
                if( empty( $new_poi_phone )){
                    update_post_meta( $post_id, 'rc_poi_location_phone', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_phone', sanitize_text_field( $new_poi_phone ), $old_poi_phone );
                }
                if( empty( $new_poi_geo_code )){
                    update_post_meta( $post_id, 'rc_poi_location_geo_code', null );
                }else{
                    update_post_meta( $post_id, 'rc_poi_location_geo_code', sanitize_text_field( $new_poi_geo_code ), $old_poi_geo_code );
                }


            }
        }

        // ADD COLUMNS
        public function rcMapCPTColumns($columns)   {

            // Hide or show columns
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
            $columns['rc_poi_location_geo_code'] = 'rc_poi_location_geo_code';
            $columns['rc_poi_location_address'] = 'rc_poi_location_address';
            $columns['rc_poi_location_city'] = 'rc_poi_location_city';
            $columns['rc_poi_location_state'] = 'rc_poi_location_state';
            $columns['rc_poi_location_zip_code'] = 'rc_poi_location_zip_code';
            $columns['rc_poi_location_phone'] = 'rc_poi_location_phone';
            $columns['rc_poi_location_url'] = 'rc_poi_location_url';
            return $columns;
        }

        // SEARCHABLE META DATA

        public function customSearchQuery($query): void {
            $post_type = 'rc-poi';
            if ($query->query['post_type'] != $post_type) {
                return;
            }
            if ( is_search() ) {
                // global $wp_query;
                // error_log(print_r($wp_query->get_queried_object(),true));

                // Prevent duplicates in the search results
                add_filter( 'posts_distinct', [$this, 'distinctColumns']);

                // Modify the WHERE clause to include custom meta fields in the search
                add_filter( 'posts_where', [$this, 'whereClauseAdjustment'] );

                // Join the postmeta table for custom meta fields search
                add_action( 'posts_join', [$this, 'joinDatabaseColumns'] );
            }
        }
        public function distinctColumns (): string
        {
            return "DISTINCT";
        }
        public function whereClauseAdjustment($where){
            global $wpdb;
            $search_term = get_search_query();
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
            if (strpos($join, $wpdb->postmeta) === false) {
                // If not joined, then add the LEFT JOIN clause with the unique alias '{plugin}_pm'
                $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' AS rc_map_pm ON ' . $wpdb->posts . '.ID = rc_map_pm.post_id ';
            }
            return $join;
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
