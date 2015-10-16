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
        add_action( 'current_screen', array( $this, 'disable_propertyhive_meta_box_dragging' ) );
        add_action( 'current_screen', array( $this, 'remove_propertyhive_meta_boxes_from_screen_options' ) );
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
            /*include( 'class-ph-admin-welcome.php' );
            include( 'class-ph-admin-notices.php' );*/
            include( 'class-ph-admin-assets.php' );
            /*include( 'class-ph-admin-permalink-settings.php' );
            include( 'class-ph-admin-editor.php' );*/

            // Help
            //if ( apply_filters( 'propertyhive_enable_admin_help_tab', true ) )
                //include( 'class-ph-admin-help.php' );
        }

        // Importers
        //if ( defined( 'WP_LOAD_IMPORTERS' ) )
            //include( 'class-ph-admin-importers.php' );
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
    
    /**
     * Include admin files conditionally
     */
    public function conditonal_includes() {
        $screen = get_current_screen();

        switch ( $screen->id ) {
            case 'dashboard' :
                //include( 'class-ph-admin-dashboard.php' );
            break;
        }
    }
}

return new PH_Admin();