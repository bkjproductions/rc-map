<?php
include_once(RC_MAP_PATH . 'class.rc-map-settings.php');

class ProcessMap
{
    private wpdb $db;

    private array $finished_data;

    // MAP SETTINGS
    private string $google_api_key;
    private mixed $mapData;
    private mixed $markers;
    private mixed $snazzy_style;
    private mixed $categories;
    private string $default_longitude;
    private string $default_latitude;
    private string $default_zoom;
    private string $map_center_lat;
    private string $map_center_lon;


    public function __construct()
    {
        global $wpdb;
        $this->finished_data = [];
        $this->google_api_key = $this->getApiKey();
        $this->default_zoom = $this->getDefaultZoom();
        $this->map_center_lat = $this->getMapCenterLat();
        $this->map_center_lon = $this->getMapCenterLon();
        $this->default_latitude = $this->getDefaultLatitude();
        $this->default_longitude = $this->getDefaultLongitude();
        $this->snazzy_style = $this->getSnazzyStyle();
        $this->categories = $this->getCategories();
        $this->markers = $this->getAllPoints();


        $this->mapData = [];
        // Process template file

    }
    public function generateMap(): void
    {
        error_log("Updating map data...!");
        $result = $this->getAllPoints();

        if (!$result) {
            error_log("Something went wrong!");
        }else{
            error_log("Writing to JavaScript file...!");
            $this->generateJavaScriptFile();
            error_log("Writing to CSS file...!");
            $this->generateCSSFile();;
        }

    }
    public function getCategories():array|object|null {
        global $wpdb;

        $tax_query = "
                SELECT 
                    t.term_id,
                    t.name,
                    t.slug,
                    tx.count,
                    tm.meta_value AS color
                    from wp_terms t
                LEFT join wp_term_taxonomy tx USING (term_id)
                LEFT join wp_termmeta tm USING(term_id)
                WHERE taxonomy = 'poi' AND meta_key = 'rc_poi_tax_color';
        ";

        return $wpdb->get_results($tax_query, ARRAY_A);

    }
    public function getAllPoints(): array|object|null|string
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
                    GROUP_CONCAT(DISTINCT tt.term_id SEPARATOR ', ') AS term_ids,
					tm.meta_value AS color,
                    t.slug AS category
                    FROM wp_posts p
                    LEFT JOIN wp_postmeta pm ON (p.ID = pm.post_id)
                    LEFT JOIN wp_term_relationships tr ON (p.ID = tr.object_id)
                    LEFT JOIN wp_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
                    LEFT JOIN wp_terms t ON (tt.term_id = t.term_id)
                    LEFT JOIN wp_termmeta tm ON (tm.term_id = t.term_id)
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

