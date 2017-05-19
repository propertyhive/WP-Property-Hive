<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Contact
 *
 * The Property Hive contact class handles contact data.
 *
 * @class       PH_Contact
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Contact {

    /** @public int Contact (post) ID */
    public $id;

    /**
     * Get the contact if ID is passed, otherwise the contact is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '', $user_id = '' ) {

        if ( $user_id > 0 )
        {
            // We've been passed a user ID. Need to get contact ID based on user_id
            $contact_query = new WP_Query( array( 'post_type' => 'contact', 'meta_key' =>  '_user_id', 'meta_value' => $user_id, 'fields' => 'ids', 'posts_per_page' => 1 ) );

            if ( $contact_query->have_posts() )
            {
                while ( $contact_query->have_posts() )
                {
                    $contact_query->the_post();

                    $this->get_contact( get_the_ID() );
                }
            }

            wp_reset_postdata();
        }
        else
        {
            if ( $id > 0 ) {
                $this->get_contact( $id );
            }
        }
    }

    /**
     * Gets a contact from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_contact( $id = 0 ) {
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
     * Populates a contact from the loaded post data.
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
}
