<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <?php

    $active_tab = $_GET['tab'] ?? 'main_options';

    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=rc_map_admin&tab=main_options"
           class="nav-tab <?php echo $active_tab == 'main_options' ? 'nav-tab-active' : ''; ?>">Main Options</a>
        <a href="?page=rc_map_admin&tab=google_api_options"
           class="nav-tab <?php echo $active_tab == 'google_api_options' ? 'nav-tab-active' : ''; ?>">Google MAP</a>
        <a href="?page=rc_map_admin&tab=load_map_style_options"
           class="nav-tab <?php echo $active_tab == 'load_map_style_options' ? 'nav-tab-active' : ''; ?>">Load Style</a>
        <a href="?page=rc_map_admin&tab=load_map_data_options"
           class="nav-tab <?php echo $active_tab == 'load_map_data_options' ? 'nav-tab-active' : ''; ?>">Load Data</a>

        <?php
        if (RC_MAP_SETTINGS_ADDITIONAL_OPTIONS::$options['rc_map_show_generate_map_tab']){
            ?>
            <a href="?page=rc_map_admin&tab=generate_map"
               class="nav-tab <?php echo $active_tab == 'generate_map' ? 'nav-tab-active' : ''; ?>">Generate</a>
            <?php

        }
        ?>
        <a href="?page=rc_map_admin&tab=additional_options"
           class="nav-tab <?php echo $active_tab == 'additional_options' ? 'nav-tab-active' : ''; ?>">Additional
            Options</a>
    </h2>
    <?php
    if ($active_tab == 'generate_map'){

        ?>

        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <?php
            // Add the WordPress nonce field for security
            wp_nonce_field('get_geo_cords', 'get_geo_cords');
            do_settings_sections('rc_map_page5');
            ?>
            <input type="hidden" name="action" value="get_geo_cords">
            <div>After importing data, use this to retrieve missing any geo coordinates from Google API. <br> This will save the coordinates for each point of interest in the database.</div>
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Get Geo Coordinates', 'rc-map' ); ?></button>

        </form>
        <p></p>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <?php
            // Add the WordPress nonce field for security
            wp_nonce_field('process_nonce', 'process_nonce');
            ?>

            <input type="hidden" name="action" value="generate_code">
            <div>After you are satisfied with your data, generate the output files, then use shortcode [rc-map] to draw map on any page</div>
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Code', 'rc-map' ); ?></button>

        </form>
        <?php
    }
    else if ($active_tab == 'load_map_data_options') {
        ?>
        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                <?php

                // TODO: Add the WordPress nonce field for security

                do_settings_sections('rc_map_page4');
                $error = get_option('rc_map_error_load_data');

                if ($error){
                    echo '<div class="notice notice-error is-dismissible"><p>ERROR: '.$error . '</p></div>';
                    delete_option('rc_map_error_load_data');
                }
                ?>

                <div style="padding-bottom: 1rem;" "><strong>name | address | city | state | zip_code | url | phone | categories| geo_code</strong></div>
                 <input type="hidden" name="action" value="run_custom_script">

                 <textarea style="white-space: nowrap" id="rc_map_data" name="rc_map_data" rows="20" cols="80">Paste tab separated data here.</textarea>
                 <br>
                <p>
                    <label for="checkbox_id">Replace Existing Data:</label>
                    <input type="checkbox" id="rc_map_replace_data" name="rc_map_replace_data" value="1">

                </p>
                 <button type="submit" class="button button-primary"><?php esc_html_e( 'Import Data', 'rc-map' ); ?></button>
        </form>

        <?php


    } else {

        ?>
        <form action="options.php" method="post">
            <?php
            if ($active_tab == 'main_options') {
                settings_fields('rc_map_settings-main_options_group');
                do_settings_sections('rc_map_settings-main_options_page');
                submit_button('Save Settings');


            } else if ($active_tab == 'google_api_options') {
                settings_fields('rc_map_settings-google-map_options_group');
                do_settings_sections('rc_map_settings-google-maps_options_page');
                submit_button('Save Settings');


            } else if ($active_tab == 'load_map_style_options') {
                settings_fields('rc_map_settings-styles_options_group');
                do_settings_sections('rc_map_settings-styles_options_page');
                submit_button('Add new style');


            }else if ($active_tab == 'additional_options') {
                settings_fields('rc_map_settings-additional_options_group');
                do_settings_sections('rc_map_settings-additional_options_page');
                submit_button('Save Settings');

            } else {
                settings_fields('rc_map_group');
                do_settings_sections('rc_map_page5');
                submit_button('Save Settings');

            }
            ?>

        </form>
    <?php } ?>
</div>

