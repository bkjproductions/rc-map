<?php
/*
 * Description: Displays a Google map
 See /maputil/update_map_data.php for re-geocoding map_data.js files
 */

// alt key = AIzaSyDxvXx_r0Stp0UNN0eXH06NYaRrHDlf3-E

// possibly need
// define('TEMPLATE_URL',get_stylesheet_directory_uri() );
global $wpdb;

$tax_query = "
        SELECT 
            t.term_id,
            t.name,
            t.slug,
            tx.count,
            tm.meta_value AS Color
            from wp_terms t
        LEFT join wp_term_taxonomy tx USING (term_id)
        LEFT join wp_termmeta tm USING(term_id)
        WHERE taxonomy = 'poi' AND meta_key = 'rc_poi_tax_color';
";

$categories = $wpdb->get_results($tax_query, ARRAY_A);

?>


    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

    <style>
        #map {
            height: 100%;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>




    <div id="legend">
        <ul id="map-categories">
            <li><a id="All-link" href="#" class="all selected" data-category="All">Show All</a></li>
            <?php

                foreach ($categories as $category):
                    ?>
                    <li><a id="<?= $category['slug'] ?>-link" href="#" class="<?= $category['slug'] ?>" data-category="<?= $category['slug'] ?>"><?= $category['name'] ?></a></li>
                    <?php

                endforeach;


            ?>
        </ul>
    </div>
    <div class="clear"></div>
    <div style="width:100%; height:600px;">
        <div id="map" style="width: 100%; height: 100%"></div>
    </div>


<?php
//function bkj_map($atts = [], $content = null, $tag = '') {
//    wp_enqueue_script('map', TEMPLATE_URL . "/scripts/map.js", array('jquery'), '1.0', true);
//    wp_enqueue_script('map_data',  "/maputil/map_data.js", array('map'), '1.0', false);
//    // normalize attribute keys, lowercase
//    $atts = array_change_key_case((array)$atts, CASE_LOWER);
//
//    // override default attributes with user attributes
//    $my_atts = shortcode_atts([
//        'title' => 'Stratus Residences',
//    ], $atts, $tag);
//
//    // return output
//    $output = '';
//
//    $output .= '<script>
//		// need a global or two here
//		var initMap = function(){};
//		var myGoogleMap;
//		var allMarkers = [];
//		// Initial location of where our markers are:
//		var base_url = "https://staging2.stratusresidences.com/";
//		</script>';
//    //$output .= '<script src="' . TEMPLATE_URL . '/scripts/map.js"></script>' .
//    //$output .= '<script src="' . TEMPLATE_URL . '/scripts/map_data.js"></script>' .
//    $output .= '<script src= "https://maps.googleapis.com/maps/api/js?key=AIzaSyANEnuo9XKMDKhYozHXFqi9yJoDDKbqH5I&callback=initMap" async defer>
//		</script>';
//    $output .= '<div id="legend">
//			<ul id="map-categories">
//				<li><a id="All-link" href="#" class="all" data-category="All">Show All</a></li>
//				<li><a id="Education-link" href="#" class="education" data-category="Education">Education</a></li>
//				<li><a id="Groceries-link" href="#" class="groceries" data-category="Groceries">Groceries</a></li>
//				<li><a id="Hospital-link" href="#" class="hospital" data-category="Hospital">Hospital</a></li>
//				<li><a id="Recreation-link" href="#" class="recreation" data-category="Recreation">Recreation</a></li>
//				<li><a id="Restaurant-link" href="#" class="restaurant" data-category="Restaurant">Restaurant</a></li>
//				<li><a id="Retail-link" href="#" class="retail" data-category="Retail">Retail</a></li>
//				<li><a id="Transport-link" href="#" class="transport" data-category="Transport">Transport</a></li>
//				<li><a id="Wellness-link" href="#" class="wellness" data-category="Wellness">Wellness</a></li>
//			</ul>
//		</div>
//		<div class="clear"></div>';
//    $output .= '<div id="map_canvas"></div>';
//
//    return $output;
//
//
//}
//
//function bkj_map_init() {
//    add_shortcode('map', 'bkj_map');
//}
//
//add_action('init', 'bkj_map_init');
