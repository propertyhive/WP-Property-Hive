<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Enquiry
 *
 * The PropertyHive enquiry class handles enquiry data.
 *
 * @class       PH_Property
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Enquiry {

    /** @public int Enquiry (post) ID */
    public $id;

    /**
     * Get the enquiry if ID is passed, otherwise the enquiry is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id > 0 ) {
            $this->get_enquiry( $id );
        }
    }

    /**
     * Gets a enquiry from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_enquiry( $id = 0 ) {
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
     * Populates a enquiry from the loaded post data.
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
     * Get the assigned negotiator
     *
     * @access public
     * @return string
     */
    public function get_negotiator()
    {
        if ($this->_negotiator_id == '' || $this->_negotiator_id == 0)
        {
            return '<em>-- ' . __( 'Unassigned', 'propertyhive' ) . ' --</em>';
        }
        else
        {
            $userdata = get_userdata( $this->_negotiator_id );
            if ( $userdata !== FALSE )
            {
                return $userdata->display_name;
            }
            else
            {
                return '<em>Unknown user</em>';
            }
        }
    }

    /**
     * Get the assigned office
     *
     * @access public
     * @return string
     */
    public function get_office()
    {
        if ($this->_office_id == '' || $this->_office_id == 0)
        {
            return '<em>-- ' . __( 'Unassigned', 'propertyhive' ) . ' --</em>';
        }
        else
        {
            return get_the_title( $this->_office_id );
        }
    }
}
