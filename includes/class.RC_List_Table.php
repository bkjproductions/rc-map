<?php

if (!class_exists('RC_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

    class RC_List_Table extends WP_List_Table
    {

        public function __construct($args)
        {
            parent::__construct(array(
                'singular' => $args['singular'], // Singular name for your table
                'plural' => $args['plural'], // Plural name for your table
                'ajax' => $args['ajax'],   // Set to true if you want to enable Ajax functionality
                'screen' => $args['screen'],
            ));
            add_action('admin_footer', [$this,'initialize_datatables'],11);
        }
        function initialize_datatables() {
            $query = new WP_Query([
                    'post-type' => 'rc-poi',
                    'posts_per_page' => 1
            ]);
            if ($query->have_posts()){
                ?>
                <script>
                    jQuery(document).ready(function($) {
                        // Find the table with the 'wp-list-table' class
                        let $wpListTable = $('.wp-list-table');

                        // Append the desired ID to the table
                        $wpListTable.attr('id', 'poi-data-table');
                        $wpListTable.addClass('cell-border compact stripe hover order-column')

                        // Initialize DataTables on your wp_list_table
                        // Add a time delay of 1 second (1000 milliseconds) before initializing DataTables

                        $('#poi-data-table').DataTable({

                            columnDefs: [
                                {
                                    targets: [0], // Replace 0 with the column index you want to omit from sorting (zero-based index)
                                    orderable: false,
                                },
                            ],
                        });

                        // Adjust the width of the number of columns dropdown
                        const columnsDropdown = document.querySelector('div.dataTables_length select');
                        columnsDropdown.style.width = '4rem'; // Replace 200px with your desired width

                    });
                </script>
                <?php
                wp_reset_postdata();
            }

        }

        public function prepare_items()
        {
            $order_by = $_GET['orderby'] ?? '';
            $order = $_GET['order'] ?? '';
            $search_from = $_POST['s'] ?? '';

            $this->items =  $this->rc_list_table_data ( $order_by = '', $order = '', $search_from = '');

            $rc_column = $this->get_columns();
            $rc_hidden_col = $this->get_hidden_columns();

            $this->_column_headers = [ $rc_column, $rc_hidden_col ];

        }

        public function rc_list_table_data($order_by = '', $order ='' , $search_form = ''): array
        {
            global $wpdb;
            $data_array = [];

            // We need to us SQL to join term_meta and post_meta
            $sql = "
                SELECT p.ID, p.post_title, p.post_content, p.post_status,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_address' THEN pm.meta_value END) AS rc_poi_location_address,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_geo_code' THEN pm.meta_value END) AS rc_poi_location_geo_code,
                    GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS terms,
                    GROUP_CONCAT(DISTINCT t.term_id SEPARATOR ', ') AS term_ids
                FROM wp_posts p
                    LEFT JOIN wp_postmeta pm ON (p.ID = pm.post_id)
                    LEFT JOIN wp_term_relationships tr ON (p.ID = tr.object_id)
                    LEFT JOIN wp_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    LEFT JOIN wp_terms t ON (tt.term_id = t.term_id)
                WHERE p.post_type LIKE 'rc-poi'
                    AND p.post_status LIKE 'publish'
                GROUP BY p.ID;
            ";

            $results = $wpdb->get_results($sql);

            if ($results){
                foreach ($results as $post) {


                    $data_array[] = [
                        'post_title' => '<a data-post-id="'. $post->ID .'" href="'. get_edit_post_link($post->ID) . '">' . $post->post_title . '</a>',
                        'post_content' => $post->post_content,
                        'rc_poi_location_address' => $post->rc_poi_location_address,
                        'rc_poi_location_geo_code' => $post->rc_poi_location_geo_code,
                        'terms' => $post->terms,
                        'term_ids' => $post->term_ids
                    ];

                    // You can also create your custom HTML structure to display the data nicely

                }
            }

            //error_log(print_r($data_array,true));

            return $data_array;

        }

        public function get_bulk_actions()
        {
            return    [
	          'delete'       => 'Delete'
	          ];

        }
        public function handle_row_actions($item, $column_name, $primary)
        {
            if ($primary !== $column_name){
                return ;
            }
            $actions = [];
            $actions['edit'] = '<a>'.__( 'Edit', RC_TEXT_DOMAIN) .' </a>';
            $actions['delete'] = '<a class="rc-map-delete">'.__( 'Delete', RC_TEXT_DOMAIN) .' </a>';
            $actions['quick-edit'] = '<a>'.__( 'Quick Edit', RC_TEXT_DOMAIN) .' </a>';
            $actions['view'] = '<a>'.__( 'View', RC_TEXT_DOMAIN) .' </a>';

            return $this->row_actions($actions);
        }

        public function get_hidden_columns (){
            return ['ID'];
        }

        public function  get_columns()
        {
           return [
                   // 'cb' class name must match to column_cb() function (top checkbox)
               'cb' => '<input type="checkbox" class="rc-map-selected"/>',
               'post_title' => __( 'Name', RC_TEXT_DOMAIN),
               'rc_poi_location_address'=> __('Address', RC_TEXT_DOMAIN),
               'rc_poi_location_geo_code'=> __('Geo Code', RC_TEXT_DOMAIN),
               'terms' => __('Location Types', RC_TEXT_DOMAIN)

           ];
        }
        public function column_default($item, $column_name)
        {
            switch ($column_name) {
                case 'ID':
                case 'post_title':
                case 'rc_poi_location_address':
                case 'rc_poi_location_geo_code':
                case 'terms':
                    return $item[$column_name];
                default:
                    return 'No Column Found';
            }
        }

        /**
         * @param $items
         * @return string
         * purpose: returns html content for 'top checkbox' (select all')
         */
        public function column_cb( $items ): string
        {
            // the classname must match the class name listed in
            return '<input type="checkbox" class="rc-map-selected"/>';
        }
    }
}