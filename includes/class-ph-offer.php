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
}
