<?php
global $wpdb;
include_once (RC_MAP_PATH . 'includes/class.POI_List_Table.php');
$poi_table = new POI_List_Table();
?>

<div class="wrap"><h2>POI List Table</h2>

    <?php
    // Prepare table
    $poi_table->prepare_items();
    ?>
    <form method="post">
        <input type="hidden" name="page" value="poi_list_table" />
        <?php $poi_table->search_box('search', 'search_id'); ?>
    </form>
<?php
// Display table
$poi_table->display();
echo '</div>';