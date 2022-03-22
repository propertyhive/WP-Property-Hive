<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Application
 *
 * The Property Hive application class handles application data.
 *
 * @class       PH_Application
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Application {

    /** @public int Application (post) ID */
    public $id;

    /**
     * Get the application if ID is passed, otherwise the application is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id > 0 ) {
            $this->get_application( $id );
        }
    }

    /**
     * Gets a application from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_application( $id = 0 ) {
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
     * Populates an application from the loaded post data.
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
     * Get the formatted application rent
     *
     * @access public
     * @return string
     */
    public function get_formatted_rent( ) {

        $ph_countries = new PH_Countries();

        if ($this->_currency != '')
        {
            $currency = $ph_countries->get_currency( $this->_currency );
        }
        else
        {
            $currency = $ph_countries->get_currency( 'GBP' );
        }

        $prefix = ( ($currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
        $suffix = ( (!$currency['currency_prefix']) ? $currency['currency_symbol'] : '' );

        $amount = $this->_offered_rent;

        // If there are decimals on the number, display them. If not, display none
		$decimals = (float)$amount == intval($amount) ? 0 : 2;

        return ( ( $amount != '' ) ? $prefix . number_format($amount, $decimals, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) : '-' ) . $suffix . ' ' . __( $this->_rent_frequency, 'propertyhive' );

    }

    /**
     * Get the application status
     *
     * @access public
     * @return string
     */
    public function get_status()
    {
        return __( ucwords(str_replace("_", " ", $this->_status)), 'propertyhive' );;
    }

    /**
     * Get a list of tenants on the property
     *
     * @access public
     * @param bool $add hyperlinks
     * @return string
     */
    public function get_tenants( $add_hyperlinks = false, $additional_contact_details = false )
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
                    $contact_details = array();
                    $telephone_number = get_post_meta( $applicant_contact_id, '_telephone_number', true );
                    if( !empty($telephone_number) )
                    {
                        $contact_details[] = 'T: ' . $telephone_number;
                    }

                    $email_address = get_post_meta( $applicant_contact_id, '_email_address', true );
                    if( !empty($email_address) )
                    {
                        $contact_details[] = 'E: ' . $email_address;
                    }

                    if ( !empty($contact_details) )
                    {
                        $applicant_name .= '<div class="row-actions visible">' . implode( "<br>", $contact_details ) . '</div>';
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
}
