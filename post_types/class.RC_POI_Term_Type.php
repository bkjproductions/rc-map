<?php

if (!class_exists('RC_POI_Term_Type')){
    class RC_POI_Term_Type {
        public RC_POI_Taxonomy_Views $poi_taxonomy;

        public function __construct()
        {
            // CREATE  TAXONOMY
            add_action( 'init', [$this, 'addTaxonomy'], 0 );

            // Load Taxonomy Meta Data Views Class
            include_once ( RC_MAP_PATH . 'views/term_meta/class.rc-poi_taxonomy-views.php');
            $this->poi_taxonomy = new RC_POI_Taxonomy_Views();

            // TERM META ADMIN COLUMNS to include sortable meta data
            add_filter( 'manage_edit-poi_columns', [$this, 'rcPOICPTColumns']);

            add_filter('manage_poi_custom_column', [$this,'displayPOICustomMetaColumn'], 10, 3);

        }
        // ADD CUSTOM TAXONOMY
        public function addTaxonomy (): void {

            $labels = array(
                'name'              => _x( 'Location Type', 'taxonomy general name' ),
                'singular_name'     => _x( 'Location Type', 'taxonomy singular name' ),
                // Add other labels as needed
            );

            $args = array(
                'hierarchical'      => false, // You can choose hierarchical or non-hierarchical
                'labels'            => $labels,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'point-of-interest' ),
            );

            register_taxonomy(
                taxonomy: 'poi',
                object_type: ['post', 'rc-poi' ],
                args: $args
            );
        }

        /**
         * @param $columns
         * @return mixed
         * hook: manage_edit-{taxonomy}_columns
         * purpose: Show/Hide columns in Taxonomy Term List
         */
        public function rcPOICPTColumns($columns)   {
            $columns['rc_poi_tax_color'] = 'Color';
            $columns['color'] = 'View';

            // Hide or show columns [ ** UPDATE BELOW ****
            unset($columns['description']);

            return $columns;
        }

        /**
         * @param $content
         * @param $column_name
         * @param $term_id
         * @return mixed|string
         * hook: manage_{taxonomy}_custom_column
         * purpose: Loads the data in the column
         */
        function displayPOICustomMetaColumn($content, $column_name, $term_id) {
            if ($column_name === 'rc_poi_tax_color') {
                // Retrieve the custom meta value for the term
                $meta_value = get_term_meta($term_id, 'rc_poi_tax_color', true);

                // Display the custom meta value
                $content = !empty($meta_value) ? $meta_value : '';
            }
            if ($column_name === 'color'){
                $meta_value = get_term_meta($term_id, 'rc_poi_tax_color', true);

                $content = !empty($meta_value) ? "<input type='color' disabled value='$meta_value'/ >" : '--';
            }

            return $content;
        }
    }

}