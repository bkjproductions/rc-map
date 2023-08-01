<?php
include_once(RC_MAP_PATH . 'class.rc-map-settings.php');

class GetCoordinates
{
    private wpdb $db;
    private string $table_name;
    private string $google_api_key;
    private array $finished_data;
    const GOOGLE_API_ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';


    public function __construct()
    {
        global $wpdb;
        $this->finished_data = [];
        $this->table_name = $wpdb->prefix . 'bkj_map_poi_data';
        $this->google_api_key = $this->getApiKey();

    }
    public function runScript(): void
    {
        error_log("Getting Coordinates from Google...!");
        $result = $this->getAllPoints();
        if (!$result) {
            error_log("Something went wrong!");
        }

    }

    /**
     * @return array|object|null
     * purpose:
     */
    public function getAllPoints(): array|object|null
    {
        global $wpdb;

        // Get all posts of the 'rc-poi' post type along with their meta data and term meta data and id
        $query = "
               SELECT p.ID, p.post_title, p.post_content, p.post_status,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_address' THEN pm.meta_value END) AS address,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_url' THEN pm.meta_value END) AS url,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_geo_code' THEN pm.meta_value END) AS geo_code,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_city' THEN pm.meta_value END) AS city,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_state' THEN pm.meta_value END) AS state,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_zip_code' THEN pm.meta_value END) AS zip_code,
                    MAX(CASE WHEN pm.meta_key = 'rc_poi_location_phone' THEN pm.meta_value END) AS phone,
                    GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') AS terms,
                    GROUP_CONCAT(DISTINCT tt.term_id SEPARATOR ', ') AS term_ids
                    FROM wp_posts p
                    LEFT JOIN wp_postmeta pm ON (p.ID = pm.post_id)
                    LEFT JOIN wp_term_relationships tr ON (p.ID = tr.object_id)
                    LEFT JOIN wp_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    LEFT JOIN wp_terms t ON (tt.term_id = t.term_id)
                    WHERE p.post_type LIKE 'rc-poi'
                    AND p.post_status LIKE 'publish'
                    GROUP BY p.ID;
    ";

        $results = $wpdb->get_results($query);

        // Process the data
        if (!empty($results)) {
            foreach ($results as $result) {

                $encodedAddress = $this->encodeAddress($result);
                // get coordinates
                $hasGeoCode = $this->verifyGeoCode($result);
                error_log(print_r($encodedAddress, true));
                if ($hasGeoCode) {
                    $coordinates = $this->getExistingGeoCode($result);


                } else {

                    $isValidAddress = $this->isValidAddress($result);
                    if (!$isValidAddress) {
                        error_log("Skipping: " . print_r($result->post_title, true) . " No Valid Address or GeoCode.");

                        continue;
                    } // exit if no address exists

                    $coordinates = $this->getGeoCodeViaRequest($encodedAddress);
                    // since we just got them, let's put them in the db
                    $db_result = $this->updatePostMetaCoordinates($coordinates, $result->ID);

                }


            }
        } else {
            // Handle the case when there are no results
            error_log('No results returned from the stored procedure.');
            return null;
        }
        // PRINT RESULTS TO ERROR LOG
        // error_log(print_r($this->finished_data, true));

        return $results;
    }


    /**
     * @return string
     * purpose: retrieves option from wp_options table
     */
    private function getApiKey(): string
    {
        return RC_Map_Settings::$options['rc_map_api_key'];

    }


    /**
     *
     * @param $point a point of interest.
     * @return string Encodes a URL version of the address.
     */
    private function encodeAddress($point): string
    {

        return urlencode($point->address . ', ' .
            $point->city . ', ' . $point->state . ' ' .
            $point->zip_code);
    }


    private function isValidAddress($point): bool
    {
        return !(($point->address == ''));

    }

    private function verifyGeoCode($point): bool
    {
        return !(($point->geo_code == ''));
    }

    private function getExistingGeoCode($point): array
    {
        $coordinates = @$point->geo_code;  //@ = suppress errors

        $coordinates = explode(',', $coordinates);
        $data_lat_long['lat'] = $coordinates[0];
        $data_lat_long['lng'] = $coordinates[1];

        return $coordinates;
    }

    private function getGeoCodeViaRequest($encodeAddress): array|null
    {
        $url = self::GOOGLE_API_ENDPOINT . "?address=$encodeAddress&key=$this->google_api_key";

        $response = wp_remote_get($url); // WordPress HTTP request

        // Check if the request was successful
        if (is_array($response) && !is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            error_log(print_r($response_body,true));
            // Handle the response data
            // For example, if the response is JSON, you can decode it using json_decode():
            $data = json_decode($response_body, true);


            return $data['results'][0]['geometry']['location'];


        } else {
            // Handle the request error
            $error_message = is_wp_error($response) ? $response->get_error_message() : 'Unknown error';

            error_log($error_message);
        }

        return null;

    }
    private function updatePostMetaCoordinates($coordinates, $post_id):bool {
        $geo_code = $coordinates['lat'] . ", " . $coordinates['lng'];

        return update_post_meta($post_id, 'rc_poi_location_geo_code', $geo_code);

    }

}


$map = new GetCoordinates();
$map->runScript();