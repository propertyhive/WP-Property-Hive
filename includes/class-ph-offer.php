<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Offer
 *
 * The Property Hive offer class handles offer data.
 *
 * @class       PH_Offer
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Offer {

    /** @public int Offer (post) ID */
    public $id;

    /**
     * Get the offer if ID is passed, otherwise the offer is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id > 0 ) {
            $this->get_offer( $id );
        }
    }

    /**
     * Gets a offer from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_offer( $id = 0 ) {
        if ( ! $id ) {
            return false;
        }
        if ( $result = get_post( $id ) ) {
            $this->populate( $result );
            return true;
        }
        return false;
    }
    
    /**
     * __isset function.
     *
     * @access public
     * @param mixed $key
     * @return bool
     */
    public function __isset( $key ) {
        if ( ! $this->id ) {
            return false;
        }
        return metadata_exists( 'post', $this->id, '_' . $key );
    }

    /**
     * __get function.
     *
     * @access public
     * @param mixed $key
     * @return mixed
     */
    public function __get( $key ) {
        // Get values or default if not set
        $value = get_post_meta( $this->id, $key, true );
        if ($value == '')
        {
            $value = get_post_meta( $this->id, '_' . $key, true );
        }
        return $value;
    }
    
    /**
     * Populates a offer from the loaded post data.
     *
     * @access public
     * @param mixed $result
     * @return void
     */
    public function populate( $result ) {
        // Standard post data
        $this->id                  = $result->ID;
        $this->post_title          = $result->post_title;
        $this->post_status         = $result->post_status;
    }

    /**
     * Get the formatted offer amount
     *
     * @access public
     * @return string
     */
    public function get_formatted_amount( ) {

        $amount = $this->_amount;
        $prefix = '&pound;';
        return ( ( $amount != '' ) ? $prefix . number_format($amount , 0) : '-' );

    }

    /**
     * Get a list of applicants on the offer
     *
     * @access public
     * @param bool $add_hyperlinks
     * @param bool $additional_contact_details
     * @param bool $contact_details_visible
     * @return array
     */
    public function get_applicants( $add_hyperlinks = false, $additional_contact_details = false, $contact_details_visible = true )
    {
        $applicant_contact_ids = get_post_meta( $this->id, '_applicant_contact_id' );
        if ( is_array($applicant_contact_ids) && !empty($applicant_contact_ids) )
        {
            $applicants = array();
            foreach ( $applicant_contact_ids as $applicant_contact_id )
            {
                $applicant_name = get_the_title($applicant_contact_id);
                if ( $add_hyperlinks )
                {
                    $edit_link = get_edit_post_link( $applicant_contact_id );
                    $applicant_name = '<a href="' . esc_url( $edit_link ) . '">' . $applicant_name . '</a>';
                }
                if ( $additional_contact_details )
                {
                    $contact_details = '';
                    $telephone_number = get_post_meta( $applicant_contact_id, '_telephone_number', true );
                    if( !empty($telephone_number) )
                    {
                        $contact_details = 'T: ' . $telephone_number;
                    }

                    $email_address = get_post_meta( $applicant_contact_id, '_email_address', true );
                    if( !empty($email_address) )
                    {
                        $contact_details .= ( $contact_details != '' ) ? '<br>' : '';
                        $contact_details .= 'E: ' . $email_address;
                    }

                    if ( $contact_details != '' )
                    {
                        $applicant_name .= '<div class="row-actions' . ($contact_details_visible ? ' visible' : '') . '">' . $contact_details . '</div>';
                    }
                }
                $applicants[] = $applicant_name;
            }
            return implode("<br>", $applicants);
        }
        else
        {
            return '-';
        }
    }

    public function get_applicant_ids()
    {
        $applicant_contact_ids = get_post_meta( $this->id, '_applicant_contact_id' );
        if ( $applicant_contact_ids == '' )
        {
            $applicant_contact_ids = array();
        }
        if ( !is_array($applicant_contact_ids) && $applicant_contact_ids != '' && $applicant_contact_ids != 0 )
        {
            $applicant_contact_ids = array($applicant_contact_ids);
        }

        return $applicant_contact_ids;
    }

    /**
     * Get the full address of the property attached to the offer
     *
     * @access public
     * @return string
     */
    public function get_property_address()
    {
        $property_id = (int)$this->_property_id;

        if ( !empty($property_id) )
        {
            $property = new PH_Property( $property_id );
            return '<a href="' . get_edit_post_link( $property_id, '' ) . '" target="' . apply_filters('propertyhive_subgrid_link_target', '') . '">' . $property->get_formatted_full_address() . '</a>';
        }
        else
        {
            return '-';
        }
    }

    /**
     * Get the contact details of the owner(s) of the property attached to the offer
     *
     * @access public
     * @return string
     */
    public function get_property_owners()
    {
        $property_owners = '-';

        $property_id = (int)$this->_property_id;

        if ( !empty($property_id) )
        {
            $property = new PH_Property( $property_id );
            $owner_contact_ids = $property->_owner_contact_id;
            if (
                ( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 )
                ||
                ( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
            )
            {
                $property_owners = '';

                if ( !is_array($owner_contact_ids) )
                {
                    $owner_contact_ids = array($owner_contact_ids);
                }

                foreach ( $owner_contact_ids as $owner_contact_id )
                {
                    $property_owners .= $this->formatted_contact_meta_box_data($owner_contact_id, false);
                }
            }
        }

        return $property_owners;
    }

    /**
     * Format contact data for display in a meta box list
     */
    private function formatted_contact_meta_box_data( $contact_post_id, $add_edit_link = true )
    {
        if ( $add_edit_link )
        {
            $contact_text = '<a href="' . get_edit_post_link( $contact_post_id, '' ) . '" target="' . apply_filters('propertyhive_subgrid_link_target', '') . '">' . get_the_title($contact_post_id) . '</a>';
        }
        else
        {
            $contact_text = get_the_title($contact_post_id);
        }

        $telephone_number = get_post_meta( $contact_post_id, '_telephone_number', true );
        if( !empty($telephone_number) )
        {
            $contact_details = 'T: ' . $telephone_number;
        }

        $email_address = get_post_meta( $contact_post_id, '_email_address', true );
        if( !empty($email_address) )
        {
            $contact_details .= ( $contact_details != '' ) ? '<br>' : '';
            $contact_details .= 'E: ' . $email_address;
        }

        if ( $contact_details != '' )
        {
            $contact_text .= '<div class="row-actions visible">' . $contact_details . '</div>';
        }

        return $contact_text;
    }
}
