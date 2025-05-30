<?php
/**
 * PropertyHive Meta Boxes
 *
 * Sets up the write panels used by products and orders (custom post types)
 *
 * @author 		BIOSTA::
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Admin_Meta_Boxes' ) )
{

/**
 * PH_Admin_Meta_Boxes
 */
class PH_Admin_Meta_Boxes {

	private static $meta_box_errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'rename_meta_boxes' ), 20 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

        add_action( 'post_submitbox_start', array( $this, 'add_archive_link_to_post_submitbox' ) );

		// Save Property Meta Boxes
		add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Address::save', 10, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Owner::save', 12, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Record_Details::save', 13, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Coordinates::save', 70, 2 );
        
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Department::save', 15, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Residential_Details::save', 20, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Residential_Lettings_Details::save', 25, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Residential_Sales_Details::save', 30, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Commercial_Details::save', 30, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Material_Information::save', 32, 2 );
        
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Marketing::save', 35, 2 );
        
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Rooms::save', 40, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Features::save', 45, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Description::save', 45, 2 );
        
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Photos::save', 50, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Floorplans::save', 55, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Brochures::save', 65, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Epcs::save', 70, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Virtual_Tours::save', 75, 2 );
        
        // Save Contact Meta Boxes
        if ( isset($_POST['_contact_type_new']) )
        {
            add_action( 'propertyhive_process_contact_meta', 'PH_Meta_Box_Contact_New_Relationship::save', 1, 2 );
        }
        add_action( 'propertyhive_process_contact_meta', 'PH_Meta_Box_Contact_Correspondence_Address::save', 10, 2 );
        add_action( 'propertyhive_process_contact_meta', 'PH_Meta_Box_Contact_Contact_Details::save', 15, 2 );
        add_action( 'propertyhive_process_contact_meta', 'PH_Meta_Box_Contact_Solicitor::save', 20, 2 );
        if ( !isset($_POST['_contact_type_new']) )
        {
            add_action( 'propertyhive_process_contact_meta', 'PH_Meta_Box_Contact_Relationships::save', 25, 2 );
        }
        
        // Save Enquiry Meta Boxes
        if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
        {
            add_action( 'propertyhive_process_enquiry_meta', 'PH_Meta_Box_Enquiry_Record_Details::save', 10, 2 );
            add_action( 'propertyhive_process_enquiry_meta', 'PH_Meta_Box_Enquiry_Details::save', 15, 2 );
        }

        // Save Appraisal Meta Boxes
        add_action( 'propertyhive_process_appraisal_meta', 'PH_Meta_Box_Appraisal_Details::save', 10, 2 );
        add_action( 'propertyhive_process_appraisal_meta', 'PH_Meta_Box_Appraisal_Event::save', 15, 2 );
        add_action( 'propertyhive_process_appraisal_meta', 'PH_Meta_Box_Appraisal_Property_Owner::save', 20, 2 );
        add_action( 'propertyhive_process_appraisal_meta', 'PH_Meta_Box_Appraisal_Property::save', 25, 2 );

        // Save Viewing Meta Boxes
        add_action( 'propertyhive_process_viewing_meta', 'PH_Meta_Box_Viewing_Details::save', 10, 2 );
        add_action( 'propertyhive_process_viewing_meta', 'PH_Meta_Box_Viewing_Event::save', 15, 2 );
        add_action( 'propertyhive_process_viewing_meta', 'PH_Meta_Box_Viewing_Applicant::save', 20, 2 );
        add_action( 'propertyhive_process_viewing_meta', 'PH_Meta_Box_Viewing_Property::save', 25, 2 );

        // Save Offer Meta Boxes
        add_action( 'propertyhive_process_offer_meta', 'PH_Meta_Box_Offer_Details::save', 10, 2 );
        add_action( 'propertyhive_process_offer_meta', 'PH_Meta_Box_Offer_Applicant::save', 15, 2 );
        add_action( 'propertyhive_process_offer_meta', 'PH_Meta_Box_Offer_Applicant_Solicitor::save', 20, 2 );
        add_action( 'propertyhive_process_offer_meta', 'PH_Meta_Box_Offer_Property::save', 25, 2 );
        add_action( 'propertyhive_process_offer_meta', 'PH_Meta_Box_Offer_Property_Owner_Solicitor::save', 30, 2 );

        // Save Sale Meta Boxes
        add_action( 'propertyhive_process_sale_meta', 'PH_Meta_Box_Sale_Details::save', 10, 2 );
        add_action( 'propertyhive_process_sale_meta', 'PH_Meta_Box_Sale_Applicant_Solicitor::save', 15, 2 );
        add_action( 'propertyhive_process_sale_meta', 'PH_Meta_Box_Sale_Property_Owner_Solicitor::save', 20, 2 );

        // Save Tenancy Meta Boxes
        add_action( 'propertyhive_process_tenancy_meta', 'PH_Meta_Box_Tenancy_Details::save', 10, 2 );
        add_action( 'propertyhive_process_tenancy_meta', 'PH_Meta_Box_Tenancy_Applicant::save', 15, 2 );
        add_action( 'propertyhive_process_tenancy_meta', 'PH_Meta_Box_Tenancy_Property::save', 20, 2 );
        add_action( 'propertyhive_process_tenancy_meta', 'PH_Meta_Box_Tenancy_Deposit_Scheme::save', 25, 2 );
        add_action( 'propertyhive_process_tenancy_meta', 'PH_Meta_Box_Tenancy_Meter_Readings::save', 25, 2 );
        add_action( 'propertyhive_process_tenancy_meta', 'PH_Meta_Box_Tenancy_Management::save', 30, 2 );

		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );

        add_filter( 'redirect_post_location', array( $this, 'redirect_to_tab' ), 10, 2 );

        $this->check_contact_create_relationship();
        $this->check_contact_delete_relationship();
        $this->check_remove_solicitor();
        $this->check_create_offer();
        $this->check_create_sale();
        $this->check_create_tenancy();
	}

    function add_archive_link_to_post_submitbox() 
    {
        global $post, $current_screen;

        if ( isset($current_screen) && $current_screen->base === 'post' && $current_screen->action === 'add' )
            return;

        // Check if the post status is not 'archive'
        $post_types = array('property', 'contact', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy', 'key_date');
        $post_types = apply_filters( 'propertyhive_post_types_with_archive', $post_types );

        if ( in_array($post->post_type, $post_types) ) 
        {
            if ( $post->post_status != 'archive' )
            {
                echo '<div id="archive-action" style="float:left; line-height:2.30769231">';
                    $archive_url = wp_nonce_url(admin_url('post.php?post=' . $post->ID . '&action=archive_single'), 'archive-post_' . $post->ID);
                    echo '<a href="' . esc_url($archive_url) . '" id="archive-post" class="submitdelete deletion">' . esc_html( __('Archive', 'propertyhive') ) . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
                echo '</div>';
            }
            else
            {
                // This is an archived post
                echo '<div id="archive-action" style="float:left; line-height:2.30769231">';
                    $archive_url = wp_nonce_url(admin_url('post.php?post=' . $post->ID . '&action=unarchive_single'), 'unarchive-post_' . $post->ID);
                    echo '<a href="' . esc_url($archive_url) . '" id="unarchive-post" class="submitdelete deletion">' . esc_html( __('Unarchive', 'propertyhive') ) . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
                echo '</div>';
            }
        }
    }

    public function redirect_to_tab( $url, $post_id )
    {
        if ( isset($_POST['propertyhive_selected_metabox_tab']) )
        {
            $url .= '#' . $_POST['propertyhive_selected_metabox_tab'];
        }

        return $url;
    }

    public function check_contact_create_relationship()
    {
        if ( isset($_GET['add_applicant_relationship']) && wp_verify_nonce($_GET['add_applicant_relationship'], '1') && isset($_GET['post']) ) 
        {
            // Need to add blank applicant
            if ( get_post_type((int)$_GET['post']) != 'contact' )
                return;

            $num_applicant_profiles = get_post_meta( (int)$_GET['post'], '_applicant_profiles', TRUE );
            if ( $num_applicant_profiles == '' )
            {
                $num_applicant_profiles = 0;
            }

            update_post_meta( (int)$_GET['post'], '_applicant_profile_' . $num_applicant_profiles, '' );
            update_post_meta( (int)$_GET['post'], '_applicant_profiles', $num_applicant_profiles + 1 );

            $existing_contact_types = get_post_meta( (int)$_GET['post'], '_contact_types', TRUE );
            if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
            {
                $existing_contact_types = array();
            }
            if ( !in_array( 'applicant', $existing_contact_types ) )
            {
                $existing_contact_types[] = 'applicant';
                update_post_meta( (int)$_GET['post'], '_contact_types', $existing_contact_types );
            }

            // Do redirect
            wp_redirect( admin_url( 'post.php?post=' . (int)$_GET['post'] . '&action=edit#propertyhive-contact-relationships' ) );
            exit();
        }

        if ( isset($_GET['add_third_party_relationship']) && wp_verify_nonce($_GET['add_third_party_relationship'], '1') && isset($_GET['post']) ) 
        {
            // Need to add blank third party relationship
            if ( get_post_type((int)$_GET['post']) != 'contact' )
                return;

            $existing_third_party_categories = get_post_meta( (int)$_GET['post'], '_third_party_categories', TRUE );
            if ( !is_array($existing_third_party_categories) )
            {
                $existing_third_party_categories = array();
            }
            $existing_third_party_categories[] = 0;
            update_post_meta( $_GET['post'], '_third_party_categories', $existing_third_party_categories );

            $existing_contact_types = get_post_meta( (int)$_GET['post'], '_contact_types', TRUE );
            if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
            {
                $existing_contact_types = array();
            }
            if ( !in_array( 'thirdparty', $existing_contact_types ) )
            {
                $existing_contact_types[] = 'thirdparty';
                update_post_meta( (int)$_GET['post'], '_contact_types', $existing_contact_types );
            }
        }
    }

    public function check_contact_delete_relationship()
    {
        if ( isset($_GET['delete_applicant_relationship']) && isset($_GET['post']) )
        {
            // Need to add blank applicant
            if ( get_post_type((int)$_GET['post']) != 'contact' )
                return;

            $num_applicant_profiles = get_post_meta( (int)$_GET['post'], '_applicant_profiles', TRUE );
            if ( $num_applicant_profiles == '' )
            {
                $num_applicant_profiles = 0;
            }

            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                if ( wp_verify_nonce($_GET['delete_applicant_relationship'], $i) ) 
                {
                    $deleting_applicant_profile = $i;

                    // We're deleting this one
                    delete_post_meta( (int)$_GET['post'], '_applicant_profile_' . $i );

                    // Now need to rename any that are higher than $deleting_applicant_profile
                    for ( $j = 0; $j < $num_applicant_profiles; ++$j )
                    {
                       if ( $j > $deleting_applicant_profile )
                        {
                            $this_applicant_profile = get_post_meta( (int)$_GET['post'], '_applicant_profile_' . $j, true );
                            update_post_meta( (int)$_GET['post'], '_applicant_profile_' . ($j - 1), $this_applicant_profile );
                            delete_post_meta( (int)$_GET['post'], '_applicant_profile_' . $j );
                        }
                    }

                    // remove from _contact_types if no more profiles left
                    if ( $num_applicant_profiles == 1 )
                    {
                        $existing_contact_types = get_post_meta( (int)$_GET['post'], '_contact_types', TRUE );
                        if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
                        {
                            $existing_contact_types = array();
                        }
                        if( ( $key = array_search('applicant', $existing_contact_types) ) !== false )
                        {
                            unset($existing_contact_types[$key]);
                        }
                        update_post_meta( (int)$_GET['post'], '_contact_types', $existing_contact_types );
                    }

                    update_post_meta( (int)$_GET['post'], '_applicant_profiles', $num_applicant_profiles - 1 );

                    // update applicant departments
                    $hot_applicant = '';
                    $applicant_departments = array();

                    $num_applicant_profiles = get_post_meta( (int)$_GET['post'], '_applicant_profiles', TRUE );
                    if ( $num_applicant_profiles == '' )
                    {
                        $num_applicant_profiles = 0;
                    }

                    for ( $j = 0; $j < $num_applicant_profiles; ++$j )
                    {
                        $applicant_profile = get_post_meta( (int)$_GET['post'], '_applicant_profile_' . $j, true );

                        if ( isset($applicant_profile['department']) && !empty($applicant_profile['department']) )
                        {
                            $applicant_departments[] = $applicant_profile['department'];
                        }

                        if ( isset($applicant_profile['grading']) && ph_clean($applicant_profile['grading']) == 'yes' )
                        {
                            $hot_applicant = 'yes';
                        }
                    }

                    update_post_meta( (int)$_GET['post'], '_hot_applicant', $hot_applicant );

                    if ( !empty($applicant_departments) )
                    {
                        $applicant_departments = array_filter($applicant_departments);
                        $applicant_departments = array_unique($applicant_departments);
                    }
                    update_post_meta( (int)$_GET['post'], '_applicant_departments', $applicant_departments );

                    // Do redirect
                    wp_redirect( admin_url( 'post.php?post=' . (int)$_GET['post'] . '&action=edit#propertyhive-contact-relationships' ) );
                    exit();
                }
            }
        }
    }

    public function check_remove_solicitor()
    {
        if ( isset($_GET['remove_contact_solicitor']) && isset($_GET['post']) )
        {
            if ( get_post_type((int)$_GET['post']) != 'contact' )
                return;

            update_post_meta( (int)$_GET['post'], '_contact_solicitor_contact_id', '' );
        }

        if ( isset($_GET['remove_property_owner_solicitor']) && isset($_GET['post']) )
        {
            if ( get_post_type((int)$_GET['post']) != 'offer' && get_post_type((int)$_GET['post']) != 'sale' )
                return;

            update_post_meta( (int)$_GET['post'], '_property_owner_solicitor_contact_id', '' );
        }

        if ( isset($_GET['remove_applicant_solicitor']) && isset($_GET['post']) )
        {
            if ( get_post_type((int)$_GET['post']) != 'offer' && get_post_type((int)$_GET['post']) != 'sale' )
                return;

            update_post_meta( (int)$_GET['post'], '_applicant_solicitor_contact_id', '' );
        }
    }

    public function check_create_offer()
    {
        if ( isset($_GET['create_offer']) && isset($_GET['post']) )
        {
            if ( get_post_type((int)$_GET['post']) != 'viewing')
                return;

            $viewing = new PH_Viewing((int)$_GET['post']);

            $viewing_applicant_ids = $viewing->get_applicant_ids();

            $offer_post = array(
              'post_title'    => '',
              'post_content'  => '',
              'post_type'  => 'offer',
              'post_status'   => 'publish',
              'comment_status'    => 'closed',
              'ping_status'    => 'closed',
            );
            
            // Insert the post into the database
            $offer_post_id = wp_insert_post( $offer_post );
            
            add_post_meta( $offer_post_id, '_status', 'pending' );
            add_post_meta( $offer_post_id, '_amount', '' );

            $applicant_solicitor_contact_id = '';
            foreach( $viewing_applicant_ids as $viewing_applicant_id )
            {
                add_post_meta( $offer_post_id, '_applicant_contact_id', $viewing_applicant_id );

                // If any applicant has a solicitor assigned, select them as the offer applicant solicitor
                if ( empty($applicant_solicitor_contact_id) )
                {
                    if ( !empty( get_post_meta( $viewing_applicant_id, '_contact_solicitor_contact_id', TRUE ) ) )
                    {
                        $applicant_solicitor_contact_id = get_post_meta( $viewing_applicant_id, '_contact_solicitor_contact_id', TRUE );
                    }
                }
            }

            add_post_meta( $offer_post_id, '_applicant_solicitor_contact_id', $applicant_solicitor_contact_id );
            add_post_meta( $offer_post_id, '_property_id', $viewing->property_id );

            // If any owner has a solicitor assigned, select them as the offer owner solicitor
            $property_owner_solicitor_contact_id = '';

            $owner_contact_ids = get_post_meta($viewing->property_id, '_owner_contact_id', TRUE);
            if ( !empty( $owner_contact_ids ) )
            {
                if ( !is_array($owner_contact_ids) )
                {
                    $owner_contact_ids = array($owner_contact_ids);
                }

                foreach( $owner_contact_ids as $owner_contact_id )
                {
                    if ( empty($property_owner_solicitor_contact_id) )
                    {
                        if ( !empty( get_post_meta( $owner_contact_id, '_contact_solicitor_contact_id', TRUE ) ) )
                        {
                            $property_owner_solicitor_contact_id = get_post_meta( $owner_contact_id, '_contact_solicitor_contact_id', TRUE );
                        }
                    }
                }
            }
            add_post_meta( $offer_post_id, '_property_owner_solicitor_contact_id', $property_owner_solicitor_contact_id );

            add_post_meta( $offer_post_id, '_offer_date_time', date("Y-m-d H:i:s") );

            update_post_meta( (int)$_GET['post'], '_offer_id', $offer_post_id );
            update_post_meta( (int)$_GET['post'], '_status', 'offer_made' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_offer_made',
            );

            $data = array(
                'comment_post_ID'      => (int)$_GET['post'],
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );

            // Do redirect
            wp_redirect( admin_url( 'post.php?post=' . $offer_post_id . '&action=edit' ) );
            exit();
        }

    }

    public function check_create_sale()
    {
        if ( isset($_GET['create_sale']) && isset($_GET['post']) )
        {
            if ( get_post_type((int)$_GET['post']) != 'offer')
                return;

            $offer = new PH_Offer((int)$_GET['post']);

            $offer_applicant_ids = $offer->get_applicant_ids();

            $sale_post = array(
              'post_title'    => '',
              'post_content'  => '',
              'post_type'  => 'sale',
              'post_status'   => 'publish',
              'comment_status'    => 'closed',
              'ping_status'    => 'closed',
            );
            
            // Insert the post into the database
            $sale_post_id = wp_insert_post( $sale_post );
            
            add_post_meta( $sale_post_id, '_status', 'current' );
            add_post_meta( $sale_post_id, '_amount', $offer->amount );

            foreach( $offer_applicant_ids as $offer_applicant_id )
            {
                add_post_meta( $sale_post_id, '_applicant_contact_id', $offer_applicant_id );
            }

            add_post_meta( $sale_post_id, '_applicant_solicitor_contact_id', $offer->applicant_solicitor_contact_id );
            add_post_meta( $sale_post_id, '_property_id', $offer->property_id );
            add_post_meta( $sale_post_id, '_property_owner_solicitor_contact_id', $offer->property_owner_solicitor_contact_id );
            add_post_meta( $sale_post_id, '_sale_date_time', date("Y-m-d H:i:s") );

            update_post_meta( (int)$_GET['post'], '_sale_id', $sale_post_id );

            $current_user = wp_get_current_user();

            // Add note/comment to offer
            $comment = array(
                'note_type' => 'action',
                'action' => 'offer_sale_created',
            );

            $data = array(
                'comment_post_ID'      => (int)$_GET['post'],
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );

            // Do redirect
            wp_redirect( admin_url( 'post.php?post=' . $sale_post_id . '&action=edit' ) );
            exit();
        }

    }

    public function check_create_tenancy() {
        if ( isset( $_GET['create_tenancy'] ) && isset( $_GET['post'] ) ) {
            if ( get_post_type( (int) $_GET['post'] ) != 'viewing' ) {
                return;
            }

            $viewing = new PH_Viewing( (int) $_GET['post'] );

            $viewing_applicant_ids = $viewing->get_applicant_ids();

            $tenancy_post = array(
                'post_title'     => '',
                'post_content'   => '',
                'post_type'      => 'tenancy',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            );

            // Insert the post into the database
            $tenancy_post_id = wp_insert_post( $tenancy_post );

            $property_id = $viewing->property_id;

            foreach( $viewing_applicant_ids as $viewing_applicant_id )
            {
                add_post_meta( $tenancy_post_id, '_applicant_contact_id', $viewing_applicant_id );
            }

            add_post_meta( $tenancy_post_id, '_property_id', $viewing->property_id );

            add_post_meta( $tenancy_post_id, '_rent', get_post_meta( $property_id, '_rent', true ) );
            add_post_meta( $tenancy_post_id, '_rent_frequency', get_post_meta( $property_id, '_rent_frequency', true ) );
            add_post_meta( $tenancy_post_id, '_price_actual', get_post_meta( $property_id, '_price_actual', true ) );
            add_post_meta( $tenancy_post_id, '_currency', get_post_meta( $property_id, '_currency', true ) );

            add_post_meta( $tenancy_post_id, '_deposit', get_post_meta( $property_id, '_deposit', true ) );

            update_post_meta( (int) $_GET['post'], '_tenancy_id', $tenancy_post_id );
            update_post_meta( (int) $_GET['post'], '_status', 'offer_made' );

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action'    => 'viewing_offer_made',
            );

            $data = array(
                'comment_post_ID'      => (int) $_GET['post'],
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date( "Y-m-d H:i:s" ),
                'comment_content'      => serialize( $comment ),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            $comment_id = wp_insert_comment( $data );

            // Do redirect
            wp_redirect( admin_url( 'post.php?post=' . $tenancy_post_id . '&action=edit' ) );
            exit();
        }

    }

	/**
	 * Add an error message
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option
	 */
	public function save_errors() {
		update_option( 'propertyhivemeta_box_errors', self::$meta_box_errors );
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
        
		$errors = @unserialize( get_option( 'propertyhive_meta_box_errors' ), ['allowed_classes' => false] );

		if ( $errors !== false && !empty( $errors ) ) {

			echo '<div id="propertyhive_errors" class="error fade">';
			foreach ( $errors as $error ) {
				echo '<p>' . esc_html( $error ) . '</p>';
			}
			echo '</div>';

			// Clear
			delete_option( 'propertyhive_meta_box_errors' );
		}
	}

	/**
	 * Add PH Meta boxes
	 */
	public function add_meta_boxes() {
	    
        global $tabs, $post, $pagenow;
        
		// PROPERTY
		if (!isset($tabs)) $tabs = array();

        /* PROPERTY SUMMARY META BOXES */
        $meta_boxes = array();

        $pinned_notes_args = array();
        $pinned_notes = array();
        if ( $pagenow != 'post-new.php' && isset($post->post_type) && in_array( 
            $post->post_type, 
            apply_filters( 'propertyhive_post_types_with_tabs', array('property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy') )
        ) )
        {
            $pinned_notes_args = array(
                'post_id' => (int)$post->ID,
                'type' => 'propertyhive_note',
                'fields' => 'ids',
                'search' => '"pinned";s:1:"1"',
                'meta_query' => array(
                    array(
                        'key' => 'related_to',
                        'value' => '"' . (int)$post->ID . '"',
                        'compare' => 'LIKE',
                    ),
                )
            );

            $pinned_notes = get_comments( $pinned_notes_args );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-property-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Property_Pinned_Notes::output',
                    'screen' => 'property',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        $meta_boxes[5] = array(
            'id' => 'propertyhive-property-address',
            'title' => __( 'Property Address', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Address::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
        {
            $meta_boxes[10] = array(
                'id' => 'propertyhive-property-owner',
                'title' => __( 'Property Owner / Landlord', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Property_Owner::output',
                'screen' => 'property',
                'context' => 'normal',
                'priority' => 'high'
            );
        }
        $meta_boxes[15] = array(
            'id' => 'propertyhive-property-record-details',
            'title' => __( 'Record Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Record_Details::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-property-coordinates',
            'title' => __( 'Property Location', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Coordinates::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_property_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'property'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            $tabs['tab_summary']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'property\')' );
        }
        
        /* PROPERTY DETAILS META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-property-department',
            'title' => __( 'Property Department', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Department::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-property-residential-sales-details',
            'title' => __( 'Residential Sales Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Residential_Sales_Details::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-property-residential-lettings-details',
            'title' => __( 'Residential Lettings Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Residential_Lettings_Details::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-property-residential-details',
            'title' => __( 'Residential Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Residential_Details::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[25] = array(
            'id' => 'propertyhive-property-commercial-details',
            'title' => __( 'Commercial Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Commercial_Details::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[30] = array(
            'id' => 'propertyhive-property-material-information',
            'title' => __( 'Utilities & Additional Information', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Material_Information::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_property_details_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_details'] = array(
            'name' => __( 'Details', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'property'
        );

        if ( 
            ( 
                get_post_meta( $post->ID, '_department', TRUE ) == 'commercial' || 
                ph_get_custom_department_based_on(get_post_meta( $post->ID, '_department', TRUE )) == 'commercial' 
            ) && 
            isset($post->post_parent) && 
            $post->post_parent == 0 
        )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-property-commercial-units',
                'title' => __( 'Commercial Units', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Property_Commercial_Units::output',
                'screen' => 'property',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_property_commercial_units_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_units'] = array(
                'name' => __( 'Units', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'property'
            );
        }

        /* PROPERTY MARKETING META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-property-marketing',
            'title' => __( 'Property Marketing', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Marketing::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            $meta_boxes[10] = array(
                'id' => 'propertyhive-property-marketing-statistics',
                'title' => __( 'Property Marketing Statistics', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Property_Marketing_Statistics::output',
                'screen' => 'property',
                'context' => 'normal',
                'priority' => 'high'
            );
        }

        $meta_boxes = apply_filters( 'propertyhive_property_marketing_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_marketing'] = array(
            'name' => __( 'Marketing', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'property',
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            $tabs['tab_marketing']['ajax_actions'] = array( 'get_property_marketing_statistics_meta_box^' . wp_create_nonce( 'get_property_marketing_statistics_meta_box' ) . '^reload_marketing_statistics()' );
        }

        /* PROPERTY DESCRIPTIONS META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-property-features',
            'title' => __( 'Property Features', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Features::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'postexcerpt',
            'title' => __( 'Property Summary Description', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Summary_Description::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-property-rooms',
            'title' => __( 'Property Descriptions', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Rooms::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-property-description',
            'title' => __( 'Property Description', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Description::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_property_descriptions_meta_boxes', $meta_boxes );
        ksort($meta_boxes);
        
        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_descriptions'] = array(
            'name' => __( 'Descriptions', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'property'
        );

        /* PROPERTY MEDIA META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-property-photos',
            'title' => __( 'Property Photos', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Photos::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-property-floorplans',
            'title' => __( 'Property Floorplans', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Floorplans::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-property-brochures',
            'title' => __( 'Property Brochures', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Brochures::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-property-epcs',
            'title' => __( 'Property EPCs', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Epcs::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[25] = array(
            'id' => 'propertyhive-property-virtual-tours',
            'title' => __( 'Property Virtual Tours', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Virtual_Tours::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_property_media_meta_boxes', $meta_boxes );
        ksort($meta_boxes);
        
        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_media'] = array(
            'name' => __( 'Media', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'property'
        );

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            /* PROPERTY VIEWINGS META BOXES */
            if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
            {
                $meta_boxes = array();
                $meta_boxes[5] = array(
                    'id' => 'propertyhive-property-viewings',
                    'title' => __( 'Viewings', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Property_Viewings::output',
                    'screen' => 'property',
                    'context' => 'normal',
                    'priority' => 'high'
                );

                $meta_boxes = apply_filters( 'propertyhive_property_viewings_meta_boxes', $meta_boxes );
                ksort($meta_boxes);
                
                $ids = array();
                foreach ($meta_boxes as $meta_box)
                {
                    add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                    $ids[] = $meta_box['id'];
                }

                $tab_name_suffix = '';
                if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                {
                    $args = array(
                        'post_type'   => 'viewing',
                        'nopaging'    => true,
                        'fields'      => 'ids',
                        'post_status' => 'publish',
                        'meta_query'  => array(
                            array(
                                'key'   => '_property_id',
                                'value' => $post->ID
                            ),
                        ),
                    );
                    $viewings_query = new WP_Query( $args );
                    $viewings_count = $viewings_query->found_posts;
                    wp_reset_postdata();

                    $tab_name_suffix = ' (' . $viewings_count . ')';
                }

                $tabs['tab_viewings'] = array(
                    'name' => __( 'Viewings', 'propertyhive' ) . $tab_name_suffix,
                    'metabox_ids' => $ids,
                    'post_type' => 'property',
                    'ajax_actions' => array( 'get_property_viewings_meta_box' ),
                );
            }

            if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
            {
                if ( 
                    (
                        get_post_meta( $post->ID, '_department', TRUE ) == 'residential-sales' ||
                        ph_get_custom_department_based_on(get_post_meta( $post->ID, '_department', TRUE )) == 'residential-sales' 
                    )
                    ||
                    (
                        (
                            get_post_meta( $post->ID, '_department', TRUE ) == 'commercial' ||
                            ph_get_custom_department_based_on(get_post_meta( $post->ID, '_department', TRUE )) == 'commercial' 
                        )
                        && 
                        get_post_meta( $post->ID, '_for_sale', TRUE ) == 'yes'
                    )
                )
                {
                    /* PROPERTY OFFERS META BOXES */
                    $meta_boxes = array();
                    $meta_boxes[5] = array(
                        'id' => 'propertyhive-property-offers',
                        'title' => __( 'Offers', 'propertyhive' ),
                        'callback' => 'PH_Meta_Box_Property_Offers::output',
                        'screen' => 'property',
                        'context' => 'normal',
                        'priority' => 'high'
                    );

                    $meta_boxes = apply_filters( 'propertyhive_property_offers_meta_boxes', $meta_boxes );
                    ksort($meta_boxes);
                    
                    $ids = array();
                    foreach ($meta_boxes as $meta_box)
                    {
                        add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                        $ids[] = $meta_box['id'];
                    }

                    $tab_name_suffix = '';
                    if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                    {
                        $args = array(
                            'post_type'   => 'offer',
                            'nopaging'    => true,
                            'fields'      => 'ids',
                            'post_status' => 'publish',
                            'meta_query'  => array(
                                array(
                                    'key'   => '_property_id',
                                    'value' => $post->ID
                                ),
                            ),
                        );
                        $offers_query = new WP_Query( $args );
                        $offers_count = $offers_query->found_posts;
                        wp_reset_postdata();

                        $tab_name_suffix = ' (' . $offers_count . ')';
                    }

                    $tabs['tab_offers'] = array(
                        'name' => __( 'Offers', 'propertyhive' ) . $tab_name_suffix,
                        'metabox_ids' => $ids,
                        'post_type' => 'property',
                        'ajax_actions' => array( 'get_property_offers_meta_box' ),
                    );

                    /* PROPERTY SALES META BOXES */
                    $meta_boxes = array();
                    $meta_boxes[5] = array(
                        'id' => 'propertyhive-property-sales',
                        'title' => __( 'Sales', 'propertyhive' ),
                        'callback' => 'PH_Meta_Box_Property_Sales::output',
                        'screen' => 'property',
                        'context' => 'normal',
                        'priority' => 'high'
                    );

                    $meta_boxes = apply_filters( 'propertyhive_property_sales_meta_boxes', $meta_boxes );
                    ksort($meta_boxes);
                    
                    $ids = array();
                    foreach ($meta_boxes as $meta_box)
                    {
                        add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                        $ids[] = $meta_box['id'];
                    }

                    $tab_name_suffix = '';
                    if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                    {
                        $args = array(
                            'post_type'   => 'sale',
                            'nopaging'    => true,
                            'fields'      => 'ids',
                            'post_status' => 'publish',
                            'meta_query'  => array(
                                array(
                                    'key'   => '_property_id',
                                    'value' => $post->ID
                                ),
                            ),
                        );
                        $sales_query = new WP_Query( $args );
                        $sales_count = $sales_query->found_posts;
                        wp_reset_postdata();

                        $tab_name_suffix = ' (' . $sales_count . ')';
                    }
                    
                    $tabs['tab_sales'] = array(
                        'name' => __( 'Sales', 'propertyhive' ) . $tab_name_suffix,
                        'metabox_ids' => $ids,
                        'post_type' => 'property',
                        'ajax_actions' => array( 'get_property_sales_meta_box' ),
                    );
                }
            }
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
            {
                /* PROPERTY ENQUIRIES META BOXES */
                $meta_boxes = array();
                $meta_boxes[5] = array(
                    'id' => 'propertyhive-property-enquiries',
                    'title' => __( 'Enquiries', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Property_Enquiries::output',
                    'screen' => 'property',
                    'context' => 'normal',
                    'priority' => 'high'
                );

                $meta_boxes = apply_filters( 'propertyhive_property_enquiries_meta_boxes', $meta_boxes );
                ksort($meta_boxes);
                
                $ids = array();
                foreach ($meta_boxes as $meta_box)
                {
                    add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                    $ids[] = $meta_box['id'];
                }
                
                $tab_name_suffix = '';
                if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                {
                    $args = array(
                        'post_type' => 'enquiry',
                        'nopaging'    => true,
                        'fields' => 'ids',
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key' => 'property_id',
                                'value' => $post->ID
                            ),
                            array(
                                'key' => '_property_id',
                                'value' => $post->ID
                            )
                        ),
                    );
                    $enquiry_query = new WP_Query( $args );
                    $enquiry_count = $enquiry_query->found_posts;
                    wp_reset_postdata();

                    $tab_name_suffix = ' (' . $enquiry_count . ')';
                }

                $tabs['tab_enquiries'] = array(
                    'name' => __( 'Enquiries', 'propertyhive' ) . $tab_name_suffix,
                    'metabox_ids' => $ids,
                    'post_type' => 'property',
                    'ajax_actions' => array( 'get_property_enquiries_meta_box' ),
                );
            }
        }

		if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' ) {

            if ( 
                get_post_meta( $post->ID, '_department', TRUE ) == 'residential-lettings' ||
                ph_get_custom_department_based_on(get_post_meta( $post->ID, '_department', TRUE )) == 'residential-lettings'
            )
            {
                if ( get_option( 'propertyhive_module_disabled_tenancies', '' ) != 'yes' )
                {
                    /* TENANCY MANAGEMENT META BOXES */
                    $meta_boxes    = array();

                    $meta_boxes[5] = array(
                        'id' => 'propertyhive-property-tenancies',
                        'title' => __( 'Tenancies', 'propertyhive' ),
                        'callback' => 'PH_Meta_Box_Property_Tenancies::output',
                        'screen' => 'property',
                        'context' => 'normal',
                        'priority' => 'high'
                    );

                    $meta_boxes[10] = array(
                        'id'       => 'propertyhive-management-dates',
                        'title'    => __( 'Management Dates', 'propertyhive' ),
                        'callback' => 'PH_Meta_Box_Management_Dates::output',
                        'screen'   => 'property',
                        'context'  => 'normal',
                        'priority' => 'high'
                    );

                    $meta_boxes = apply_filters( 'propertyhive_property_management_meta_boxes', $meta_boxes );
                    ksort( $meta_boxes );

                    $ids = array();
                    foreach ( $meta_boxes as $meta_box ) {
                        add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                        $ids[] = $meta_box['id'];
                    }

                    $tabs['tab_property_management'] = array(
                        'name'        => __( 'Management', 'propertyhive' ),
                        'metabox_ids' => $ids,
                        'post_type'   => 'property',
                        'ajax_actions' => array( 'get_property_tenancies_grid^' . wp_create_nonce( 'get_property_tenancies_grid' ) ),
                    );
                }
			}
		}

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            /* PROPERTY NOTES META BOXES */
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-property-history-notes',
                'title' => __( 'Property History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Property_Notes::output',
                'screen' => 'property',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_property_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);
            
            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_property_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'property',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'property\')' ),
            );

            add_meta_box( 'propertyhive-property-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Property_Actions::output', 'property', 'side' );
        }

        // CONTACT
        if (!isset($tabs)) $tabs = array();

        $meta_boxes = array();

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'contact' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-contact-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Contact_Pinned_Notes::output',
                    'screen' => 'contact',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        if ( $pagenow == 'post-new.php' && get_post_type($post->ID) == 'contact' )
        {
            $meta_boxes[2] = array(
                'id' => 'propertyhive-contact-new-relationship',
                'title' => __( 'Contact Type', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Contact_New_Relationship::output',
                'screen' => 'contact',
                'context' => 'normal',
                'priority' => 'high'
            );
        }

        $meta_boxes[5] = array(
            'id' => 'propertyhive-contact-correspondence-address',
            'title' => __( 'Correspondence Address', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Contact_Correspondence_Address::output',
            'screen' => 'contact',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-contact-contact-details',
            'title' => __( 'Contact Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Contact_Contact_Details::output',
            'screen' => 'contact',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-contact-solicitor',
            'title' => __( 'Contact Solicitor Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Contact_Solicitor::output',
            'screen' => 'contact',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_contact_details_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_contact_details'] = array(
            'name' => __( 'Contact Details', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'contact'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'contact' )
        {
            $tabs['tab_contact_details']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'contact\')' );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'contact' )
        {
            add_meta_box( 'propertyhive-contact-relationships', __( 'Relationships', 'propertyhive' ), 'PH_Meta_Box_Contact_Relationships::output', 'contact', 'normal', 'high' );
            $tabs['tab_contact_relationships'] = array(
                'name' => __( 'Relationships', 'propertyhive' ),
                'metabox_ids' => array('propertyhive-contact-relationships'),
                'post_type' => 'contact'
            );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'contact' )
        {
            $contact_types = get_post_meta( $post->ID, '_contact_types', TRUE );

            if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
            {
                $args = array(
                    'post_type' => 'viewing',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => '_applicant_contact_id',
                            'value' => $post->ID
                        )
                    )
                );
                $viewing_query = new WP_Query( $args );
                $viewing_count = $viewing_query->found_posts;
                wp_reset_postdata();

                // If contact is an applicant, or is attached to a viewing, show the viewings tab and meta box
                if (
                    ( is_array($contact_types) && in_array('applicant', $contact_types) )
                    ||
                    $viewing_count > 0
                )
                {
                    /* CONTACT VIEWINGS META BOXES */
                    add_meta_box( 'propertyhive-contact-viewings', __( 'Viewings', 'propertyhive' ), 'PH_Meta_Box_Contact_Viewings::output', 'contact', 'normal', 'high' );

                    $tab_name_suffix = '';
                    if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                    {
                        $tab_name_suffix = ' (' . $viewing_count . ')';
                    }

                    $tabs['tab_viewings'] = array(
                        'name' => __( 'Viewings', 'propertyhive' ) . $tab_name_suffix,
                        'metabox_ids' => array('propertyhive-contact-viewings'),
                        'post_type' => 'contact',
                        'ajax_actions' => array( 'get_contact_viewings_meta_box' ),
                    );
                }
            }

            if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
            {
                $args = array(
                    'post_type' => 'offer',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => '_applicant_contact_id',
                            'value' => $post->ID
                        )
                    )
                );
                $offer_query = new WP_Query( $args );
                $offer_count = $offer_query->found_posts;
                wp_reset_postdata();

                $args = array(
                    'post_type' => 'sale',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => '_applicant_contact_id',
                            'value' => $post->ID
                        )
                    )
                );
                $sale_query = new WP_Query( $args );
                $sale_count = $sale_query->found_posts;
                wp_reset_postdata();

                $has_sales_applicant_profile = false;
                if ( $offer_count == 0 || $sale_count == 0 )
                {
                    if ( is_array($contact_types) && in_array('applicant', $contact_types) )
                    {
                        $num_applicant_profiles = get_post_meta( $post->ID, '_applicant_profiles', TRUE );
                        if ( $num_applicant_profiles == '' )
                        {
                            $num_applicant_profiles = 0;
                        }

                        if ( $num_applicant_profiles > 0 )
                        {
                            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                            {
                                $applicant_profile = get_post_meta( $post->ID, '_applicant_profile_' . $i, TRUE );

                                if (
                                    isset($applicant_profile['department']) &&
                                    (
                                        $applicant_profile['department'] == 'residential-sales' ||
                                        ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-sales'
                                    )
                                )
                                {
                                    $has_sales_applicant_profile = true;
                                }
                            }
                        }
                    }
                }

                if ( $has_sales_applicant_profile === true || $offer_count > 0 )
                {
                    /* CONTACT OFFERS META BOXES */
                    add_meta_box( 'propertyhive-contact-offers', __( 'Offers', 'propertyhive' ), 'PH_Meta_Box_Contact_Offers::output', 'contact', 'normal', 'high' );

                    $tab_name_suffix = '';
                    if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                    {
                        $tab_name_suffix = ' (' . $offer_count . ')';
                    }
                    
                    $tabs['tab_offers'] = array(
                        'name' => __( 'Offers', 'propertyhive' ) . $tab_name_suffix,
                        'metabox_ids' => array('propertyhive-contact-offers'),
                        'post_type' => 'contact',
                        'ajax_actions' => array( 'get_contact_offers_meta_box' ),
                    );
                }

                if ( $has_sales_applicant_profile === true || $sale_count > 0 )
                {
                    /* CONTACT SALES META BOXES */
                    add_meta_box( 'propertyhive-contact-sales', __( 'Sales', 'propertyhive' ), 'PH_Meta_Box_Contact_Sales::output', 'contact', 'normal', 'high' );

                    $tab_name_suffix = '';
                    if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                    {
                        $tab_name_suffix = ' (' . $sale_count . ')';
                    }
                    
                    $tabs['tab_sales'] = array(
                        'name' => __( 'Sales', 'propertyhive' ) . $tab_name_suffix,
                        'metabox_ids' => array('propertyhive-contact-sales'),
                        'post_type' => 'contact',
                        'ajax_actions' => array( 'get_contact_sales_meta_box' ),
                    );
                }
            }

            if ( get_option( 'propertyhive_module_disabled_tenancies', '' ) != 'yes' )
            {
                $args = array(
                    'post_type' => 'tenancy',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => array(
                        array(
                            'key' => '_applicant_contact_id',
                            'value' => $post->ID
                        )
                    )
                );
                $tenancy_query = new WP_Query( $args );
                $tenancy_count = $tenancy_query->found_posts;
                wp_reset_postdata();

                $has_lettings_applicant_profile = false;
                if ( $tenancy_count == 0 )
                {
                    if ( is_array($contact_types) && in_array('applicant', $contact_types) )
                    {
                        $num_applicant_profiles = get_post_meta( $post->ID, '_applicant_profiles', TRUE );
                        if ( $num_applicant_profiles == '' )
                        {
                            $num_applicant_profiles = 0;
                        }

                        if ( $num_applicant_profiles > 0 )
                        {
                            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                            {
                                $applicant_profile = get_post_meta( $post->ID, '_applicant_profile_' . $i, TRUE );

                                if (
                                    isset($applicant_profile['department']) &&
                                    (
                                        $applicant_profile['department'] == 'residential-lettings' ||
                                        ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-lettings'
                                    )
                                )
                                {
                                    $has_lettings_applicant_profile = true;
                                }
                            }
                        }
                    }
                }

                if ( $has_lettings_applicant_profile === true || $tenancy_count > 0 )
                {
                    $meta_boxes = array();

                    $meta_boxes[5] = array(
                        'id' => 'propertyhive-contact-tenancies',
                        'title' => __( 'Tenancies', 'propertyhive' ),
                        'callback' => 'PH_Meta_Box_Contact_Tenancies::output',
                        'screen' => 'contact',
                        'context' => 'normal',
                        'priority' => 'high'
                    );

                    $meta_boxes = apply_filters( 'propertyhive_contact_tenancies_meta_boxes', $meta_boxes );
                    ksort( $meta_boxes );

                    $ids = array();
                    foreach ( $meta_boxes as $meta_box ) {
                        add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                        $ids[] = $meta_box['id'];
                    }

                    $tab_name_suffix = '';
                    if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                    {
                        $tab_name_suffix = ' (' . $tenancy_count . ')';
                    }

                    $tabs['tab_contact_tenancies'] = array(
                        'name'        => __( 'Tenancies', 'propertyhive' ) . $tab_name_suffix,
                        'metabox_ids' => $ids,
                        'post_type'   => 'contact',
                        'ajax_actions' => array( 'get_contact_tenancies_grid^' . wp_create_nonce( 'get_contact_tenancies_grid' ) ),
                    );
                }
            }

            if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
            {
                $meta_query = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_contact_id',
                        'value' => $post->ID,
                    ),
                );

                $contact_email_address = get_post_meta( $post->ID, '_email_address', TRUE );
                if ( !empty($contact_email_address) )
                {
                    $meta_query[] = array(
                        'key' => 'email',
                        'value' => $contact_email_address,
                    );

                    $meta_query[] = array(
                        'key' => 'email_address',
                        'value' => $contact_email_address,
                    );
                }

                $tab_name_suffix = '';
                if ( apply_filters( 'propertyhive_show_tab_counts', true ) === true )
                {
                    $args = array(
                        'post_type' => 'enquiry',
                        'nopaging'    => true,
                        'fields' => 'ids',
                        'meta_query' => $meta_query,
                    );
                    $enquiry_query = new WP_Query( $args );
                    $enquiry_count = $enquiry_query->found_posts;
                    
                    $tab_name_suffix = ' (' . $enquiry_count . ')';
                }

                wp_reset_postdata();

                /* CONTACT ENQUIRIES META BOXES */
                add_meta_box( 'propertyhive-contact-enquiries', __( 'Enquiries', 'propertyhive' ), 'PH_Meta_Box_Contact_Enquiries::output', 'contact', 'normal', 'high' );
                $tabs['tab_contact_enquiries'] = array(
                    'name' => __( 'Enquiries', 'propertyhive' ) . $tab_name_suffix,
                    'metabox_ids' => array('propertyhive-contact-enquiries'),
                    'post_type' => 'contact',
                    'ajax_actions' => array( 'get_contact_enquiries_meta_box' ),
                );
            }

            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-contact-history-notes',
                'title' => __( 'Contact History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Contact_Notes::output',
                'screen' => 'contact',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_contact_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_contact_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'contact',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'contact\')' ),
            );

            add_meta_box( 'propertyhive-contact-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Contact_Actions::output', 'contact', 'side' );
        } 

        // ENQUIRY
        if (!isset($tabs)) $tabs = array();

        $meta_boxes = array();

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'enquiry' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-enquiry-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Enquiry_Pinned_Notes::output',
                    'screen' => 'enquiry',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        $meta_boxes[5] = array(
            'id' => 'propertyhive-enquiry-record-details',
            'title' => __( 'Record Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Enquiry_Record_details::output',
            'screen' => 'enquiry',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-enquiry-details',
            'title' => __( 'Enquiry Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Enquiry_details::output',
            'screen' => 'enquiry',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_enquiry_details_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_enquiry_details'] = array(
            'name' => __( 'Details', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'enquiry'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'enquiry' )
        {
            $tabs['tab_enquiry_details']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'enquiry\')' );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'enquiry' )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-enquiry-history-notes',
                'title' => __( 'Enquiry History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Enquiry_Notes::output',
                'screen' => 'enquiry',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_enquiry_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_enquiry_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'enquiry',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'enquiry\')' ),
            );
        }

        // APPRAISAL
        if (!isset($tabs)) $tabs = array();

        /* APPRAISAL SUMMARY META BOXES */
        $meta_boxes = array();
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'appraisal' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-appraisal-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Appraisal_Pinned_Notes::output',
                    'screen' => 'appraisal',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'appraisal' )
        {
            $meta_boxes[5] = array(
                'id' => 'propertyhive-appraisal-details',
                'title' => __( 'Appraisal Details', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Appraisal_Details::output',
                'screen' => 'appraisal',
                'context' => 'normal',
                'priority' => 'high'
            );
        }
        $meta_boxes[10] = array(
            'id' => 'propertyhive-appraisal-event',
            'title' => __( 'Event Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Appraisal_Event::output',
            'screen' => 'appraisal',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-appraisal-property-owner',
            'title' => __( 'Property Owner Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Appraisal_Property_Owner::output',
            'screen' => 'appraisal',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-appraisal-property',
            'title' => __( 'Property Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Appraisal_Property::output',
            'screen' => 'appraisal',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_appraisal_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_appraisal_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'appraisal'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'appraisal' )
        {
            $tabs['tab_appraisal_summary']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'appraisal\')' );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'appraisal' )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-appraisal-history-notes',
                'title' => __( 'Appraisal History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Appraisal_Notes::output',
                'screen' => 'appraisal',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_appraisal_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_appraisal_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'appraisal',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'appraisal\')' ),
            );

            add_meta_box( 'propertyhive-appraisal-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Appraisal_Actions::output', 'appraisal', 'side' );
        }

        // VIEWING
        if (!isset($tabs)) $tabs = array();

        /* VIEWING SUMMARY META BOXES */
        $meta_boxes = array();
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'viewing' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-viewing-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Viewing_Pinned_Notes::output',
                    'screen' => 'viewing',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'viewing' )
        {
            $meta_boxes[5] = array(
                'id' => 'propertyhive-viewing-details',
                'title' => __( 'Viewing Details', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Viewing_Details::output',
                'screen' => 'viewing',
                'context' => 'normal',
                'priority' => 'high'
            );
        }
        $meta_boxes[10] = array(
            'id' => 'propertyhive-viewing-event',
            'title' => __( 'Event Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Viewing_Event::output',
            'screen' => 'viewing',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-viewing-applicant',
            'title' => __( 'Applicant Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Viewing_Applicant::output',
            'screen' => 'viewing',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-viewing-property',
            'title' => __( 'Property Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Viewing_Property::output',
            'screen' => 'viewing',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_viewing_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_viewing_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'viewing'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'viewing' )
        {
            $tabs['tab_viewing_summary']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'viewing\')' );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'viewing' )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-viewing-history-notes',
                'title' => __( 'Viewing History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Viewing_Notes::output',
                'screen' => 'viewing',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_viewing_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_viewing_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'viewing',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'viewing\')' ),
            );

            add_meta_box( 'propertyhive-viewing-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Viewing_Actions::output', 'viewing', 'side' );
        }

        // OFFER
        if (!isset($tabs)) $tabs = array();

        /* OFFER SUMMARY META BOXES */
        $meta_boxes = array();
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'offer' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-offer-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Offer_Pinned_Notes::output',
                    'screen' => 'offer',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        $meta_boxes[5] = array(
            'id' => 'propertyhive-offer-details',
            'title' => __( 'Offer Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Offer_Details::output',
            'screen' => 'offer',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-offer-property',
            'title' => __( 'Property Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Offer_Property::output',
            'screen' => 'offer',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-offer-property-owner-solicitor',
            'title' => __( 'Property Owner Solicitor Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Offer_Property_Owner_Solicitor::output',
            'screen' => 'offer',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-offer-applicant',
            'title' => __( 'Applicant Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Offer_Applicant::output',
            'screen' => 'offer',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[25] = array(
            'id' => 'propertyhive-offer-applicant-solicitor',
            'title' => __( 'Applicant Solicitor Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Offer_Applicant_Solicitor::output',
            'screen' => 'offer',
            'context' => 'normal',
            'priority' => 'high'
        );
        

        $meta_boxes = apply_filters( 'propertyhive_offer_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_offer_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'offer'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'offer' )
        {
            $tabs['tab_offer_summary']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'offer\')' );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'offer' )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-offer-history-notes',
                'title' => __( 'Offer History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Offer_Notes::output',
                'screen' => 'offer',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_offer_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_offer_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'offer',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'offer\')' ),
            );

            add_meta_box( 'propertyhive-offer-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Offer_Actions::output', 'offer', 'side' );
        }

        // SALE
        if (!isset($tabs)) $tabs = array();

        /* SALE SUMMARY META BOXES */
        $meta_boxes = array();
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'sale' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-sale-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Sale_Pinned_Notes::output',
                    'screen' => 'sale',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        $meta_boxes[5] = array(
            'id' => 'propertyhive-sale-details',
            'title' => __( 'Sale Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Sale_Details::output',
            'screen' => 'sale',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-sale-property',
            'title' => __( 'Property Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Sale_Property::output',
            'screen' => 'sale',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-sale-property-owner-solicitor',
            'title' => __( 'Property Owner Solicitor Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Sale_Property_Owner_Solicitor::output',
            'screen' => 'sale',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[20] = array(
            'id' => 'propertyhive-sale-applicant',
            'title' => __( 'Applicant Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Sale_Applicant::output',
            'screen' => 'sale',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[25] = array(
            'id' => 'propertyhive-sale-applicant-solicitor',
            'title' => __( 'Applicant Solicitor Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Sale_Applicant_Solicitor::output',
            'screen' => 'sale',
            'context' => 'normal',
            'priority' => 'high'
        );
        
        $meta_boxes = apply_filters( 'propertyhive_sale_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_sale_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'sale'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'sale' )
        {
            $tabs['tab_sale_summary']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'sale\')' );
        }

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'sale' )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-sale-history-notes',
                'title' => __( 'Sale History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Sale_Notes::output',
                'screen' => 'sale',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_sale_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_sale_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'sale',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'sale\')' ),
            );

            add_meta_box( 'propertyhive-sale-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Sale_Actions::output', 'sale', 'side' );
        }

        // TENANCY
        if (!isset($tabs)) $tabs = array();

        /* TENANCY SUMMARY META BOXES */
        $meta_boxes = array();
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'tenancy' )
        {
            if ( !empty($pinned_notes) )
            {
                $meta_boxes[1] = array(
                    'id' => 'propertyhive-tenancy-pinned-notes',
                    'title' => __( 'Pinned Notes', 'propertyhive' ),
                    'callback' => 'PH_Meta_Box_Tenancy_Pinned_Notes::output',
                    'screen' => 'tenancy',
                    'context' => 'normal',
                    'priority' => 'high',
                );
            }
        }
        $meta_boxes[5] = array(
            'id' => 'propertyhive-tenancy-details',
            'title' => __( 'Tenancy Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Tenancy_Details::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-tenancy-property',
            'title' => __( 'Property', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Tenancy_Property::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-tenancy-applicant',
            'title' => __( 'Tenants', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Tenancy_Applicant::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_tenancy_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_tenancy_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'tenancy'
        );
        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'tenancy' )
        {
            $tabs['tab_tenancy_summary']['ajax_actions'] = array( '^^ph_redraw_pinned_notes_grid(\'tenancy\')' );
        }

        /* TENANCY DEPOSIT SCHEME META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-tenancy-deposit-scheme',
            'title' => __( 'Deposit Scheme Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Tenancy_Deposit_Scheme::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_tenancy_deposit_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_tenancy_deposit_scheme'] = array(
            'name' => __( 'Deposit Scheme', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'tenancy'
        );

        /* MANAGEMENT META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-tenancy-management',
            'title' => __( 'Management Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Tenancy_Management::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes[10] = array(
            'id' => 'propertyhive-management-dates',
            'title' => __( 'Management Dates', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Management_Dates::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_tenancy_management_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_tenancy_management'] = array(
            'name' => __( 'Management', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'tenancy'
        );

        /* TENANCY METER READINGS META BOXES */
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-tenancy-meter-readings',
            'title' => __( 'Meter Readings', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Tenancy_Meter_Readings::output',
            'screen' => 'tenancy',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_tenancy_meter_readings_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_tenancy_meter_readings'] = array(
            'name' => __( 'Meter Readings', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'tenancy'
        );

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'tenancy' )
        {
            /* HISTORY & NOTES META BOXES */
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-tenancy-history-notes',
                'title' => __( 'Tenancy History &amp; Notes', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Tenancy_Notes::output',
                'screen' => 'tenancy',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_tenancy_notes_meta_boxes', $meta_boxes );
            ksort($meta_boxes);

            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }

            $tabs['tab_tenancy_notes'] = array(
                'name' => __( 'History &amp; Notes', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'tenancy',
                'ajax_actions' => array( '^^ph_redraw_notes_grid(\'tenancy\')' ),
            );
        }

        $tabs = apply_filters( 'propertyhive_tabs', $tabs );

        // Force order of meta boxes
        $meta_box_ids = array();
        if ( 
            in_array(
                get_post_type($post->ID), 
                apply_filters( 'propertyhive_post_types_with_tabs', array('property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy') )
            )
        )
        {
            foreach ( $tabs as $tab_id => $tab_options)
            {
                if ( isset($tab_options['post_type']) && $tab_options['post_type'] == get_post_type($post->ID) )
                {
                    $meta_box_ids = array_merge( $meta_box_ids, $tab_options['metabox_ids'] );
                }
            }
        }

        if (!empty($meta_box_ids) )
        {
            $existing_meta_box_order = get_user_meta( get_current_user_id(), 'meta-box-order_' . get_post_type($post->ID), TRUE );
            if ( $existing_meta_box_order == '' )
            {
                $existing_meta_box_order = array();
                $existing_meta_box_order['side'] = '';
                $existing_meta_box_order['advanced'] = '';
            }
            $existing_meta_box_order['normal'] = implode(",", $meta_box_ids);

            update_user_meta( get_current_user_id(), 'meta-box-order_' . get_post_type($post->ID), $existing_meta_box_order );
        }

        // TO DO: move this so it works when in one column
        add_action( 'edit_form_after_title', array( $this, 'draw_meta_box_tabs' ), 31, 1);
    }
    
    /**
     * Draw meta box tabs
     */
    public function draw_meta_box_tabs() {
        
        global $post, $tabs;
        
        if (
            !empty($tabs) && 
            in_array(
                $post->post_type, 
                apply_filters( 'propertyhive_post_types_with_tabs', array('property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy') )
            )
        )
        {
            $meta_boxes_under_tabs = array();
            
            $i = 0;
            echo '<div id="propertyhive_metabox_tabs" style="margin-top:15px">';
            foreach ($tabs as $tab_id => $tab)
            {
                if (isset($tab['post_type']) && $post->post_type == $tab['post_type'])
                {
                    echo '<a href="#' . implode("|#", $tab['metabox_ids']) . '" id="' . esc_attr($tab_id) . '" class="button' . ( ($i == 0) ? ' button-primary' : '') . '"';
                    if ( isset($tab['ajax_actions']) )
                    {
                        echo ' data-ajax-actions="' . esc_attr(implode("|", $tab['ajax_actions'])) . '"';
                    }
                    echo '>' . esc_html($tab['name']) . '</a> ';
                    
                    $meta_boxes_under_tabs[] = $tab['metabox_ids'];
                    
                    ++$i;
                }
            }
            echo '</div><br>';

            // hidden field to keep track of selected hash
            echo '<input type="hidden" name="propertyhive_selected_metabox_tab" id="propertyhive_selected_metabox_tab" value="">';
            
            if (!empty($meta_boxes_under_tabs))
            {
                echo '
                <script>
                    var meta_boxes_under_tabs = ' . json_encode($meta_boxes_under_tabs) . ';
                    
                    var ajax_actions_executed = new Array();

                    jQuery(document).ready(function()
                    {
                        // Hide all on page load
                        hide_meta_box_tabs();
                        
                        // Show first meta box
                        //jQuery(\'#\' + meta_boxes_under_tabs[0][0] + \'\').show();
                        for (var i in meta_boxes_under_tabs[0])
                        {
                            jQuery(\'#\' + meta_boxes_under_tabs[0][i] + \'\').show();
                        }
                        //jQuery(\'#propertyhive_metabox_tabs a:first-child\').trigger(\'click\');
                        
                        // Set first button as primary
                        jQuery(\'#propertyhive_metabox_tabs a:first-child\').addClass(\'button-primary\');
                        
                        // Hide meta boxes and show correct one when tab clicked
                        jQuery(\'#propertyhive_metabox_tabs a\').click(function(e)
                        {
                            e.preventDefault();

                            hide_meta_box_tabs();
                            
                            var this_href = jQuery(this).attr(\'href\').split(\'|\');
                            
                            for (var i in this_href)
                            {
                                jQuery(this_href[i]).show();
                            }
                            
                            jQuery(this).addClass(\'button-primary\');
                            
                            ' . ( ( $post->post_type == 'property' ) ? 'if (jQuery(this).attr(\'id\') == \'tab_details\') { showHideDepartmentMetaBox(); }' : '' ) . '
                            ' . ( ( $post->post_type == 'property' ) ? 'if (jQuery(this).attr(\'id\') == \'tab_descriptions\') { showHideRoomsMetaBox(); }' : '' ) . '
                            ' . ( ( $post->post_type == 'property' ) ? 'if (jQuery(this).attr(\'id\') == \'tab_descriptions\') { showHideDescriptionsMetaBox(); }' : '' ) . '
                            
                            if ( jQuery(this).attr(\'data-ajax-actions\') && jQuery(this).attr(\'data-ajax-actions\') != \'\' )
                            {
                                var ajax_actions = jQuery(this).attr(\'data-ajax-actions\').split(\'|\');

                                for ( var i in ajax_actions )
                                {
                                    var ajax_action = ajax_actions[i].split(\'^\');

                                    jQuery(\'#\' + ajax_action[0].replace(\'get_\', \'propertyhive_\')).html(\'Loading...\');

                                    if ( ajax_action[2] ) // callback
                                    {
                                        eval(ajax_action[2]);
                                    }
                                    else
                                    {
                                        var data = {
                                            action: \'propertyhive_\' + ajax_action[0],
                                            post_id: ' . (int)$post->ID . ',
                                            security: ajax_action[1]
                                        }

                                        jQuery.post( \'' . esc_url(admin_url('admin-ajax.php')) . '\', data, function(response) 
                                        {
                                            jQuery(\'#\' + ajax_action[0].replace(\'get_\', \'propertyhive_\')).html(response);
                                            activateTipTip();
                                        }, \'html\');
                                    }
                                }
                            }

                            //window.location.hash = jQuery(this).attr(\'href\');
                            history.pushState({}, \'\', \'#\' + jQuery(this).attr(\'href\').replace(/#/g, \'\').replace(/\|/g, \'%7C\'));

                            jQuery(\'#propertyhive_selected_metabox_tab\').val(jQuery(this).attr(\'href\').replace(/#/g, \'\').replace(/\|/g, \'%7C\'));

                            return false;
                        });

                        // Set default tab if hash set
                        if (window.location.hash != \'\')
                        {
                            var hash = window.location.hash;
                            if ( window.location.hash.indexOf(\'%7C\') )
                            {
                                hash = window.location.hash.replace(/%7C/g, \'|#\');
                            }
                            jQuery("#propertyhive_metabox_tabs a[href*=\'" + hash + "\']").trigger(\'click\');
                        }
                    });

                    jQuery(window).on(\'load\', function()
                    {
                        if (window.location.hash == \'\')
                        {
                            if ( jQuery(\'#propertyhive_metabox_tabs a\').eq(0).attr(\'data-ajax-actions\') && jQuery(\'#propertyhive_metabox_tabs a\').eq(0).attr(\'data-ajax-actions\') != \'\' )
                            {
                                var ajax_actions = jQuery(\'#propertyhive_metabox_tabs a\').eq(0).attr(\'data-ajax-actions\').split(\'|\');

                                for ( var i in ajax_actions )
                                {
                                    var ajax_action = ajax_actions[i].split(\'^\');

                                    jQuery(\'#\' + ajax_action[0].replace(\'get_\', \'propertyhive_\')).html(\'Loading...\');

                                    if ( ajax_action[2] ) // callback
                                    {
                                        eval(ajax_action[2]);
                                    }
                                    else
                                    {
                                        var data = {
                                            action: \'propertyhive_\' + ajax_action[0],
                                            post_id: ' . (int)$post->ID . ',
                                            security: ajax_action[1]
                                        }

                                        jQuery.post( \'' . esc_url(admin_url('admin-ajax.php')) . '\', data, function(response) 
                                        {
                                            jQuery(\'#\' + ajax_action[0].replace(\'get_\', \'propertyhive_\')).html(response);
                                            activateTipTip();
                                        }, \'html\');
                                    }
                                }
                            }
                        }
                    });
                    
                    function hide_meta_box_tabs()
                    {
                        for (var i in meta_boxes_under_tabs)
                        {
                            for (var j in meta_boxes_under_tabs[i])
                            {
                                jQuery(\'#\' + meta_boxes_under_tabs[i][j] + \'\').hide();
                            }
                        }
                        
                        jQuery(\'#propertyhive_metabox_tabs a\').removeClass(\'button-primary\');
                    }
                </script>
                ';
            }
        }
        
    }

	/**
	 * Remove bloat
	 */
	public function remove_meta_boxes() {
        //remove_meta_box( 'submitdiv', 'property', 'side' );
		remove_meta_box( 'postexcerpt', 'property', 'normal' );
		remove_meta_box( 'pageparentdiv', 'property', 'side' );
		remove_meta_box( 'commentstatusdiv', 'property', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'property', 'side' );
        remove_meta_box( 'commentsdiv', 'product', 'normal' );
	}

	/**
	 * Rename core meta boxes
	 */
	public function rename_meta_boxes() {
		global $post;

		// Comments/Reviews
		if ( isset( $post ) && ( 'publish' == $post->post_status || 'private' == $post->post_status ) ) {
			//remove_meta_box( 'commentsdiv', 'product', 'normal' );

			//add_meta_box( 'commentsdiv', __( 'Reviews', 'propertyhive' ), 'post_comment_meta_box', 'product', 'normal' );
		}
	}

	/**
	 * Check if we're saving, then trigger an action based on the post type
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( $_POST['propertyhive_meta_nonce'], 'propertyhive_save_data' ) ) {
			return;
		}
        
		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check the post type
		if ( 
            ! in_array( 
                $post->post_type, 
                apply_filters( 'propertyhive_post_types_with_tabs', array('property', 'contact', 'enquiry', 'appraisal', 'viewing', 'offer', 'sale', 'tenancy') )
            ) 
        ) {
			return;
		}

        if ( isset($_POST['post_parent']) && $_POST['post_parent'] != '' && $_POST['post_parent'] != '0' )
        {
            global $wpdb;

            $wpdb->update( $wpdb->posts, array( 'post_parent' => (int)$_POST['post_parent'] ), array( 'ID' => $post_id ) );
        }

		do_action( 'propertyhive_process_' . $post->post_type . '_meta', $post_id, $post );
	}

}

new PH_Admin_Meta_Boxes();

}