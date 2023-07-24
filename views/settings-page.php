<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php
    $active_tab = $_GET['tab'] ?? 'main_options';
    ?>
    <h2 class="nav-tab-wrapper">
        <a href="?page=rc_map_admin&tab=main_options" class="nav-tab <?php echo $active_tab == 'main_options' ? 'nav-tab-active' : ''; ?>">Main Options</a>
        <a href="?page=rc_map_admin&tab=google_api_options" class="nav-tab <?php echo $active_tab == 'google_api_options' ? 'nav-tab-active' : ''; ?>">Google API</a>
        <a href="?page=rc_map_admin&tab=load_map_style_options" class="nav-tab <?php echo $active_tab == 'load_map_style_options' ? 'nav-tab-active' : ''; ?>">Load Style</a>
        <a href="?page=rc_map_admin&tab=load_map_data_options" class="nav-tab <?php echo $active_tab == 'load_map_data_options' ? 'nav-tab-active' : ''; ?>">Load Data</a>
        <a href="?page=rc_map_admin&tab=additional_options" class="nav-tab <?php echo $active_tab == 'additional_options' ? 'nav-tab-active' : ''; ?>">Additional Options</a>
    </h2>
    <form action="options.php" method="post">
        <?php
        if( $active_tab == 'main_options' ){
            settings_fields( 'rc_map_group' );
            do_settings_sections( 'rc_map_page1' );
            submit_button( 'Save Settings' );

        }else if ($active_tab == 'google_api_options'){
            settings_fields( 'rc_map_group' );
            do_settings_sections( 'rc_map_page2' );
            submit_button( 'Save Settings' );

        }else if ($active_tab == 'load_map_style_options'){
            settings_fields( 'rc_map_group' );
            do_settings_sections( 'rc_map_page3' );


        }else if ($active_tab == 'load_map_data_options'){
            settings_fields( 'rc_map_group' );
            do_settings_sections( 'rc_map_page3' );


        }else {
            settings_fields( 'rc_map_group' );
            do_settings_sections( 'rc_map_page4' );
            submit_button( 'Save Settings' );
        }

        ?>
    </form>
</div>
