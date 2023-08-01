<?php


include_once 'DataEncryption.php';

class ProcessMapOLD {
    private wpdb $db;
    private string $table_name;
    private string $google_api_key;
    private array $finished_data;
    const GOOGLE_API_ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';


    public function __construct(public DataEncryption $data_encryption){
        global $wpdb;
        $this->db = $wpdb;
        $this->finished_data = [];
        $this->table_name = $wpdb->prefix . 'bkj_map_poi_data';
        $this->google_api_key = $this->getApiKey();

    }

    public function generateMap ():void{
       error_log("Updating map data...!");
       $result =  $this->getPoints();
       if (!$result) { error_log("Something went wrong!");}

    }
    private function getApiKey(): string {
        return $this->data_encryption->decrypt( get_option('bkj_map_google_api_key' ));

    }
    private function getPoints ():bool{
        $points = $this->db->get_results("SELECT * FROM get_all_poi_data");
        if ( $points ) {
            foreach ( $points as $point ) {
                // Process each row of the result

                $encodedAddress = $this->encodeAddress($point);

                // get coordinates
                $hasGeoCode = $this->verifyGeoCode($point);
                if($hasGeoCode){
                    $coordinates =  $this->getExistingGeoCode($point);

                } else{

                    $isValidAddress = $this->isValidAddress($point);
                    if(!$isValidAddress) { continue; } // exit if no address exists

                    $coordinates = $this->getGeoCodeViaRequest($encodedAddress);
                    // since we just got them, let's put them in the db
                    $db_result = $this->addCoordinatesToDatabase($coordinates, $point->entry_id);
                }
                $this->generateOutput($coordinates, $point);



            }
        } else {
            // Handle the case when there are no results
            return 0;
            error_log('No results returned from the stored procedure.');
        }
        error_log(print_r($this->finished_data,true));

        return 1;
    }

    /**
     *
     * @param $point a point of interest.
     * @return string Encodes a URL version of the address.
     */
    private function encodeAddress ($point): string {

        return urlencode($point->address . ', ' .
            $point->city . ', ' . $point->state . ' ' .
            $point->zip_code);
    }
    private function htmlAddress ($point): string {
        return '<p>' . $point->address . '<br> ' .
            $point->city . ', ' . $point->state . ' ' .
            $point->zip_code . '<br> ' . $point->phone . '</p>';
    }
    private function isValidAddress($point): bool {
        return !(($point->address == ''));

    }
    private function verifyGeoCode($point):bool{
        return !(($point->geo_code == ''));
    }
    private function getExistingGeoCode($point): array
    {
         $coordinates = @$point->geo_code;  //@ = suppress errors

                $coordinates = explode(',',$coordinates);
                $data_lat_long['lat'] = $coordinates[0];
                $data_lat_long['lng'] = $coordinates[1];

        return $coordinates;
    }

    private function getGeoCodeViaRequest($encodeAddress): array | null
    {
        $url = self::GOOGLE_API_ENDPOINT . "?address=$encodeAddress&key=$this->google_api_key";

        $response = wp_remote_get($url); // WordPress HTTP request

        // Check if the request was successful
        if (is_array($response) && !is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

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
    private function addCoordinatesToDatabase($coordinates, $poi_id): bool {

        $geo_code = $coordinates['lat'] . ", " . $coordinates['lng'];

        // Prepare the data for the update
       $data_to_update = array(
           'geo_code' => $geo_code
       );

        // Prepare the WHERE clause for the update
        $where_condition = array(
            'id' => $poi_id
        );

        // Execute the update query
        $this->db->update($this->table_name, $data_to_update, $where_condition);

        // Optionally, check if the update was successful
        if ($this->db->last_error) {
            // Error occurred, handle it
            error_log("Update error: " . $this->db->last_error);

        } else {
            // Update successful
            return true;
            // Additional actions if needed
        }
        return false;
    }
    private function generateOutput($coordinates,$point):void{
        $htmlAddress = $this->htmlAddress($point);
        $output = [];

        $output['positionLat'] = $coordinates[0];
        $output['positionLong'] = $coordinates[1];
        $output['name'] = trim($point->name);
        $output['html'] = ($point->description ?? null) ?  $htmlAddress . '<p>' . $point->description . '</p>' : $htmlAddress;
        $output['logo'] = $point->logo ?? '';
        $output['url'] = $point->url;
        // see if there is a URL and if it has http in it already?
        if ( $point->url > '') {
            if (stripos($point->url,'http') === false ) {$point->url = 'https://' . $point->url;}
            $output['html'] .= '<p><a href="' . $point->url . '" target="_blank">Visit website</a></p>';
        }

        if (! isset( $this->finished_data[ $point->category ]) ) {
            $this->finished_data[ $point->category ] = array();
        }

        $this->finished_data[ $point->category ][] = $output;

    }
}

$bkj_map = new ProcessMap(new DataEncryption());
$bkj_map->generateMap();





