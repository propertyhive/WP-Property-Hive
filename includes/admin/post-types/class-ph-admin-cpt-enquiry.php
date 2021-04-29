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

        // Admin notices
        add_action( 'admin_notices', array( $this, 'enquiry_admin_notices') );

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
     * Output admin notices relating to enquiry
     */
    public function enquiry_admin_notices()
    {
        $screen = get_current_screen();

        if ( $screen->id == 'enquiry' && isset($_GET['post']) && get_post_type($_GET['post']) == 'enquiry' )
        {
            $enquiry = new PH_Enquiry((int)$_GET['post']);

            // Get associated property_id(s), from either meta_key
            $property_ids = get_post_meta( $enquiry->id, '_property_id' );
            if ( empty($property_ids) )
            {
                $property_ids = get_post_meta( $enquiry->id, 'property_id' );
            }

            // If the enquiry has a property associated
            if ( !empty($property_ids) )
            {
                if ( !is_array($property_ids) )
                {
                    $property_ids = array( $property_ids );
                }

                $contact_ids = array();
                $contact_id = get_post_meta( $enquiry->id, '_contact_id', true );
                if ( !empty( $contact_id ) )
                {
                    $contact_ids[] = $contact_id;
                }
                else
                {
                    // The enquiry doesn't have a contact_id meta_key, so check for contacts with the same email address

                    // Get enquiry email address, from either meta_key
                    $email_address = get_post_meta( $enquiry->id, 'email_address', true );
                    if ( empty($email_address) )
                    {
                        $email_address = get_post_meta( $enquiry->id, 'email', true );
                    }

                    if ( !empty($email_address) )
                    {
                        // Get all contact with this email address
                        $args = array(
                            'post_type'   => 'contact',
                            'post_status' => 'any',
                            'nopaging'    => true,
                            'fields'      => 'ids',
                            'meta_query'  => array(
                                array(
                                    'key' => '_email_address',
                                    'value' => $email_address,
                                )
                            )
                        );
                        $contacts_query = new WP_Query( $args );
                        $contact_ids = $contacts_query->posts;
                        wp_reset_postdata();
                    }
                }

                if ( count($contact_ids) > 0 )
                {
                    // Get any viewings for the propert(y/ies) and contact(s) on this enquiry
                    $args = array(
                        'post_type'   => 'viewing',
                        'nopaging'    => true,
                        'fields'      => 'ids',
                        'post_status' => 'publish',
                        'meta_query'  => array(
                            array(
                                'key'     => '_property_id',
                                'value'   => $property_ids,
                                'compare' => 'IN',
                            ),
                            array(
                                'key'     => '_applicant_contact_id',
                                'value'   => $contact_ids,
                                'compare' => 'IN',
                            ),
                        ),
                    );
                    $viewings_query = new WP_Query( $args );
                    $viewing_ids = $viewings_query->posts;
                    wp_reset_postdata();

                    if ( count($viewing_ids) > 0 )
                    {
                        if ( count($viewing_ids) == 1 )
                        {
                            $enquiry_text = 'is an existing viewing';
                        }
                        else
                        {
                            $enquiry_text = 'are ' . count($viewing_ids) . ' existing viewings';
                        }

                        $message = __( 'There ' . $enquiry_text . ' for this applicant at this property.', 'propertyhive' );
                        foreach( $viewing_ids as $viewing_id )
                        {
                            $message .= '<br><a href="' . get_edit_post_link( $viewing_id ) . '">Edit Viewing</a>';
                        }
                        echo "<div class=\"notice notice-info\"> <p>$message</p></div>";
                    }
                }
            }
        }
    }
    
    /**
     * Check if we're editing or adding a is_editing_enquiry
     * @return boolean
     */
    private function is_editing_enquiry() {
        if ( ! empty( $_GET['post_type'] ) && 'is_editing_enquiry' == $_GET['post_type'] ) {
            return true;
        }
        if ( ! empty( $_GET['post'] ) && 'is_editing_enquiry' == get_post_type( (int)$_GET['post'] ) ) {
            return true;
        }
        if ( ! empty( $_REQUEST['post_id'] ) && 'is_editing_enquiry' == get_post_type( (int)$_REQUEST['post_id'] ) ) {
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

                $sources = array(
                    'office' => __( 'Office', 'propertyhive' ),
                    'website' => __( 'Website', 'propertyhive' )
                );

                $sources = apply_filters( 'propertyhive_enquiry_sources', $sources );

                echo ( ( isset($sources[$the_enquiry->source]) ) ? $sources[$the_enquiry->source] : $the_enquiry->source );
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