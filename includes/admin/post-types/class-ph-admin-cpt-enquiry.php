<?php
/**
 * Admin functions for the enquiry post type
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Post Types
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'PH_Admin_CPT' ) ) {
    include( 'class-ph-admin-cpt.php' );
}

if ( ! class_exists( 'PH_Admin_CPT_Enquiry' ) ) :

/**
 * PH_Admin_CPT_Enquiry Class
 */
class PH_Admin_CPT_Enquiry extends PH_Admin_CPT {

    /**
     * Constructor
     */
    public function __construct() {
        $this->type = 'enquiry';

        // Post title fields
        add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

        // Featured image text
        //add_filter( 'gettext', array( $this, 'featured_image_gettext' ) );
        //add_filter( 'media_view_strings', array( $this, 'media_view_strings' ), 10, 2 );

        // Visibility option
        //add_action( 'post_submitbox_misc_actions', array( $this, 'property_data_visibility' ) );

        // Before data updates
        add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
        add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) );

        // Admin Columns
        add_filter( 'manage_edit-enquiry_columns', array( $this, 'edit_columns' ) );
        add_action( 'manage_enquiry_posts_custom_column', array( $this, 'custom_columns' ), 2 );

        // Bulk / quick edit
        add_filter( 'bulk_actions-edit-enquiry', array( $this, 'remove_bulk_actions') );
        
        // Call PH_Admin_CPT constructor
        parent::__construct();
    }
    
    /**
     * Check if we're editing or adding a is_editing_enquiry
     * @return boolean
     */
    private function is_editing_enquiry() {
        if ( ! empty( $_GET['post_type'] ) && 'is_editing_enquiry' == $_GET['post_type'] ) {
            return true;
        }
        if ( ! empty( $_GET['post'] ) && 'is_editing_enquiry' == get_post_type( $_GET['post'] ) ) {
            return true;
        }
        if ( ! empty( $_REQUEST['post_id'] ) && 'is_editing_enquiry' == get_post_type( $_REQUEST['post_id'] ) ) {
            return true;
        }
        return false;
    }
    
    /**
     * Change title boxes in admin.
     * @param  string $text
     * @param  object $post
     * @return string
     */
    public function enter_title_here( $text, $post ) {
        if ( is_admin() && $post->post_type == 'enquiry' ) {
            return __( 'Enter Enquiry Subject', 'propertyhive' );
        }

        return $text;
    }
    
    /**
     * Some functions, like the term recount, require the visibility to be set prior. Lets save that here.
     *
     * @param int $post_id
     */
    public function pre_post_update( $post_id ) {

    }

    /**
     * Forces certain product data based on the product's type, e.g. grouped products cannot have a parent.
     *
     * @param array $data
     * @return array
     */
    public function wp_insert_post_data( $data ) {
        

        return $data;
    }

    /**
     * Change the columns shown in admin.
     */
    public function edit_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        unset( $existing_columns['title'], $existing_columns['comments'] );

        $columns = array();
        $columns['cb'] = '<input type="checkbox" />';
        
        $columns['subject'] = __( 'Subject', 'propertyhive' );
        
        $columns['status'] = __( 'Status', 'propertyhive' );

        $columns['source'] = __( 'Source', 'propertyhive' );
        
        $columns['negotiator'] = __( 'Negotiator', 'propertyhive' );
        
        $columns['office'] = __( 'Office', 'propertyhive' );

        return array_merge( $columns, $existing_columns );
    }

    /**
     * Define our custom columns shown in admin.
     * @param  string $column
     */
    public function custom_columns( $column ) {
        global $post, $propertyhive, $the_enquiry;

        if ( empty( $the_enquiry ) || $the_enquiry->ID != $post->ID ) 
        {
            $the_enquiry = new PH_Enquiry( $post->ID );
        }

        switch ( $column ) {
            case 'subject' :
                $edit_link        = get_edit_post_link( $post->ID );
                $title = _draft_or_post_title();
                
                echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a></strong>';
                
                break;
            case 'status' :
                echo $the_enquiry->status;
                break;
            case 'source' :
                echo $the_enquiry->source;
                break;
            case 'negotiator' :
                if ($the_enquiry->_negotiator_id == '' || $the_enquiry->_negotiator_id == 0)
                {
                    echo '<em>-- ' . __( 'Unassigned', 'propertyhive' ) . ' --</em>';
                }
                else
                {
                    $userdata = get_userdata( $the_enquiry->_negotiator_id );
                    if ( $userdata !== FALSE )
                    {
                        echo $userdata->display_name;
                    }
                    else
                    {
                        echo '<em>Unknown user</em>';
                    }
                }
                break;
            case 'office' :
                echo get_the_title( $the_enquiry->_office_id );
                break;
            default :
                break;
        }
    }

    /**
     * Remove bulk edit option
     * @param  array $actions
     */
    public function remove_bulk_actions( $actions ) {
        unset( $actions['edit'] );
        return $actions;
    }
    
}

endif;

return new PH_Admin_CPT_Enquiry();