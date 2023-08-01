<?php
global $wpdb;
include_once (RC_MAP_PATH . 'includes/class.POI_List_Table.php');
include_once (RC_MAP_PATH . 'includes/class.RC_List_Table.php');

    $args = (array(
        'singular' => 'Point of Interest', // Singular name for your table
        'plural'   => 'Points of Interest', // Plural name for your table
        'ajax'     => false,   // Set to true if you want to enable Ajax functionality
        'screen'   => 'edit-rc-poi',
    ));

$poi_table = new RC_List_Table($args);
?>


<div class="wrap" ><h2>POI List Table</h2>

    <?php
    // Prepare table
    $poi_table->prepare_items();
    ?>
<!--    <form method="post">-->
<!--        <input type="hidden" name="page" value="poi_list_table" />-->
<!--        --><?php //$poi_table->search_box('search', 'search_id'); ?>
<!--    </form>-->
<?php
// Display table
$poi_table->display();
echo '</div>';