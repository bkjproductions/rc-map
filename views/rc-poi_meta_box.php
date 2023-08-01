<?php
$meta = get_post_meta( $post->ID );
// ID	Name	Address	City	State	ZIP	URL	Phone number	Categories	GEOCODE	Description
$poi_url = get_post_meta( $post->ID, 'rc_poi_location_url', true ); // single: return as string, false, return array
$poi_address = get_post_meta( $post->ID, 'rc_poi_location_address', true ); // single: return as string, false, return array
$poi_city = get_post_meta( $post->ID, 'rc_poi_location_city', true ); // single: return as string, false, return array
$poi_state = get_post_meta( $post->ID, 'rc_poi_location_state', true ); // single: return as string, false, return array
$poi_zip_code = get_post_meta( $post->ID, 'rc_poi_location_zip_code', true ); // single: return as string, false, return array
$poi_phone = get_post_meta( $post->ID, 'rc_poi_location_phone', true ); // single: return as string, false, return array
$poi_geo_code = get_post_meta( $post->ID, 'rc_poi_location_geo_code', true ); // single: return as string, false, return array


?>
<table class="form-table rc_poi_meta_box">
    <input type="hidden" name="rc_poi_nonce" value="<?php echo wp_create_nonce( "rc_poi_nonce" ); ?>">
    <tr>
        <th>
            <label for="rc_poi_location_geo_code">Geo Code</label>
        </th>
        <td>
            <input
                    type="url"
                    name="rc_poi_location_geo_code"
                    id="rc_poi_location_geo_code"
                    class="regular-text link-url"
                    value="<?php echo ( isset( $poi_geo_code ) ) ? esc_html( $poi_geo_code ) : ''; ?>"
            >
        </td>
    </tr>

    <tr>
        <th>
            <label for="rc_poi_location_address">Address</label>
        </th>
        <td>
            <input
                type="text"
                name="rc_poi_location_address"
                id="rc_poi_location_address"
                class="regular-text link-text"
                value="<?php echo ( isset( $poi_address ) ) ? esc_html( $poi_address ) : ''; ?>"

            >
        </td>
    </tr>
    <tr>
        <th>
            <label for="rc_poi_location_city">City</label>
        </th>
        <td>
            <input
                    type="text"
                    name="rc_poi_location_city"
                    id="rc_poi_location_city"
                    class="regular-text link-text"
                    value="<?php echo ( isset( $poi_city ) ) ? esc_html( $poi_city ) : ''; ?>"

            >
        </td>
    </tr>
    <tr>
        <th>
            <label for="rc_poi_location_state">State</label>
        </th>

        <td>
            <input
                    type="text"
                    name="rc_poi_location_state"
                    id="rc_poi_location_state"
                    class="regular-text link-text"
                    value="<?php echo ( isset( $poi_state ) ) ? esc_html( $poi_state ) : ''; ?>"

            >
        </td>

    </tr>
    <tr>
        <th>
            <label for="rc_poi_location_zip_code">Zip Code</label>
        </th>

        <td>
            <input
                    type="text"
                    name="rc_poi_location_zip_code"
                    id="rc_poi_location_zip_code"
                    class="regular-text link-text"
                    value="<?php echo ( isset( $poi_zip_code ) ) ? esc_html( $poi_zip_code ) : ''; ?>"

            >
        </td>

    </tr>
    <tr>
        <th>
            <label for="rc_poi_location_address">Phone</label>
        </th>
        <td>
            <input
                    type="text"
                    name="rc_poi_location_phone"
                    id="rc_poi_location_phone"
                    class="regular-text link-text"
                    value="<?php echo ( isset( $poi_phone ) ) ? esc_html( $poi_phone ) : ''; ?>"

            >
        </td>
    </tr>
    <tr>
        <th>
            <label for="rc_poi_location_url">URL</label>
        </th>
        <td>
            <input
                type="url"
                name="rc_poi_location_url"
                id="rc_poi_location_url"
                class="regular-text link-url"
                value="<?php echo ( isset( $poi_url ) ) ? esc_url( $poi_url ) : ''; ?>"

            >
        </td>
    </tr>
</table>