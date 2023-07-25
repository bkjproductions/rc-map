<?php

if (!class_exists('POI_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
//    require_once (ABSPATH . 'wp-admin/includes/template.php');
    class POI_List_Table extends WP_List_Table
    {
        public function __construct()
        {
            parent::__construct(array(
                'singular' => 'Point of Interest', // Singular name for your table
                'plural'   => 'Points of Interest', // Plural name for your table
                'ajax'     => false,   // Set to true if you want to enable Ajax functionality
                'screen'   => 'edit-rc-poi',
            ));

        }

        /**
         * @param string $search
         * @return array|object|stdClass[]|null
         * purpose: Retrieves the data from the database
         */
        private function get_poi_data(string $search = ""): array|object|null
        {
            global $wpdb;
            $db = $wpdb;


            if (!empty($search)) {
                return $db->get_results(
                    query: "SELECT 
                                p.ID, 
                                p.post_title, 
                                p.post_content,
                                MAX(CASE WHEN pm.meta_key = 'rc_poi_location_address' THEN pm.meta_value END) AS rc_poi_location_address,
                                MAX(CASE WHEN pm.meta_key = 'rc_poi_location_geo_code' THEN pm.meta_value END) AS rc_poi_location_geo_code
                            FROM {$db->prefix}posts p
                            LEFT JOIN {$db->prefix}postmeta pm ON (p.ID = pm.post_id)
                                WHERE p.post_type = 'rc-poi'
                                    AND (
                                        p.post_title LIKE '%{$search}%'
                                        OR p.post_content LIKE '%{$search}%'
                                        OR pm.meta_key = 'rc_poi_location_address' AND pm.meta_value LIKE '%{$search}%'
                                        OR pm.meta_key = 'rc_poi_location_geo_code' AND pm.meta_value LIKE '%{$search}%'
                                    )
                            GROUP BY p.ID;
                                                
                    ", output: ARRAY_A
                );
            } else {
                return $db->get_results(query: "
                          SELECT
                                p.ID,
                                p.post_title,
                                p.post_content,
                                p.post_status,
                                MAX( CASE WHEN pm.meta_key = 'rc_poi_location_address' THEN pm.meta_value END ) 
                                    AS rc_poi_location_address,
                                MAX( CASE WHEN pm.meta_key = 'rc_poi_location_geo_code' THEN pm.meta_value END ) 
                                    AS rc_poi_location_geo_code
                            FROM {$db->prefix}posts p
                            LEFT JOIN {$db->prefix}postmeta pm ON (p.ID = pm.post_id)
                            WHERE p.post_type LIKE 'rc-poi' AND p.post_status LIKE 'publish'
                            GROUP BY p.ID
                            ORDER BY rc_poi_location_address;
                ", output: ARRAY_A);
            }
        }

        /**
         * @return string[]
         * purpose: Define the table columns
         */
        public function get_columns(): array
        {
            return array(
                //'cb'            => '<input type="checkbox" />',
                'ID'            => 'ID',
                'post_title'    => 'Name',
                'post_content'  => 'Content',
                'post_status'   => 'Status',
                'rc_poi_location_address' => 'Address',
                'rc_poi_location_geo_code' => 'Geo Code'

            );
        }

        /**
         * @return void
         * Bind the table with columns and data
         */
        public function prepare_items(): void
        {
            if (isset($_POST['edit-rc-poi']) && isset($_POST['s'])) {

                $poi_data = $this->get_poi_data($_POST['s']);
                error_log("SEARCHING");
                error_log(print_r($poi_data,true));

            } else {

                error_log("NO SEARCH");
                $poi_data = $this->get_poi_data();
                error_log(print_r($poi_data,true));

            }

            $columns = $this->get_columns();


            $hidden = array('cb','ID','post_content','post_status');
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);

            /* pagination */
            $per_page = 20;
            $current_page = $this->get_pagenum();
            $total_items = count($poi_data);

            $poi_data = array_slice($poi_data, (($current_page - 1) * $per_page), $per_page);

            $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page // items to show on a page
            ));

            usort($poi_data, array(&$this, 'usort_reorder'));

            $this->items = $poi_data;
        }

        /**
         * @param $item
         * @param $column_name
         * @return bool|mixed|string|void
         * purpose: Bind the data with the columns
         */
        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'ID':
                case 'post_title':
                case 'post_content':
                case 'rc_poi_location_geo_code':
                    return $item[$column_name];
                case 'rc_poi_location_address':
                    return ucwords($item[$column_name]);
                default:
                    return print_r($item, true); //Show the whole array for troubleshooting purposes
            }
        }

        /**
         * @param $item
         * @return string|void
         * purpose: show checkboxes
         */
        public function column_cb($item)
        {
            return sprintf(
                '<input type="checkbox" name="poi[]" value="%s" />',
                $item['ID']
            );
        }

        /**
         * @return array[]
         * purpose: retrieve the sortable columns
         */
        protected function get_sortable_columns(): array
        {
            return array(
                'post_title'  => array('post_title', false),
                'rc_poi_location_geo_code' => array('rc_poi_location_geo_code', false),
                'rc_poi_location_address'   => array('rc_poi_location_address', true)
            );
        }

        /**
         * @param $a
         * @param $b
         * @return int
         * purpose: the sort function
         */
        public function usort_reorder($a, $b): int
        {
            // If no sort, default to post_title
            $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'post_title';
            // If no order, default to asc
            $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
            // Determine sort order
            $result = strcmp($a[$orderby], $b[$orderby]);
            // Send final sort direction to usort
            return ($order === 'asc') ? $result : -$result;
        }



    }
}

