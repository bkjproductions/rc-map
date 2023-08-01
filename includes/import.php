<?php

if (isset($_POST['rc_map_data'])) {
    $data = sanitize_textarea_field($_POST['rc_map_data']);

    // Split the data into rows based on line breaks
    $rows = explode("\n", $data);

    foreach ($rows as $row) {
        // Split each row into individual data items based on tabs
        $data_items = explode("\t", $row);

        // Now you have an array of data items for each row, and you can process them accordingly
        $post_title = isset($data_items[0]) ? sanitize_text_field($data_items[0]) : ''; // Adjust index as per your data
        // Process other data items as needed
        $poi_address = isset($data_items[1]) ? sanitize_text_field($data_items[1]) : '';
        $poi_city = isset($data_items[2]) ? sanitize_text_field($data_items[2]) : '';
        $poi_state = isset($data_items[3]) ? sanitize_text_field($data_items[3]) : '';
        $poi_zip_code = isset($data_items[4]) ? sanitize_text_field($data_items[4]) : '';
        $poi_url = isset($data_items[5]) ? sanitize_url($data_items[5]) : '';
        $poi_phone = isset($data_items[6]) ? sanitize_text_field($data_items[6]) : '';
        $poi_category = isset($data_items[7]) ? sanitize_text_field($data_items[7]) : '';
        $poi_geo_code = isset($data_items[8]) ? sanitize_text_field($data_items[8]) : '';


        // Continue with the rest of the code to insert custom posts and assign taxonomy terms
        // ...


        $post_args = array(
            'post_title' => $post_title, // Adjust the index based on your data columns
            // Map other data to appropriate custom post fields
            'post_type' => 'rc-poi',
            'post_status' => 'publish',
        );
        $post_id = wp_insert_post($post_args);

        if ($post_id) {
            // Optionally, update custom fields for the post if needed
            update_post_meta($post_id, 'rc_poi_location_address', $poi_address);
            update_post_meta($post_id, 'rc_poi_location_city', $poi_city);
            update_post_meta($post_id, 'rc_poi_location_state', $poi_state);
            update_post_meta($post_id, 'rc_poi_location_zip_code', $poi_zip_code);
            update_post_meta($post_id, 'rc_poi_location_phone', $poi_phone);
            update_post_meta($post_id, 'rc_poi_location_url', $poi_url);
            update_post_meta($post_id, 'rc_poi_location_geo_code', $poi_geo_code);



            if (isset($data_items[7])) { // Assuming "categories" column is at index 7

                $tags = explode(',', $data_items[7]);
                $tags = array_map('trim', $tags);


                foreach ($tags as $tag) {

                    // Disable term caching to avoid potential issues with term_exists
                    $GLOBALS['wpdb']->cache_terms = false;

                    // Check if the term already exists in the "location_type" taxonomy
                    $existing_term = term_exists($tag, 'poi');

                    // Re-enable term caching
                    $GLOBALS['wpdb']->cache_terms = true;

                    if ($existing_term) {
                        // If the term exists, assign it to the current post
                        error_log(print_r($existing_term,true));
                        wp_set_object_terms($post_id, get_term($existing_term['term_id'])->term_id, 'poi', true);
                    } else {
                        // If the term doesn't exist, create a new term and assign it to the current post
                        $new_term = wp_insert_term($tag, 'poi');
                        if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
                            wp_set_object_terms($post_id, $new_term['term_id'], 'poi', true);
                        }
                    }
                }
            }
        }


    }
}
