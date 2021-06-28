<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Appraisal
 *
 * The Property Hive appraisal class handles appraisal data.
 *
 * @class       PH_Appraisal
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Appraisal {

    /** @public int Appraisal (post) ID */
    public $id;

    /**
     * Get the appraisal if ID is passed, otherwise the appraisal is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id > 0 ) {
            $this->get_appraisal( $id );
        }
    }

    /**
     * Gets a appraisal from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_appraisal( $id = 0 ) {
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

        if ( 'property_type' == $key ) 
        {
            return $this->get_property_type();
        }
        if ( 'parking' == $key ) 
        {
            return $this->get_parking();
        }
        if ( 'outside_space' == $key ) 
        {
            return $this->get_outside_space();
        }

        // Get values or default if not set
        $value = get_post_meta( $this->id, $key, true );
        if ($value == '')
        {
            $value = get_post_meta( $this->id, '_' . $key, true );
        }
        return $value;
    }
    
    /**
     * Populates a viewing from the loaded post data.
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
     * Get the full formatted address
     *
     * @access public
     * @return string
     */
    public function get_formatted_full_address( $separator = ', ' ) {
        // Standard post data
        
        $return = '';
        
        $address_name_number = $this->_address_name_number;
        if ($address_name_number != '')
        {
            $return .= $address_name_number;
        }
        $address_street = $this->_address_street;
        if ($address_street != '')
        {
            if ($return != '') { $return .= ' '; }
            $return .= $address_street;
        }
        $address_two = $this->_address_two;
        if ($address_two != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_two;
        }
        $address_three = $this->_address_three;
        if ($address_three != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_three;
        }
        $address_four = $this->_address_four;
        if ($address_four != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_four;
        }
        $address_postcode = $this->_address_postcode;
        if ($address_postcode != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_postcode;
        }
        
        return $return;
    }

    /**
     * Get the formatted price based on department
     *
     * @access public
     * @return string
     */
    public function get_formatted_price( ) {
        
        $prefix = '&pound;';
        $suffix = '';
        switch ($this->_department)
        {
            case "residential-sales":
            {
                $price = $this->_valued_price;

                // If there are decimals on the number, display them. If not, display none
                $decimals = (float)$price == intval($price) ? 0 : 2;

                return ( ( $price != '' ) ? $prefix . number_format($price, $decimals, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix : '-' );
                break;
            }
            case "residential-lettings":
            {
                $price = $this->_valued_rent;
                if ( $price != '' )
                {
                    switch ( $this->_valued_rent_frequency )
                    {
                        case "pd": { $price = ($price * 365) / 52; break; }
                        case "pw": { $price = ($price * 12) / 52; break; }
                        case "pq": { $price = ($price * 12) / 4; break; }
                        case "pa": { $price = ($price * 12); break; }
                    }
                }

                // If there are decimals on the number, display them. If not, display none
                $decimals = (float)$price == intval($price) ? 0 : 2;

                return ( ( $price != '' ) ? $prefix . number_format($price, $decimals, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix . ' ' . __( $this->_rent_frequency, 'propertyhive' ) : '-' );
                break;
            }
        }
        
        return '';
    }

    /**
     * Get the summary formatted address
     *
     * @access public
     * @return string
     */
    public function get_formatted_summary_address( $separator = ', ' ) {
        // Standard post data
        
        $return = '';
        
        $address_name_number = $this->_address_name_number;
        if ($address_name_number != '')
        {
            $return .= $address_name_number;
        }
        $address_street = $this->_address_street;
        if ($address_street != '')
        {
            if ($return != '') { $return .= ' '; }
            $return .= $address_street;
        }
        $address_two = $this->_address_two;
        $address_three = $this->_address_three;
        $address_four = $this->_address_four;
        if ($address_two != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_two;
        }
        elseif ($address_three != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_three;
        }
        elseif ($address_four != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_four;
        }
        $address_postcode = $this->_address_postcode;
        if ($address_postcode != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address_postcode;
        }
        
        return $return;
    }

    /**
     * Get the property type taxononmy
     *
     * @access public
     * @return string
     */
    public function get_property_type()
    {
        $term_list = wp_get_post_terms($this->id, ( ( $this->_department == 'commercial' ) ? 'commercial_' : '' ) . 'property_type', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the parking taxononmy
     *
     * @access public
     * @return string
     */
    public function get_parking()
    {
        $term_list = wp_get_post_terms($this->id, 'parking', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the outside space taxononmy
     *
     * @access public
     * @return string
     */
    public function get_outside_space()
    {
        $term_list = wp_get_post_terms($this->id, 'outside_space', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }
}
