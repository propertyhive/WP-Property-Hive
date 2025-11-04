<?php
/**
 * Load assets.
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Assets' ) ) :

/**
 * PH_Admin_Assets Class
 */
class PH_Admin_Assets {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 5 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 5 );
    }

    /**
     * Enqueue styles
     */
    public function admin_styles() {
        global $wp_scripts;

        // Sitewide menu CSS
        //wp_enqueue_style( 'propertyhive_admin_menu_styles', PH()->plugin_url() . '/assets/css/menu.css', array(), PH_VERSION );

        $screen = get_current_screen();

        if ( in_array( $screen->id, ph_get_screen_ids() ) ) {

            $jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

            // Admin styles for PH pages only
            wp_enqueue_style( 'propertyhive_admin_styles', PH()->plugin_url() . '/assets/css/admin.css', array(), PH_VERSION );
            
            wp_enqueue_style( 'font_awesome', PH()->plugin_url() . '/assets/css/font-awesome.min.css', array(), PH_VERSION );
            
            wp_enqueue_style( 'jquery-ui-style', PH()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.css', array(), PH_VERSION );
            wp_enqueue_style( 'wp-color-picker' );

            wp_enqueue_style( 'chosen', PH()->plugin_url() . '/assets/css/chosen.css', array(), PH_VERSION );

            wp_enqueue_style( 'multiselect', PH()->plugin_url() . '/assets/css/jquery.multiselect.css', array(), '2.4.18' );

            if ( apply_filters('propertyhive_disable_notes_mention', false) === false ) 
            {
                wp_enqueue_style( 'tinymce-mention', PH()->plugin_url() . '/assets/js/tinymce-mention-plugin/autocomplete.css', array(), PH_VERSION );
            }

            if ( get_option('propertyhive_maps_provider') == 'mapbox' )
            {
                wp_enqueue_style('mapbox', PH()->plugin_url() . '/assets/js/mapbox/mapbox-gl.css', array(), '3.8.0' );
            }
        }

        if ( in_array( $screen->id, array( 'property' ) ) )
        {
            if ( isset($_GET['tutorial']) && sanitize_text_field($_GET['tutorial']) == 'yes' )
            {
                wp_register_style( 'tour-css', PH()->plugin_url() .  '/assets/css/tours/style.css', array(), '1.0.1' );
                wp_register_style( 'driver-css', PH()->plugin_url() . '/assets/css/tours/driver-js.css', array(), '1.0.1' );
                wp_enqueue_style( 'tour-css' );
                wp_enqueue_style( 'driver-css' );
            }
        }

	    if ( in_array( $screen->id, array( 'edit-contact', 'edit-enquiry', 'edit-appraisal', 'edit-viewing', 'edit-offer', 'edit-sale', 'edit-key_date' ) ) )
	    {
		    wp_enqueue_style( 'daterangepicker.css', '//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css' );
	    }

	    if ( in_array( $screen->id, array( 'edit-key_date') ) )
	    {
		    wp_enqueue_style( 'admin-hide-default-post-data.css', PH()->plugin_url() . '/assets/css/admin-hide-default-post-data.css', PH_VERSION );
	    }

        if ( 
            get_option('propertyhive_module_disabled_viewings', '') != 'yes' &&
            in_array( $screen->id, array( 'property', 'contact' ) ) 
        )
	    {
		    wp_enqueue_style( 'propertyhive_fancybox_css', PH()->plugin_url() . '/assets/css/jquery.fancybox.css', array(), '3.5.7' );
	    }

        /*if ( in_array( $screen->id, array( 'dashboard' ) ) ) {
            wp_enqueue_style( 'propertyhive_admin_dashboard_styles', PH()->plugin_url() . '/assets/css/dashboard.css', array(), PH_VERSION );
        }*/

        if ( $screen->id === 'plugins' ) 
        {
            // Enqueue the CSS file
            wp_enqueue_style(
                'propertyhive_deactivate_survey',
                PH()->plugin_url() . '/assets/css/deactivate-survey.css',
                array(),
                '1.0.0'
            );
        }

        do_action( 'propertyhive_admin_css' );
    }


    /**
     * Enqueue scripts
     */
    public function admin_scripts() {
        global $wp_query, $post, $tabs;

        $screen       = get_current_screen();
        $ph_screen_id = sanitize_title( __( 'PropertyHive', 'propertyhive' ) );
        $suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Register scripts
        wp_register_script( 'propertyhive_dashboard', PH()->plugin_url() . '/assets/js/admin/dashboard' . /*$suffix .*/ '.js', array( 'jquery' ), PH_VERSION );

        wp_register_script( 'propertyhive_admin', PH()->plugin_url() . '/assets/js/admin/admin' . /*$suffix .*/ '.js', array( 'jquery', 'jquery-tiptip' ), PH_VERSION );

        wp_register_script( 'jquery-tiptip', PH()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . /*$suffix .*/ '.js', array( 'jquery' ), PH_VERSION, true );

        wp_register_script( 'propertyhive_admin_meta_boxes', PH()->plugin_url() . '/assets/js/admin/meta-boxes' . /*$suffix .*/ '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable' ), PH_VERSION );

        wp_register_script( 'propertyhive_admin_settings', PH()->plugin_url() . '/assets/js/admin/settings' . /*$suffix .*/ '.js', array( 'jquery', 'wp-color-picker' ), PH_VERSION );

        wp_register_script( 'propertyhive_admin_recently_viewed', PH()->plugin_url() . '/assets/js/admin/recently-viewed' . /*$suffix .*/ '.js', array( 'jquery' ), PH_VERSION );

        wp_register_script( 'ajax-chosen', PH()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . /*$suffix .*/ '.js', array('jquery', 'chosen'), PH_VERSION );

        wp_register_script( 'chosen', PH()->plugin_url() . '/assets/js/chosen/chosen.jquery' . /*$suffix .*/ '.js', array('jquery'), PH_VERSION );

        wp_register_script( 'multiselect', PH()->plugin_url() . '/assets/js/multiselect/jquery.multiselect' . /*$suffix .*/ '.js', array('jquery'), '2.4.18' );

        wp_register_script( 'flot', PH()->plugin_url() . '/assets/js/jquery-flot/jquery.flot' . $suffix . '.js', array( 'jquery' ), PH_VERSION );
        wp_register_script( 'flot-resize', PH()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.resize' . $suffix . '.js', array( 'jquery', 'flot' ), PH_VERSION );
        wp_register_script( 'flot-time', PH()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.time' . $suffix . '.js', array( 'jquery', 'flot' ), PH_VERSION );
        //wp_register_script( 'flot-pie', PH()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.pie' . $suffix . '.js', array( 'jquery', 'flot' ), PH_VERSION );
        //wp_register_script( 'flot-stack', PH()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.stack' . $suffix . '.js', array( 'jquery', 'flot' ), PH_VERSION );

        wp_enqueue_script( 'propertyhive_admin' );

        $params = array();

        // Add a bit better support for Gutenberg if enabled somehow (i.e. using Houzez) to load AJAX grids
        if ( 
            isset($post->ID) &&
            function_exists( 'use_block_editor_for_post_type' ) && 
            use_block_editor_for_post_type( get_post_type($post->ID) ) && 
            !isset( $_GET['classic-editor'] ) &&
            is_array($tabs) &&
            !empty($tabs)
        )
        {
            $ajax_actions = array();

            foreach ( $tabs as $key => $tab )
            {
                if ( isset($tab['ajax_actions']) && !empty($tab['ajax_actions']) )
                {
                    foreach ( $tab['ajax_actions'] as $ajax_action )
                    {
                        $ajax_actions[] = $ajax_action;
                    }
                }
            }

            if ( !empty($ajax_actions) )
            {
                $params = array(
                    'ajax_actions' => $ajax_actions,
                    'post_id' => $post->ID,
                );
                
            }
        }
        
        wp_localize_script( 'propertyhive_admin', 'propertyhive_admin', $params );

        $recently_viewed = get_user_meta( get_current_user_id(), '_propertyhive_recently_viewed', TRUE );
        if ( !is_array($recently_viewed) )
        {
            $recently_viewed = array();
        }
        
        $params = array(
            'recently_viewed' => $recently_viewed,
        );
        wp_localize_script( 'propertyhive_admin_recently_viewed', 'propertyhive_admin_recently_viewed', $params );

        wp_localize_script( 'multiselect', 'propertyhive_multiselect_params', apply_filters( 'propertyhive_multiselect_params', array(
            'search'    => false,
            'selected_text' => __( 'selected', 'propertyhive' ),
        ) ) );

        // PropertyHive admin pages
        if ( in_array( $screen->id, array( 'dashboard' ) ) )
        {
            wp_enqueue_script( 'propertyhive_dashboard' );

            $params = array(
                'ajax_url'                      => admin_url('admin-ajax.php'),
            );
            wp_localize_script( 'propertyhive_dashboard', 'propertyhive_dashboard', $params );
        }

	    if ( in_array( $screen->id, array( 'edit-contact', 'edit-enquiry', 'edit-appraisal', 'edit-viewing', 'edit-offer', 'edit-sale', 'edit-key_date' ) ) )
	    {
		    wp_enqueue_script( 'moment.js', '//cdn.jsdelivr.net/momentjs/latest/moment.min.js' );
		    wp_enqueue_script( 'daterangepicker.js', '//cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js' );
		    wp_enqueue_script( 'date_range_filter.js', PH()->plugin_url() . '/assets/js/admin/date_range_filter.js', array('jquery', 'moment.js', 'daterangepicker.js'), PH_VERSION );
	    }

	    if ( in_array( $screen->id, array( 'edit-key_date' ) ) )
	    {
		    wp_enqueue_script( 'inline-edit-key_date.js', PH()->plugin_url() . '/assets/js/admin/inline-edit-key_date.js', array('jquery'), PH_VERSION );
	    }

        if ( 
            get_option('propertyhive_module_disabled_viewings', '') != 'yes' &&
            in_array( $screen->id, array( 'property', 'contact' ) ) 
        )
        {
		    wp_enqueue_script( 'propertyhive_fancybox', PH()->plugin_url() . '/assets/js/fancybox/jquery.fancybox.js', array('jquery'), '3.5.7' );
	    }

        if ( in_array( $screen->id, array( 'property' ) ) )
        {
            if ( ! class_exists( '_WP_Editors', false ) ) 
            {
                require( ABSPATH . WPINC . '/class-wp-editor.php' );
            }
            add_action( 'admin_print_footer_scripts', array( '_WP_Editors', 'print_default_editor_scripts' ) );

            if ( isset($_GET['tutorial']) && sanitize_text_field($_GET['tutorial']) == 'yes' )
            {
                wp_enqueue_script( 'driver-js', PH()->plugin_url() . '/assets/js/tours/driver-js.js', array(), '1.0.1' );
                wp_register_script( 'tour', PH()->plugin_url() . '/assets/js/tours/tour.js', array( 'driver-js' ), '1.0.1' );
                wp_enqueue_script( 'tour' );

                $tours = [
                    'add-property' => [
                        [
                            'element' => '#title',
                            'popover' => [
                                'title' => __( 'Enter the Property\'s Display Address', 'propertyhive' ),
                                'description' => __( 'This is the title or address that will be shown publicly. Make it clear and appealing for your audience.', 'propertyhive' ),
                                'side' => 'bottom',
                            ],
                        ],
                        [
                            'element' => '#propertyhive_metabox_tabs',
                            'popover' => [
                                'title' => __( 'Explore the Property Record', 'propertyhive' ),
                                'description' => __( 'The property record is organized into sections for easy navigation. Use these tabs to switch between sections.<br><br>Go ahead, try clicking them now.', 'propertyhive' ),
                                'side' => 'bottom',
                                'onPrevClick' => 'revert_to_first_tab',
                                'onNextClick' => 'revert_to_first_tab',
                            ],
                        ],
                        [
                            'element' => '#propertyhive-property-address',
                            'popover' => [
                                'title' => __( 'Enter the Property Details', 'propertyhive' ),
                                'description' => __( 'Use these fields to input essential property information like address, price, bedrooms, and more. You can also upload images to showcase the property.', 'propertyhive' ),
                                'side' => 'top',
                                'onNextClick' => 'revert_to_third_tab',
                            ],
                        ],
                        [
                            'element' => '#\\_on_market',
                            'popover' => [
                                'title' => __( 'Put it on the Market', 'propertyhive' ),
                                'description' => __( 'For a property to show on your website it will need to be set to \'On Market\' under the \'Marketing\' tab.', 'propertyhive' ),
                                'side' => 'top',
                                'onPrevClick' => 'revert_to_first_tab',
                                'onNextClick' => 'revert_to_first_tab',
                            ],
                        ],
                        [
                            'element' => '#submitdiv',
                            'popover' => [
                                'title' => __( 'Publish your Property', 'propertyhive' ),
                                'description' => __( 'When you\'re done and are ready to save your changes, simply click \'Publish\'', 'propertyhive' ),
                                'side' => 'left',
                                'onPrevClick' => 'revert_to_third_tab',
                            ],
                        ],
                    ],
                ];
                wp_localize_script(
                    'tour',
                    'tour_plugin',
                    array(
                        'tours'    => $tours,
                    )
                );
            }
        }

        if ( in_array( $screen->id, ph_get_screen_ids() ) ) 
        {
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'chosen' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'wp-tinymce' );
            
            if ( get_option('propertyhive_maps_provider') == 'mapbox' )
            {
                wp_register_script('mapbox', PH()->plugin_url() . '/assets/js/mapbox/mapbox-gl.js', false, '3.8.0');
                wp_enqueue_script('mapbox');

                if ( get_option('propertyhive_geocoding_provider') == '' )
                {
                    $api_key = get_option('propertyhive_google_maps_geocoding_api_key');
                    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
                    wp_enqueue_script('googlemaps');
                }
            }
            elseif ( get_option('propertyhive_maps_provider') == 'osm' )
            {
                wp_register_style('leaflet', PH()->plugin_url() . '/assets/js/leaflet/leaflet.css', array(), '1.9.4');
                wp_enqueue_style('leaflet');

                wp_register_script('leaflet', PH()->plugin_url() . '/assets/js/leaflet/leaflet.js', array(), '1.9.4', false);
                wp_enqueue_script('leaflet');

                if ( get_option('propertyhive_geocoding_provider') == '' )
                {
                    $api_key = get_option('propertyhive_google_maps_geocoding_api_key');
                    wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
                    wp_enqueue_script('googlemaps');
                }
            }
            else
            {
                $api_key = get_option('propertyhive_google_maps_api_key');
                wp_register_script('googlemaps', '//maps.googleapis.com/maps/api/js?' . ( ( $api_key != '' && $api_key !== FALSE ) ? 'key=' . $api_key : '' ), false, '3');
                wp_enqueue_script('googlemaps');
            }

            wp_enqueue_media();
            wp_enqueue_script( 'propertyhive_admin_meta_boxes' );
            wp_enqueue_script( 'propertyhive_admin_recently_viewed' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'chosen' );

            $params = array(
                'plugin_url'                    => PH()->plugin_url(),
                'ajax_url'                      => admin_url('admin-ajax.php'),
                'post_id'                       => isset( $post->ID ) ? $post->ID : '',
                'get_notes_nonce'                => wp_create_nonce("get-notes"),
                'pin_note_nonce'                => wp_create_nonce("pin-note"),
                'add_note_nonce'                => wp_create_nonce("add-note"),
                'delete_note_nonce'             => wp_create_nonce("delete-note"),
                'viewing_details_meta_nonce'    => wp_create_nonce( 'viewing-details-meta-box' ),
                'viewing_actions_nonce'         => wp_create_nonce( 'viewing-actions' ),
                'save_key_date_nonce'         => wp_create_nonce( 'save-key-date' ),
                'delete_key_date_nonce'         => wp_create_nonce( 'delete-key-date' ),
                'check_duplicate_reference_number_nonce' => wp_create_nonce("check-duplicate-reference-number"),
                'enable_description_editor'    => apply_filters('propertyhive_enable_description_editor', false),
                'leasehold_tenures'             => apply_filters('propertyhive_leasehold_tenure_names', array( 'leasehold', 'share of freehold' ) ),
                'disable_notes_mention'         => apply_filters('propertyhive_disable_notes_mention', false),
            );
            wp_localize_script( 'propertyhive_admin_meta_boxes', 'propertyhive_admin_meta_boxes', $params );

            if ( is_rtl() ) 
            {
                wp_enqueue_script( 'chosen-rtl', PH()->plugin_url() . '/assets/js/chosen/chosen-rtl' . /*$suffix .*/ '.js', array( 'jquery' ), PH_VERSION, true );
            }
        }

        if ( in_array( $screen->id, array('profile', 'user', 'user-edit') ) ) 
        {
            wp_enqueue_media();
        }
        
        if ( strpos($screen->id, 'page_ph-settings') !== FALSE )
        {
            wp_enqueue_script( 'propertyhive_admin_settings' );
        
            $params = array(
                'confirm_not_selected_warning'              => __( 'Please check the box to confirm that you are happy to remove this term', 'propertyhive' ),
                'no_departments_selected_warning'           => __( 'Please select at least one active department', 'propertyhive' ),
                'primary_department_not_active_warning'     => __( 'The chosen primary department has not been selected as active', 'propertyhive' ),
                'no_countries_selected'                     => __( 'Please select which countries you operate in', 'propertyhive' ),
                'default_country_not_in_selected'           => __( 'The default country hasn\'t been selected as a country you operate in', 'propertyhive' ),
                'admin_url'                                 => admin_url(),
                'taxonomy_section'                          => ( ( isset($_GET['section']) ) ? sanitize_text_field($_GET['section']) : '' ),
                'ajax_nonce'                                => wp_create_nonce("updates"),
                'features_settings_url'                     => admin_url('admin.php?page=ph-settings&tab=features'),
            );
            if ( isset($_GET['tab']) && ph_clean($_GET['tab']) == 'licensekey' )
            {
                $params['valid_pro_license_key'] = PH()->license->is_valid_pro_license_key(true);
            }
            else
            {
                $params['valid_pro_license_key'] = PH()->license->is_valid_pro_license_key();
            }
            wp_localize_script( 'propertyhive_admin_settings', 'propertyhive_admin_settings', $params );
        }
        
        // Reports Pages
        if ( strpos($screen->id, 'page_ph-reports') !== FALSE || in_array( $screen->id, array( 'property' ) ) )
        {
           //wp_register_script( 'ph-reports', PH()->plugin_url() . '/assets/js/admin/reports' . /*$suffix .*/ '.js', array( 'jquery', 'jquery-ui-datepicker' ), PH_VERSION );

            //wp_enqueue_script( 'ph-reports' );
            wp_enqueue_script( 'flot' );
            wp_enqueue_script( 'flot-resize' );
            wp_enqueue_script( 'flot-time' );
            //wp_enqueue_script( 'flot-pie' );
            //wp_enqueue_script( 'flot-stack' );
        }

        if ( $screen->id === 'plugins' ) 
        {
            // Enqueue the JavaScript file
            wp_enqueue_script(
                'propertyhive_deactivate_survey',
                PH()->plugin_url() . '/assets/js/deactivate-survey.js',
                array('jquery'), // Depend on jQuery
                '1.0.0',
                true // Load in footer
            );

            wp_localize_script('propertyhive_deactivate_survey', 'deactivation_survey', array(
                'nonce'            => wp_create_nonce('deactivate-survey'),

                'modalTitle'      => __('If you have a moment, please let us know why you are deactivating:', 'propertyhive'),
                'modalHeader'     => __('Quick Feedback', 'propertyhive'),
                'skipDeactivate'  => __('Skip & Deactivate', 'propertyhive'),
                'cancel'          => __('Cancel', 'propertyhive'),
                'deactivate'      => __('Submit & Deactivate', 'propertyhive'),

                'brokeSite'       => __('The plugin broke my site', 'propertyhive'),
                'confusing'       => __('The plugin is confusing', 'propertyhive'),
                'inadequateSupport' => __('The support/documentation is inadequate', 'propertyhive'),
                'poorPerformance' => __('The plugin is too slow/affects performance', 'propertyhive'),
                'notNeeded'       => __('I no longer need the plugin', 'propertyhive'),
                'betterPlugin'    => __('I found a better plugin', 'propertyhive'),
                'temporary'       => __('It\'s a temporary deactivation', 'propertyhive'),
                'other'           => __('Other', 'propertyhive'),

                'brokeSiteLabel' => __('Oh no! Can you tell us more? (optional)', 'propertyhive'),
                'betterPluginLabel' => __('What\'s the plugin\'s name? (optional)', 'propertyhive'),
                'otherLabel'      => __('Additional feedback or comments: (optional)', 'propertyhive'),

                'anonymous'       => __('Anonymous feedback', 'propertyhive'),
            ));
        }
    }

}

endif;

return new PH_Admin_Assets();