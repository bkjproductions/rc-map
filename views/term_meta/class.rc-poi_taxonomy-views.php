<?php

if (!class_exists('RC_POI_Taxonomy_Views')){

    class RC_POI_Taxonomy_Views{

        public function __construct()
        {

            // BUILD TERM META BOXES
            add_action( 'poi_add_form_fields', [$this, 'addTaxonomyMetaBoxes'], 10, 2 );
            add_action( 'poi_edit_form_fields', [$this, 'editTaxonomyMetaBoxes'], 10, 2 );

            // SAVE TERM META DATA
            add_action( 'created_poi', [$this, 'saveTaxonomyMetaBoxes'] );
            add_action( 'edited_poi', [$this, 'saveTaxonomyMetaBoxes' ]);
            add_action( 'admin_print_footer_scripts-edit-tags.php', [$this, 'getTaxonomyData']);
            add_action( 'updated_term_meta',[$this, 'handleAfterUpdateTermMeta']);

            // QUICK EDIT META DATA:
            add_action('quick_edit_custom_box', [$this, 'quickEditCustomBox' ],10,2);
            add_action('save_post_poi', [$this, 'savePostPOI']);



        }

        /**
         * @param $taxonomy
         * @return void
         * hook: {taxonomy}_add_form_fields
         * purpose: The HTML for the 'Add Taxonomy Field'
         */
        public function addTaxonomyMetaBoxes ($taxonomy):void {
            ?>
            <div class="form-field">
                <label for="rc_poi_tax_color">Hex Color</label>
                <input type="text" name="rc_poi_tax_color" id="rc_poi_tax_color" />
                <p class="rc_poi_tax_color">example: #ff0000 </p>
            </div>
            <?php
        }

        /**
         * @param $term
         * @param $taxonomy
         * @return void
         * hook: {taxonomy}_edit_form_fields
         * purpose: The HTML for the 'Edit Taxonomy Field'
         */
        public function editTaxonomyMetaBoxes ($term, $taxonomy):void {

            $value = get_term_meta( $term->term_id, 'rc_poi_tax_color', true );
            ?>
            <tr class="form-field">
                    <th>
                      <label for="rc_poi_tax_color">Color</label>
                    </th>
                    <td>
                      <input  name="rc_poi_tax_color" id="rc_poi_tax_color" type="text" value="<?php echo esc_html($value) ?>" />
                      <p class="rc_poi_tax_color">Hex Color (example: #ff0000) </p>
                    </td>
                  </tr>
            <?php
        }

        /**
         * @param $term_id
         * @return void
         * hook: created_{taxonomy}
         * purpose: Saves the Term Meta to the database
         * TODO: Validate the input
         */
        public function saveTaxonomyMetaBoxes ($term_id): void{
            update_term_meta($term_id, 'rc_poi_tax_color',sanitize_hex_color($_POST['rc_poi_tax_color']));
        }

        /**
         * @return void
         * Front end JS to populate quick edit fields
         * purpose: High jacks the HTML on the Custom Taxonomy LIST. Populates
         *          the respective input with the data from the column during
         *          edit.
         */
        public function getTaxonomyData():void {
            $current_screen = get_current_screen();


            if ( $current_screen->id != 'edit-poi' || $current_screen->taxonomy != 'poi' ) {
                return;
            }

            ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    let list = document.getElementById('the-list');

                    list.addEventListener('click', function(e) {
                        if (e.target && e.target.classList.contains('editinline')) {
                            e.preventDefault();
                            console.log(e.target.value)
                            let tr = e.target.closest('tr');
                            let val = tr.querySelector('td.rc_poi_tax_color').textContent;

                            // Update field
                            let inlineEditRow = document.querySelector('tr.inline-edit-row');
                            console.log(inlineEditRow)
                            let inputField = inlineEditRow.querySelector('input[name="rc_poi_tax_color"]');
                            inputField.value = val ? val.trim() : '#';
                        }
                    });
                });

            </script>
            <?php
        }
        /**
         * @param $column_name
         * @param $screen
         * @return void
         * hook: 'quick_edit_custom_box'
         * purpose:  The HTML for the quick edit input
         */
        public function quickEditCustomBox($column_name, $screen):void{
            if ($screen !== 'edit-tags'){return;}

            if ($column_name === 'rc_poi_tax_color'){
                global $taxonomy;


                // Get the term ID from the inline edit row
                $term_id = 0;
                if (isset($_REQUEST['edit_term'])) {
                    $term_id = (int)$_REQUEST['edit_term'];
                }

                // Display the custom meta field input for Quick Edit

                // Get the existing custom meta value for the term
                $meta_value = get_term_meta($term_id, 'rc_poi_tax_color', true);
                ?>

                <fieldset>
                    <div id="rc_poi_tax_color" class="inline-edit-col">
                        <label>
                            <span class="title"><?php _e( 'Hex Color', 'rc-map' ); ?></span>
                            <span style="width: 10rem;" class="input-text-wrap"><input type="text" name="<?php echo esc_attr( $column_name ); ?>" class="ptitle" value="#"></span>
                        </label>
                    </div>
                </fieldset>

                <?php
            }

        }
        /**
         * @param $term_id
         * @return void
         * hook: save_post_{taxonomy}
         * purpose: Saves the quick edit data.
         * TODO: Validate input
         */
        public function savePostPOI($term_id): void {

            if (!isset($_POST['action']) || $_POST['action'] !== 'inline-save' || !isset($_POST['rc_poi_tax_color'])) {
                return;
            }
            // Save the custom meta value
            $meta_value = sanitize_text_field($_POST['rc_poi_tax_color']);
            update_term_meta($term_id, 'rc_poi_tax_color', $meta_value);
        }

        public function handleAfterUpdateTermMeta():void{
            include_once(RC_MAP_PATH . 'includes/process.php');


        }
    }
}