                if ($hasGeoCode) {
                    $coordinates = $this->getExistingGeoCode($result);
                    $this->generateMarkerData($coordinates, $result);

                } else {

                    error_log("Missing Coordinates for:");
                    error_log(print_r($result->post_title,true));
                }


            }
        } else {
            // Handle the case when there are no results
            error_log('No results returned from the stored procedure.');
            return null;
        }
        //error_log(print_r($this->finished_data, true));

        return json_encode($results);
    }




    private function getApiKey(): string
    {
        return RC_Map_Settings::$options['rc_map_api_key'];

    }
    private function getDefaultZoom():string{

        return RC_Map_Settings::$options['rc_map_zoom'];

    }
    private function getDefaultLatitude():string{

        return RC_Map_Settings::$options['rc_map_latitude'];

    }
    private function getDefaultLongitude():string{

        return RC_Map_Settings::$options['rc_map_longitude'];

    }
    private function getMapCenterLat():string{

        return RC_Map_Settings::$options['rc_map_center_latitude'];

    }
    private function getMapCenterLon():string{

        return RC_Map_Settings::$options['rc_map_center_longitude'];

    }
    private function getSnazzyStyle():string{

        return get_option('rc_map_load_style');

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

    private function htmlAddress($point): string
    {
        return '<p>' . $point->address . '<br> ' .
            $point->city . ', ' . $point->state . ' ' .
            $point->zip_code . '<br> ' . $point->phone . '</p>';
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


    private function generateMarkerData($coordinates, $point): void
    {
        $htmlAddress = $this->htmlAddress($point);

        $output = [];
        //error_log(print_r($coordinates,true));
        $output['positionLat'] = $coordinates[0];
        $output['positionLong'] = $coordinates[1];
        $output['name'] = trim($point->post_title);
        $output['html'] = ($point->description ?? null) ? $htmlAddress . '<p>' . $point->description . '</p>' : $htmlAddress;
        $output['logo'] = $point->logo ?? '';
        $output['url'] = $point->url;
        // see if there is a URL and if it has http in it already?
        if (!($point->url > '')) {
            if (stripos($point->url, 'http') === false) {
                $point->url = 'https://' . $point->url;
            }
            $output['html'] .= '<p><a href="' . $point->url . '" target="_blank">Visit website</a></p>';
        }

        if (!isset($this->finished_data[$point->terms])) {
            $this->finished_data[$point->terms] = array();
        }

        $this->finished_data[$point->terms][] = $output;

    }
    public function generateCSSFile():void {
        error_log("regenerating CSS");
        // Load the CSS template file
        ob_start();
        include('map_template.css.php');
        $cssContent = ob_get_clean();

        $custom_css = '';
        foreach($this->categories as $category):

            $custom_css .= "#{$category['slug']}-link:before {background-color: {$category['color']};}\n";
        endforeach;

        // Replace the placeholders with serialized data in the JavaScript template
        $cssContent = str_replace(
            array('{{CUSTOM_CSS}}'),
            array($custom_css),
            $cssContent
        );

        // Save the generated JavaScript to a file
        $file_path =  RC_MAP_PATH. 'src/css/rc-map.css';
        $file = fopen($file_path, 'w'); // Open the file in write mode (creates the file if it doesn't exist)
        if ($file === false) {
            echo "Error opening the file.";
        } else {
            // Perform any writing or operations with the file here (e.g., writing content to the file)
            // ...
            fwrite($file, $cssContent);
            fclose($file); // Close the file after writing or operations are done
        }

    }
    private function generateJavaScriptFile():void {


        $serializedMapData = json_encode($this->mapData);
        $serializedMarkers = json_encode($this->markers);
        // Load the JavaScript template file
        ob_start();
        include('map_template.js.php');
        $javascriptContent = ob_get_clean();

        // Replace the placeholders with serialized data in the JavaScript template
        $javascriptContent = str_replace(
            array('{{MAP_DATA}}', '{{GOOGLE_MAPS_API_KEY}}', '{{MAP_STYLE}}', '{{BASE_URL}}',
                '{{CENTER_LAT}}', '{{CENTER_LON}}',
                '{{DEFAULT_LATITUDE}}', '{{DEFAULT_LONGITUDE}}', '{{DEFAULT_ZOOM}}', '{{MARKERS}}'),
            array($serializedMapData, $this->google_api_key, $this->snazzy_style, get_site_url(),
                $this->map_center_lat, $this->map_center_lon,
                $this->default_latitude, $this->default_longitude, $this->default_zoom, $this->markers),
            $javascriptContent
        );

        error_log("Map DATA:" . print_r($this->markers,true));

        // Save the generated JavaScript to a file


        $file_path =  RC_MAP_PATH. 'src/js/initMap.js';
        $file = fopen($file_path, 'w'); // Open the file in write mode (creates the file if it doesn't exist)
        if ($file === false) {
            echo "Error opening the file.";
        } else {
            // Perform any writing or operations with the file here (e.g., writing content to the file)
            // ...
            fwrite($file, $javascriptContent);
            fclose($file); // Close the file after writing or operations are done
        }

    }


}

$map = new ProcessMap();
$map->generateMap();