<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Viewing
 *
 * The Property Hive viewing class handles viewing data.
 *
 * @class       PH_Viewing
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Viewing {

    /** @public int Viewing (post) ID */
    public $id;

    /**
     * Get the viewing if ID is passed, otherwise the viewing is new and empty.
     *
     * @access public
     * @param string $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id > 0 ) {
            $this->get_viewing( $id );
        }
    }

    /**
     * Gets a viewing from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_viewing( $id = 0 ) {
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

    public function get_related_viewings()
    {
        $related_viewings = array(
            'previous' => array(),
            'next' => array(),
            'all' => array()
        );

        $applicant_contact_ids = get_post_meta($this->id, '_applicant_contact_id');
        $applicant_contact_ids = !empty($applicant_contact_ids) ? ( is_array($applicant_contact_ids) ? $applicant_contact_ids : array($applicant_contact_ids) ) : array();

        $property_id = get_post_meta($this->id, '_property_id', TRUE);
        $property_id = !empty($property_id) ? $property_id : '';

        $primary_viewing_start_date_time = get_post_meta($this->id, '_start_date_time', TRUE);

        $meta_query = array(
            array(
                'key' => '_property_id',
                'value' => (int)$property_id,
            ),
            array(
                'key' => '_status',
                'value' => 'cancelled',
                'compare' => '!='
            ),
        );
        foreach ( $applicant_contact_ids as $applicant_contact_id )
        {
            $meta_query[] = array(
                'key' => '_applicant_contact_id',
                'value' => (int)$applicant_contact_id
            );
        }

        $args = array(
            'fields'   => 'ids',
            'post_type' => 'viewing',
            'nopaging'  => true,
            'post_status' => 'publish',
            'meta_query' => $meta_query,
            'post__not_in' => array($this->id),
            'orderby' => 'none'
        );

        $viewings_query = new WP_Query( $args );

        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();

                $viewing_start_date_time = get_post_meta(get_the_ID(), '_start_date_time', TRUE);

                if ( strtotime($viewing_start_date_time) <= strtotime($primary_viewing_start_date_time) )
                {
                    $related_viewings['previous'][] = get_the_ID();
                }
                else
                {
                    $related_viewings['next'][] = get_the_ID();
                }

                $related_viewings['all'][] = get_the_ID();
            }
        }
        wp_reset_postdata();

        return $related_viewings;
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
}
