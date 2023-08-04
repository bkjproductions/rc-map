<?php
include_once(RC_MAP_PATH . 'class.rc-map-settings.php');


class ProcessMap
{
    private wpdb $db;

    private array $finished_data;

    // MAP SETTINGS
    private mixed $mapData;
    private mixed $markers;
    private mixed $snazzy_style;
    private mixed $categories;


    public function __construct()
    {
	    // Check if the option rc_map_configured is set to '0'
	    $rc_map_configured = get_option('rc_map_configured', '0');
	    if ($rc_map_configured === '0') {
			error_log("No Settings Configured for Map.js.");
		    return; // Exit the constructor if rc_map_configured is '0'
	    }

        global $wpdb;
        $this->finished_data = [];
        $this->snazzy_style = $this->getSnazzyStyle();
        $this->categories = $this->getCategories();
        $this->markers = $this->getAllPoints();
        // Process template file
	    $this->generateMap();


    }
    public function generateMap(): void
    {
        error_log("Updating map data...!");
        $result = $this->getAllPoints();
		error_log(print_r($this->markers,true));

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






    private function getSnazzyStyle():string{
        $default_style = get_option('rc_map_load_style');
        $selected_style = RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS::$options['rc_map_style'];


        $map_styles = RC_Map_Settings::$options_styles;

        if (!$map_styles){
            return $default_style;
        }
	   //error_log(print_r($map_styles[$selected_style],true));
        return $map_styles[$selected_style];

    }
    private function getPrincipalInfo() :array {
        return [
            'name' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_principal-name'],
            'address' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_address'],
            'city' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_city'],
            'state' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_state'],
            'zip_code' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_zip-code'],
            'phone' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_phone'],
            'icon_url' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_principal-icon-url'],
            'latitude' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_principal-latitude'],
            'longitude' => RC_MAP_SETTINGS_MAIN_OPTIONS::$options['rc_map_principal-longitude']
        ];
    }
	private function getMapSettings():array{
		$settings =  [
			'zoom' => RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS::$options['rc_map_zoom'],
			'scale' => RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS::$options['rc_map_marker-scale'],
			'center_latitude' => RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS::$options['rc_map_center_latitude'],
			'center_longitude' => RC_MAP_SETTINGS_GOOGLE_MAP_OPTIONS::$options['rc_map_center_longitude']
		];

		//error_log(print_r($settings,true));
		return $settings;
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
		error_log(print_r($point->terms,true));
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


        $serializedMarkers = json_encode($this->markers);
        $serializedPrincipalInfo = json_encode($this->getPrincipalInfo());
		$serializedMapSettings = json_encode($this->getMapSettings());
		$serializedCategories = json_encode($this->getCategories());

        // Load the JavaScript template file
        ob_start();
        include('map_template.js.php');
        $javascriptContent = ob_get_clean();

        // Replace the placeholders with serialized data in the JavaScript template
        $javascriptContent = str_replace(
            array('{{MAP_SETTINGS}}','{{TERMS}}','{{PRINCIPAL_INFO}}', '{{MAP_STYLE}}', '{{BASE_URL}}', '{{MARKERS}}'),
            array($serializedMapSettings,$serializedCategories, $serializedPrincipalInfo, $this->snazzy_style, get_site_url(), $this->markers),
            $javascriptContent
        );

       // error_log("Map DATA:" . print_r($this->markers,true));

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
