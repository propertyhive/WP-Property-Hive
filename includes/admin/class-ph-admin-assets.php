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
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
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
            
            wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), PH_VERSION );
            //wp_enqueue_style( 'wp-color-picker' );
        }
        
        if ( in_array( $screen->id, array( 'property', 'contact' ) ) )
        {
            wp_enqueue_style( 'chosen', PH()->plugin_url() . '/assets/css/chosen.css', array(), PH_VERSION );
        }

        /*if ( in_array( $screen->id, array( 'dashboard' ) ) ) {
            wp_enqueue_style( 'propertyhive_admin_dashboard_styles', PH()->plugin_url() . '/assets/css/dashboard.css', array(), PH_VERSION );
        }*/

        do_action( 'propertyhive_admin_css' );
    }


    /**
     * Enqueue scripts
     */
    public function admin_scripts() {
        global $wp_query, $post;

        $screen       = get_current_screen();
        $ph_screen_id = sanitize_title( __( 'PropertyHive', 'propertyhive' ) );
        $suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Register scripts
        //wp_register_script( 'propertyhive_admin', PH()->plugin_url() . '/assets/js/admin/propertyhive_admin' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), PH_VERSION );

        //wp_register_script( 'jquery-blockui', PH()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.66', true );

        //wp_register_script( 'jquery-tiptip', PH()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), PH_VERSION, true );

        //wp_register_script( 'accounting', PH()->plugin_url() . '/assets/js/admin/accounting' . $suffix . '.js', array( 'jquery' ), '0.3.2' );

        //wp_register_script( 'round', PH()->plugin_url() . '/assets/js/admin/round' . $suffix . '.js', array( 'jquery' ), PH_VERSION );

        wp_register_script( 'propertyhive_admin_meta_boxes', PH()->plugin_url() . '/assets/js/admin/meta-boxes' . /*$suffix .*/ '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable' ), PH_VERSION );

        wp_register_script( 'propertyhive_admin_settings', PH()->plugin_url() . '/assets/js/admin/settings' . /*$suffix .*/ '.js', array( 'jquery' ), PH_VERSION );

        wp_register_script( 'ajax-chosen', PH()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . /*$suffix .*/ '.js', array('jquery', 'chosen'), PH_VERSION );

        wp_register_script( 'chosen', PH()->plugin_url() . '/assets/js/chosen/chosen.jquery' . /*$suffix .*/ '.js', array('jquery'), PH_VERSION );

        // Accounting
        //$params = array(
        //    'mon_decimal_point' => get_option( 'propertyhive_price_decimal_sep' )
        //);

        //wp_localize_script( 'accounting', 'accounting_params', $params );

        // PropertyHive admin pages
        if ( in_array( $screen->id, ph_get_screen_ids() ) ) {

            /*wp_enqueue_script( 'propertyhive_admin' );
            wp_enqueue_script( 'iris' );*/
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'chosen' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            
            wp_register_script('googlemaps', 'http://maps.googleapis.com/maps/api/js?' /*. $locale . '&key=' . GOOGLE_MAPS_V3_API_KEY*/ . '&sensor=false', false, '3');
            wp_enqueue_script('googlemaps');
            
            /*$locale  = localeconv();
            $decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

            $params = array(
                'i18n_decimal_error'     => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'propertyhive' ), $decimal ),
                'i18n_mon_decimal_error' => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'propertyhive' ), get_option( 'propertyhive_price_decimal_sep' ) ),
                'decimal_point'          => $decimal,
                'mon_decimal_point'      => get_option( 'propertyhive_price_decimal_sep' )
            );

            wp_localize_script( 'propertyhive_admin', 'propertyhive_admin', $params );*/
        }

        // Edit product category pages
        if ( in_array( $screen->id, array( 'property', 'contact' ) ) )
        {
            wp_enqueue_media();
            wp_enqueue_script( 'propertyhive_admin_meta_boxes' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_script( 'ajax-chosen' );
            wp_enqueue_script( 'chosen' );
            
            $params = array(
                'plugin_url'                    => PH()->plugin_url(),
                'ajax_url'                      => admin_url('admin-ajax.php'),
                'post_id'                       => isset( $post->ID ) ? $post->ID : '',
                'add_note_nonce'                => wp_create_nonce("add-note"),
                'delete_note_nonce'             => wp_create_nonce("delete-note"),
            );
            wp_localize_script( 'propertyhive_admin_meta_boxes', 'propertyhive_admin_meta_boxes', $params );

            if ( is_rtl() ) 
            {
                wp_enqueue_script( 'chosen-rtl', PH()->plugin_url() . '/assets/js/chosen/chosen-rtl' . /*$suffix .*/ '.js', array( 'jquery' ), PH_VERSION, true );
            }
        }
        
        if ( in_array( $screen->id, array( 'contact' ) ) )
        {
            wp_enqueue_script( 'jquery-ui-dialog' );

            add_thickbox();
        }
        
        if ( in_array( $screen->id, array( 'property-hive_page_ph-settings' ) ) )
        {
            wp_enqueue_script( 'propertyhive_admin_settings' );
        
            $params = array(
                'confirm_not_selected_warning'          => __( 'Please check the box to confirm that you are happy to remove this term', 'propertyhive' )
            );
            wp_localize_script( 'propertyhive_admin_settings', 'propertyhive_admin_settings', $params );
        }
    
    }

}

endif;

return new PH_Admin_Assets();