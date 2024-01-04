<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PropertyHive Admin.
 *
 * @class       PH_Admin 
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */
class PH_Admin {

    /**
     * Constructor
     */
    public function __construct() 
    {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'current_screen', array( $this, 'conditional_includes' ) );
        add_action( 'current_screen', array( $this, 'disable_propertyhive_meta_box_dragging' ) );
        add_action( 'current_screen', array( $this, 'remove_propertyhive_meta_boxes_from_screen_options' ) );
        add_action( 'admin_notices', array( $this, 'review_admin_notices') );
        add_action( 'admin_menu', array( $this, 'admin_dashboard_pages' ) );
        add_action( 'admin_head', array( $this, 'admin_head' ) );
        add_action( 'admin_init', array( $this, 'admin_redirects' ) );
        add_action( 'admin_init', array( $this, 'prevent_access_to_admin' ) );
        add_action( 'admin_init', array( $this, 'view_email' ) );
        add_action( 'admin_init', array( $this, 'preview_emails' ) );
        add_action( 'admin_init', array( $this, 'record_recently_viewed' ) );
        add_action( 'admin_init', array( $this, 'export_applicant_list' ) );
        add_action( 'admin_init', array( $this, 'export_sub_grid' ) );
        add_action( 'admin_init', array( $this, 'check_hide_demo_data_tab' ) );
        add_action( 'admin_init', array( $this, 'check_install_add_on' ) );
    }

    public function check_install_add_on()
    {
        if ( 
            isset($_GET['ph_action']) && $_GET['ph_action'] == 'install_add_on' && 
            isset($_GET['ph_add_on_slug']) && !empty($_GET['ph_add_on_slug']) && 
            isset($_GET['ph_add_on_plugin']) && !empty($_GET['ph_add_on_plugin']) 
        )
        {
            $installed_plugins = get_option( 'propertyhive_pre_pro_add_ons', array());

            if ( empty($installed_plugins) )
            {
                $installed_plugins = array();
            }

            $installed_plugins[] = array(
                'slug' => ph_clean(base64_decode($_GET['ph_add_on_slug'])),
                'plugin' => ph_clean(base64_decode($_GET['ph_add_on_plugin']))
            );

            update_option( 'propertyhive_pre_pro_add_ons', $installed_plugins );

            wp_redirect( admin_url('admin.php?page=ph-settings&tab=features') );
            die();
        }
    }

    public function check_hide_demo_data_tab()
    {
        if ( isset($_GET['tab']) && $_GET['tab'] == 'demodata' && isset($_GET['hidetab']) )
        {
            update_option( 'propertyhive_hide_demo_data_tab', 'yes' );
            wp_redirect( admin_url('admin.php?page=ph-settings') );
            die();
        }
    }

    public function export_sub_grid()
    {
        if ( 
            isset($_GET['sub_grid']) && !empty(ph_clean($_GET['sub_grid']))
        ) 
        {
            ob_start();

            $df = fopen("php://output", 'w');

            $columns = array( 'id' => __( 'ID', 'propertyhive' ) );

            if ( strpos(ph_clean($_GET['sub_grid']), 'viewings') )
            {
                $columns['datetime'] = __( 'Date/Time', 'propertyhive' );
                $columns['property'] = __( 'Property', 'propertyhive' );
                //$columns['owner'] = __( 'Owner/Landlord', 'propertyhive' );
                $columns['applicant'] = __( 'Applicant(s)', 'propertyhive' );
                $columns['negotiator'] = __( 'Attending Negotiator(s)', 'propertyhive' );
                $columns['status'] = __( 'Status', 'propertyhive' );
                $columns['feedback'] = __( 'Feedback', 'propertyhive' );
            }
            elseif ( strpos(ph_clean($_GET['sub_grid']), 'offers') )
            {
                $columns['datetime'] = __( 'Date/Time', 'propertyhive' );
                $columns['property'] = __( 'Property', 'propertyhive' );
                //$columns['owner'] = __( 'Owner/Landlord', 'propertyhive' );
                $columns['applicant'] = __( 'Applicant(s)', 'propertyhive' );
                $columns['status'] = __( 'Status', 'propertyhive' );
                $columns['amount'] = __( 'Offer Amount', 'propertyhive' );
            }
            elseif ( strpos(ph_clean($_GET['sub_grid']), 'sales') )
            {
                $columns['date'] = __( 'Date', 'propertyhive' );
                $columns['property'] = __( 'Property', 'propertyhive' );
                //$columns['owner'] = __( 'Owner/Landlord', 'propertyhive' );
                $columns['applicant'] = __( 'Applicant(s)', 'propertyhive' );
                $columns['status'] = __( 'Status', 'propertyhive' );
                $columns['amount'] = __( 'Sale Amount', 'propertyhive' );
            }

            fputcsv($df, $columns);

            if ( isset($_GET['record_ids']) && !empty(ph_clean($_GET['record_ids'])) )
            {
                $record_ids = explode("|", ph_clean($_GET['record_ids']));

                if ( !empty($record_ids) )
                {
                    if ( strpos(ph_clean($_GET['sub_grid']), 'viewings') )
                    {
                        $args = array(
                            'post_type' => 'viewing',
                            'nopaging' => TRUE,
                            'fields' => 'ids',
                            'post__in' => $record_ids,
                            'order' => 'ASC',
                            'orderby' => 'meta_value',
                            'meta_key' => '_start_date_time',
                        );

                        $records_query = new WP_Query( $args );

                        if ( $records_query->have_posts() )
                        {
                            while ( $records_query->have_posts() )
                            {
                                $records_query->the_post();

                                $viewing = new PH_Viewing( get_the_ID() );

                                $property_id = (int)$viewing->_property_id;
                                $property_address = '';
                                if ( !empty($property_id) )
                                {
                                    $property = new PH_Property( $property_id );
                                    $property_address = $property->get_formatted_full_address();
                                }

                                $columns = array(
                                    get_the_ID(),
                                    date("H:i jS F Y", strtotime($viewing->_start_date_time)),
                                    $property_address,
                                    str_replace("<br>", "\n", $viewing->get_applicants()),
                                    $viewing->get_negotiators(),
                                    str_replace("<br>", "\n", $viewing->get_status()),
                                    $viewing->_feedback
                                );

                                fputcsv($df, $columns);
                            }
                        }
                    }
                    elseif ( strpos(ph_clean($_GET['sub_grid']), 'offers') )
                    {
                        $args = array(
                            'post_type' => 'offer',
                            'nopaging' => TRUE,
                            'fields' => 'ids',
                            'post__in' => $record_ids,
                            'order' => 'ASC',
                            'orderby' => 'meta_value',
                            'meta_key' => '_offer_date_time',
                        );

                        $records_query = new WP_Query( $args );

                        if ( $records_query->have_posts() )
                        {
                            while ( $records_query->have_posts() )
                            {
                                $records_query->the_post();

                                $offer = new PH_Offer( get_the_ID() );

                                $property_id = (int)$offer->_property_id;
                                $property_address = '';
                                if ( !empty($property_id) )
                                {
                                    $property = new PH_Property( $property_id );
                                    $property_address = $property->get_formatted_full_address();
                                }

                                $columns = array(
                                    get_the_ID(),
                                    date("H:i jS F Y", strtotime($offer->_offer_date_time)),
                                    $property_address,
                                    str_replace("<br>", "\n", $offer->get_applicants()),
                                    $offer->_status,
                                    html_entity_decode($offer->get_formatted_amount())
                                );

                                fputcsv($df, $columns);
                            }
                        }
                    }
                    elseif ( strpos(ph_clean($_GET['sub_grid']), 'sales') )
                    {
                        $args = array(
                            'post_type' => 'sale',
                            'nopaging' => TRUE,
                            'fields' => 'ids',
                            'post__in' => $record_ids,
                            'order' => 'ASC',
                            'orderby' => 'meta_value',
                            'meta_key' => '_sale_date_time',
                        );

                        $records_query = new WP_Query( $args );

                        if ( $records_query->have_posts() )
                        {
                            while ( $records_query->have_posts() )
                            {
                                $records_query->the_post();

                                $sale = new PH_Sale( get_the_ID() );

                                $property_id = (int)$sale->_property_id;
                                $property_address = '';
                                if ( !empty($property_id) )
                                {
                                    $property = new PH_Property( $property_id );
                                    $property_address = $property->get_formatted_full_address();
                                }

                                $columns = array(
                                    get_the_ID(),
                                    date("jS F Y", strtotime($sale->_sale_date_time)),
                                    $property_address,
                                    str_replace("<br>", "\n", $sale->get_applicants()),
                                    $sale->_status,
                                    html_entity_decode($sale->get_formatted_amount())
                                );

                                fputcsv($df, $columns);
                            }
                        }
                    }
                }
            }

            fclose($df);

            $output = ob_get_clean();

            $filename = sanitize_title(ph_clean($_GET['sub_grid'])) . '-' . date("YmdHis") . '.csv';

            // disable caching
            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");

            // force download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");

            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");

            echo $output;

            die();
        }
    }

    public function export_applicant_list()
    {
        if ( 
            isset($_POST['submitted_applicant_list']) && $_POST['submitted_applicant_list'] == '1' &&
            isset($_POST['export_applicant_list_results']) && $_POST['export_applicant_list_results'] == '1' 
        ) 
        {
            include_once( 'class-ph-admin-applicant-list.php' );
            $ph_admin_applicant_list = new PH_Admin_Applicant_List();
            $ph_admin_applicant_list->export();
        }
    }

    public function record_recently_viewed()
    {
        global $pagenow;

        if ( 
            'post.php' === $pagenow &&
            isset($_GET['post']) && 
            in_array(
                get_post_type((int)$_GET['post']), 
                apply_filters( 'propertyhive_post_types_with_tabs', array('property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale') )
            ) 
        )
        {
            $recently_viewed = get_user_meta( get_current_user_id(), '_propertyhive_recently_viewed', TRUE );

            if ( !is_array($recently_viewed) )
            {
                $recently_viewed = array();
            }

            foreach ( $recently_viewed as $time => $post )
            {
                if ( (int)$_GET['post'] == $post['id'] )
                {
                    unset($recently_viewed[$time]);
                }
            }

            $title = get_the_title((int)$_GET['post']);

            switch ( get_post_type((int)$_GET['post']) )
            {
                case "appraisal":
                {
                    $appraisal = new PH_Appraisal( (int)$_GET['post'] );
                    $title = $appraisal->get_formatted_summary_address();
                    break;
                }
                case "property":
                {
                    $property = new PH_Property( (int)$_GET['post'] );
                    $title = $property->get_formatted_summary_address();
                    break;
                }
                case "enquiry":
                case "viewing":
                case "offer":
                case "sale":
                {
                    $property_id = get_post_meta( (int)$_GET['post'], '_property_id', TRUE );
                    if ( $property_id != '' )
                    {
                        $property = new PH_Property( (int)$property_id );
                        $title = $property->get_formatted_summary_address();
                    }
                    break;
                }
            }

            $title = ucfirst(get_post_type((int)$_GET['post'])) . ' - ' . $title;

            $recently_viewed = array(time() => array(
                'id' => (int)$_GET['post'],
                'title' => $title,
                'post_type' => get_post_type((int)$_GET['post']),
                'edit_link' => get_edit_post_link((int)$_GET['post']),
            )) + $recently_viewed;

            $recently_viewed = array_slice($recently_viewed, 0, 10, TRUE);

            update_user_meta( get_current_user_id(), '_propertyhive_recently_viewed', $recently_viewed );
        }
    }
    
    public function admin_dashboard_pages()
    {
        if ( ! empty( $_GET['page'] ) ) 
        {
            switch ( sanitize_title($_GET['page']) ) 
            {
                case 'ph-installed':
                {
                    add_dashboard_page(
                        __( 'Welcome to Property Hive', 'propertyhive'  ),
                        __( 'Welcome to Property Hive', 'propertyhive'  ),
                        'manage_propertyhive',
                        sanitize_title($_GET['page']),
                        array( $this, 'installed_screen' )
                    );

                    break;
                }
            }
        }
    }

    public function installed_screen()
    {
?>
    <div class="wrap propertyhive-installed-screen">

        <h1><?php _e( 'Welcome to Property Hive', 'propertyhive'  ); ?></h1>

        <div class="intro-text">
            <p>Thank you choosing Property Hive to power your next property website. Below you'll find useful links, tips on getting started, and more.</p>
        </div>

        <div class="panels">

            <div class="panel">

                <h2>Getting Started</h2>

                <p>Now that you've installed Property Hive you'll notice a new 'Property Hive' item in the left hand menu of WordPress.</p>

                <img src="<?php echo PH()->plugin_url(); ?>/assets/images/admin/installed-screen/wordpress-menu.png" style="margin:0 auto; display:block; max-width:100%;" alt="Property Hive menu in WordPress">

                <p><strong>Configure Property Hive:</strong> We recommend that you start by navigating to the '<a href="<?php echo admin_url( 'admin.php?page=ph-settings' ); ?>" target="_blank">Settings</a>' area of Property Hive and configuring the options available.</p>

                <p><strong>Add Your First Property:</strong> See for yourself how easy it is to use Property Hive by <a href="<?php echo admin_url( 'post-new.php?post_type=property' ); ?>" target="_blank">adding your first property</a>.</p>

            </div>

            <div class="panel">

                <h2>Extending Property Hive</h2>

                <p>We have a <a href="https://wp-property-hive.com/add-ons/" target="_blank">wide range of add ons</a> available to add extra functionality to your website.</p>

                <a href="https://wp-property-hive.com/add-ons/" target="_blank"><img src="<?php echo PH()->plugin_url(); ?>/assets/images/admin/installed-screen/add-ons.png" style="margin:0 auto; border:1px solid #CCC; display:block; max-width:100%;" alt="Property Hive Free Add Ons"></a>

                <p><strong style="font-size:14px;"><a href="https://wp-property-hive.com/add-ons/?category=free" target="_blank">Free Add Ons</a></strong><br>
                From our template assistant add on to a variety of calculators, these free add ons are great additions to any property website.</p>

                <p><strong style="font-size:14px;"><a href="https://wp-property-hive.com/add-ons/?category=enhancements" target="_blank">Website Enhancements</a></strong><br>
                Map View, Radial Search, Property Shortlist, Infinite Scroll, and lots more. Wow your users with the functionality provided with these add ons.</p>

                <p><strong style="font-size:14px;"><a href="https://wp-property-hive.com/add-ons/?category=tools" target="_blank">Internal Tools</a></strong><br>
                Add ons aimed to make your life easier and to save you time. Includes Digital Window Displays, Address Lookup and more.</p>

                <p><strong style="font-size:14px;"><a href="https://wp-property-hive.com/add-ons/?category=import" target="_blank">Import and Export</a></strong><br>
                Import properties from third party software or send your properties to portals like Rightmove, Zoopla and more. These add ons automate the import and export of property data.</p>

            </div>

            <div class="panel">

                <h2>Support</h2>

                We pride ourselves on great support at Property Hive and will always do what we can to help you make create the best site possible. Please find below some useful links relating to our support:

                <p><strong style="font-size:14px;">Documentation</strong><br>
                We have documentation <a href="https://docs.wp-property-hive.com" target="_blank">available on our website</a> covering setup advice, help with theming, and more.</p>

                <p><strong style="font-size:14px;">Our Support Policy</strong><br>
                Our <a href="https://wp-property-hive.com/support-policy/" target="_blank">Support Policy is available to view here</a> and outlines how you can get in touch, how we will (and won't) help, and how to report bugs.</p>

            </div>

            <div class="panel">

                <h2>Additional Information</h2>

                <p><strong style="font-size:14px;">Need a Theme?</strong><br>
                Property Hive does <a href="https://wp-property-hive.com/which-wordpress-themes-work-with-property-hive/" target="_blank">integrate with any new or existing theme</a>. If however you need to get up and running quickly, or just want to have a play before committing, then our free <a href="https://wp-property-hive.com/honeycomb" target="_blank">Honeycomb theme</a> might be right for you.</p>

                <a href="https://wp-property-hive.com/honeycomb" target="_blank"><img src="<?php echo PH()->plugin_url(); ?>/assets/images/admin/installed-screen/honeycomb-screenshot.png" style="margin:0 auto; display:block; max-width:80%;" alt="Property Hive Free Honeycomb Theme"></a>

                <p><strong style="font-size:14px;">Leave a Review</strong><br>
                If you've found Property Hive useful we'd love it if you could spare a moment to tell others just how great we are by <a href="https://wordpress.org/support/plugin/propertyhive/reviews/?filter=5" target="_blank">leaving a review</a>.</p>

                <p><strong style="font-size:14px;">Contribute</strong><br>
                Property Hive is completely open-source meaning anyone can access and contribute to the code. Fixing bugs and adding functionality can be done by anyone with coding knowledge. <a href="https://github.com/propertyhive/WP-Property-Hive" target="_blank">Visit us on GitHub</a> to get started.</p>

                <p><strong style="font-size:14px;">Our Feature Roadmap</strong><br>
                View our <a href="https://trello.com/b/jb7bjB6j/property-hive-roadmap" target="_blank">feature roadmap</a> to see what's coming up, vote on feature, or submit your own ideas.</p>


            </div>

        </div>

    </div>
<?php
    }

    /**
     * Hide Individual Dashboard Pages
     *
     * @access public
     * @since 1.0
     * @return void
     */
    public function admin_head() 
    {
        remove_submenu_page( 'index.php', 'ph-installed' );
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        // Functions
        include_once( 'ph-admin-functions.php' );
        include_once( 'ph-meta-box-functions.php' );

        // Classes
        include_once( 'class-ph-admin-post-types.php' );
        //include_once( 'class-ph-admin-taxonomies.php' );

        // Classes we only need if the ajax is not-ajax
        if ( ! is_ajax() ) {
            include( 'class-ph-admin-menus.php' );
            include( 'class-ph-admin-assets.php' );
        }
    }

    /**
     * Include admin files conditionally.
     */
    public function conditional_includes() {
        if ( ! $screen = get_current_screen() ) {
            return;
        }

        switch ( $screen->id ) {
            case 'dashboard' :
                include( 'class-ph-admin-dashboard.php' );
            break;
            case 'plugins' :
                include( 'class-ph-admin-plugin-updates.php' );
            break;
            case 'users':
            case 'user':
            case 'profile':
            case 'user-edit':
                include( 'class-ph-admin-profile.php' );
                break;
        }
    }

    /**
     * Include admin files conditionally
     */
    public function disable_propertyhive_meta_box_dragging()
    {
        $screen = get_current_screen();
        
        if ( in_array( $screen->id, ph_get_screen_ids() ) ) 
        {
            //wp_deregister_script('postbox');
        }
    }
    
    /**
     * Remove PropertyHive meta boxes
     */
    public function remove_propertyhive_meta_boxes_from_screen_options()
    {
        global $wp_meta_boxes;
        
        $screen = get_current_screen();
        
        if ( in_array( $screen->id, array( 'property' ) ) ) 
        {
            //wp_deregister_script('postbox');
        }
    }

    public function review_admin_notices()
    {
	    global $wpdb;

        if ( current_user_can( 'manage_options' ) )
        {
            $propertyhive_review_prompt_due_timestamp = get_option( 'propertyhive_review_prompt_due_timestamp', 0 );
            if ( $propertyhive_review_prompt_due_timestamp != '' && $propertyhive_review_prompt_due_timestamp != 0 )
            {
                if ( $propertyhive_review_prompt_due_timestamp < time() )
                {
                    echo "<div class=\"notice notice-info\" id=\"ph_notice_leave_review\">
                        <p>
                            " . __( '<strong>Finding Property Hive useful?</strong> Please take a minute to <a href="https://wordpress.org/support/plugin/propertyhive/reviews/?filter=5#new-post" target="_blank">leave us a ★★★★★ review</a>', 'propertyhive' ) . "
                        </p>
                        <p>
                            <a href=\"https://wordpress.org/support/plugin/propertyhive/reviews/?filter=5#new-post\" target=\"_blank\" class=\"button-primary\">Leave a Review</a>
                            <a href=\"\" class=\"button\" id=\"ph_dismiss_notice_leave_review\">No Thanks</a>
                        </p>
                    </div>";
                }
            }

            if ( 
                !class_exists('PH_Demo_Data') && 
                get_option( 'propertyhive_install_timestamp', '' ) >= 1618268400 &&
                get_option( 'propertyhive_hide_demo_data_tab', '' ) != 'yes' && 
                (
                    !isset($_GET['page'])
                    ||
                    (
                        isset($_GET['page']) && sanitize_title($_GET['page']) != 'ph-installed' && sanitize_title($_GET['page']) != 'ph-settings'
                    )
                )
            )
            {
                echo "<div class=\"notice notice-info\" id=\"ph_notice_demo_data\">
                        <p>
                            " . __( '<strong>New To Property Hive?</strong> Did you know that you can quickly import demo data to get a feel for how Property Hive works?', 'propertyhive' ) . "
                        </p>
                        <p>
                            <a href=\"". admin_url('admin.php?page=ph-settings&tab=demodata') . "\" class=\"button-primary\">Import Demo Data</a>
                            <a href=\"\" class=\"button\" id=\"ph_dismiss_notice_demo_data\">Dismiss</a>
                        </p>
                        
                    </div>";
            }

            if ( 
                get_option('propertyhive_search_results_page_id', '') == '' && 
                (
                    !isset($_GET['page'])
                    ||
                    (
                        isset($_GET['page']) && sanitize_title($_GET['page']) != 'ph-installed' && sanitize_title($_GET['page']) != 'ph-settings'
                    )
                ) &&
                get_option( 'missing_search_results_notice_dismissed', '' ) != 'yes'
            )
            {
                echo "<div class=\"notice notice-info\" id=\"ph_notice_missing_search_results\">
                        <p>
                            " . __( 'We noticed that you haven\'t assigned a page to be your \'Search Results\' page yet. We recommend that you do this in order to display properties on your site.', 'propertyhive' ) . "
                        </p>
                        <p>
                            <a href=\"". admin_url('admin.php?page=ph-settings&tab=general') . "\" class=\"button-primary\">Go To Property Hive Settings</a>
                            <a href=\"\" class=\"button\" id=\"ph_dismiss_notice_missing_search_results\">Dismiss</a>
                        </p>
                        
                    </div>";
            }

            if ( 
                get_option('propertyhive_maps_provider') !== 'osm' &&
                get_option('propertyhive_google_maps_api_key', '') == '' && 
                !isset($_POST['propertyhive_google_maps_api_key']) &&
                (
                    !isset($_GET['page'])
                    ||
                    (
                        isset($_GET['page']) && sanitize_title($_GET['page']) != 'ph-installed'
                    )
                ) &&
                get_option( 'missing_google_maps_api_key_notice_dismissed', '' ) != 'yes'
            )
            {
                echo "<div class=\"notice notice-info\" id=\"ph_notice_missing_google_maps_api_key\">
                        <p>
                            " . __( 'We noticed that you haven\'t entered a Google Maps API key yet. If wishing to display a map on your website it\'s recommended that you <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">create one</a> and <a href="'. admin_url('admin.php?page=ph-settings&tab=general&section=map') . '">enter it</a>.', 'propertyhive' ) . "
                        </p>
                        <p>
                            <a href=\"". admin_url('admin.php?page=ph-settings&tab=general&section=map') . "\" class=\"button-primary\">Enter Google Maps API Key</a>
                            <a href=\"\" class=\"button\" id=\"ph_dismiss_notice_missing_google_maps_api_key\">Dismiss</a>
                        </p>
                        
                    </div>";
            }

            if ( 
                get_option('propertyhive_license_key', '') != '' &&
                get_option( 'missing_invalid_expired_license_key_notice_dismissed', '' ) != 'yes' && 
                (
                    !isset($_GET['page'])
                    ||
                    (
                        isset($_GET['page']) && sanitize_title($_GET['page']) != 'ph-installed' && sanitize_title($_GET['page']) != 'ph-settings'
                    )
                )
            )
            {
                $license = PH()->license->get_current_license();
                $output = '';

                if ( isset($license['active']) && $license['active'] != '1' )
                {
                    $output = __( 'Your Property Hive license key is inactive.', 'propertyhive' );
                }
                else
                {
                    
                }

                if ( $output != '' )
                {
                    echo "<div class=\"notice notice-info\" id=\"ph_notice_invalid_expired_license_key\">
                        <p>
                            " . $output . "
                        </p>
                        <p>
                            <a href=\"". admin_url('admin.php?page=ph-settings&tab=licensekey') . "\" class=\"button-primary\">Go To License Key Settings</a>
                            <a href=\"\" class=\"button\" id=\"ph_dismiss_notice_invalid_expired_license_key\">Dismiss</a>
                        </p>
                        
                    </div>";
                }
            }

            $screen = get_current_screen();
            if ( in_array( $screen->id, array( 'dashboard' ) ) )
            {
                // Email Cron Warning
                $queuedEmailsExist = (bool)$wpdb->get_var("SELECT 1 FROM " . $wpdb->prefix . "ph_email_log WHERE status = '' LIMIT 1");
                $cronIsNextScheduled = wp_next_scheduled('propertyhive_process_email_log');
    	        if ( $queuedEmailsExist && ( $cronIsNextScheduled === false || $cronIsNextScheduled < strtotime('24 hours ago') ) )
    	        {
                    echo '
                        <div class="notice notice-error" id="ph_notice_email_cron_not_running">
                            <p>' . __( 'The Property Hive email queue does not appear to be running', 'propertyhive' ) . '
                            </p>
                            <p>
                                <a href="'. admin_url('admin.php?page=ph-settings&tab=email&section=log&status=queued') . '" class="button-primary">Go To Email Queue</a>
                                <!--<a href="" class="button" id="ph_dismiss_notice_email_cron_not_running">Dismiss</a>-->
                            </p>
                        </div>
                    ';
    	        }
            }
        }

        if ( isset($_GET['propertyhive_contacts_merged']) )
        {
            echo '
                <div class="notice notice-info">
                    <p>' . __( 'Contacts merged successfully', 'propertyhive' ) . '</p>
                </div>
                ';
        }
    }

    /**
     * Handle redirects to welcome page after install.
     */
    public function admin_redirects()
    {
        // Setup wizard redirect
        if ( get_transient( '_ph_activation_redirect' ) ) 
        {
            delete_transient( '_ph_activation_redirect' );

            // Don't do redirect if part of multisite, doing batch-activate, or if no permission
            if ( is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_propertyhive' ) ) {
                return;
            }

            wp_safe_redirect( admin_url( 'index.php?page=ph-installed' ) );
            exit;
        }
    }

    public function prevent_access_to_admin()
    {
        global $current_user;

        $user_roles = $current_user->roles;
        $user_role = array_shift($user_roles);

        // Check role, but also AJAX as request to admin-ajax.php will still need to be made
        if ( !defined( 'DOING_AJAX' ) && $user_role === 'property_hive_contact' )
        {
            exit( wp_redirect( home_url( '/' ) ) );
        }
    }

    /**
     * View previously sent email
     *
     * @return string
     */
    public function view_email() {

        global $wpdb;

        if ( isset( $_GET['view_propertyhive_email'] ) ) 
        {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'view-email' ) ) 
            {
                die( 'Security check' );
            }

            if ( isset( $_GET['email_id'] ) )
            {
                $email_log = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "ph_email_log WHERE email_id = '" . esc_sql( (int)$_GET['email_id'] ) . "'" );
                if ( null !== $email_log ) 
                {
                    $body = $email_log->body;

                    if ( extension_loaded('zlib') && @gzuncompress($body) !== false ) 
                    {
                        $body = gzuncompress($body);
                    }

                    echo apply_filters( 'propertyhive_mail_content', PH()->email->style_inline( PH()->email->wrap_message( $body ) ) );
                    
                }
                else
                {
                    die("Email not found");
                }
            }
            
            exit;
        }
    }

    /**
     * Preview email template.
     *
     * @return string
     */
    public function preview_emails() {
        if ( isset( $_GET['preview_propertyhive_email'] ) ) 
        {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'propertyhive-matching-properties' ) && ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'propertyhive-matching-applicants' ) ) 
            {
                die( 'Security check' );
            }

            $current_user = wp_get_current_user();

            // get the preview email content
            if ( isset($_GET['property_id']) )
            {
                $email_property_ids = array((int)$_GET['property_id']);
            }
            elseif ( isset($_POST['email_property_id']) )
            {
                $email_property_ids = explode(",", sanitize_text_field($_POST['email_property_id']));
            }

            $body = stripslashes(sanitize_textarea_field($_POST['body']));

            if ( isset($_GET['contact_id']) )
            {
                $contact = new PH_Contact((int)$_GET['contact_id']);
                $body = str_replace("[contact_name]", $contact->post_title, $body);
                $body = str_replace("[contact_dear]", $contact->dear(), $body);
            }
            $body = str_replace("[property_count]", count($email_property_ids) . ' propert' . ( ( count($email_property_ids) != 1 ) ? 'ies' : 'y' ), $body);

            $office_counts = array();

            if ( strpos($body, '[properties]') !== FALSE )
            {
                ob_start();

                if ( !empty($email_property_ids) )
                {
                    foreach ( $email_property_ids as $email_property_id )
                    {
                        $property = new PH_Property((int)$email_property_id);

                        if ( $property->office_id != '' && $property->office_id != 0 )
                        {
                            if ( !isset($office_counts[$property->office_id]) ) { $office_counts[$property->office_id] = 0; }
                            ++$office_counts[$property->office_id];
                        }

                        ph_get_template( 'emails/applicant-match-property.php', array( 'property' => $property ) );
                    }
                }
                $body = str_replace("[properties]", ob_get_clean(), $body);
            }

            $office_name = '';
            $office_email_address = '';

            $office_id = get_user_meta($current_user->ID, 'office_id', TRUE);
            if ($office_id == '')
            {
                // No office against user. Use email address of office with most properties
                if ( !empty($office_counts) )
                {
                    arsort($office_counts);
                    reset($office_counts);
                    $office_id = key($office_counts);
                }
            }

            if ( !empty($office_id) )
            {
                $office_name = get_the_title($office_id);
                $office_email_address = get_post_meta( $office_id, '_office_email_address_sales', TRUE );
            }

            $body = str_replace("[office_name]", $office_name, $body);
            $body = str_replace("[office_email_address]", $office_email_address, $body);

            $body = str_replace("[negotiator_name]", $current_user->display_name, $body);
            $body = str_replace("[negotiator_email_address]", $current_user->user_email, $body);

            // wrap the content with the email template and then add styles
            $message = apply_filters( 'propertyhive_mail_content', PH()->email->style_inline( PH()->email->wrap_message( $body ) ) );

            // print the preview email
            echo $message;
            exit;
        }
    }
}

return new PH_Admin();