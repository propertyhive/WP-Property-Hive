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

    /**
     * Get the applicants
     *
     * @access public
     * @return string
     */
    public function get_applicants( $add_hyperlinks = false, $additional_contact_details = false )
    {
        $applicant_contact_ids = get_post_meta($this->id, '_applicant_contact_id');
        if (!empty($applicant_contact_ids))
        {
            $applicant_contacts = array();
            foreach ($applicant_contact_ids as $applicant_contact_id)
            {
                $applicant_name = get_the_title($applicant_contact_id);
                if ( $add_hyperlinks )
                {
                    $edit_link = get_edit_post_link( $applicant_contact_id );
                    $applicant_name = '<a href="' . esc_url($edit_link) . '" target="' . esc_attr(apply_filters('propertyhive_subgrid_link_target', '')) . '">' . $applicant_name . '</a>';
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

                    $contact_details = apply_filters( 'propertyhive_viewing_applicant_contact_details', $contact_details, $applicant_contact_id );

                    if ( !empty($contact_details) )
                    {
                        $applicant_name .= '<div class="row-actions visible">' . implode( "<br>", $contact_details ) . '</div>';
                    }
                }
                $applicant_contacts[] = $applicant_name;
            }
            return implode('<br>', $applicant_contacts);
        }
        else
        {
            return'-';
        }
    }

    /**
     * Get the attending negotiator(s)
     *
     * @access public
     * @return string
     */
    public function get_negotiators()
    {
        $negotiator_ids = get_post_meta($this->id, '_negotiator_id');
        if ( !empty($negotiator_ids) )
        {
            $negotiators = array();
            foreach ($negotiator_ids as $negotiator_id)
            {
                $userdata = get_userdata( $negotiator_id );
                if ( $userdata !== FALSE )
                {
                    $negotiators[] = $userdata->display_name;
                }
                else
                {
                    $negotiators[] = '<em>' . __( 'Unknown user', 'propertyhive' ) . '</em>';
                }
            }
            return implode(', ', $negotiators);
        }
        else
        {
            return 'Unattended';
        }
    }

    /**
     * Get the viewing status
     *
     * @access public
     * @return string
     */
    public function get_status()
    {
        $status = $this->_status;
        $status_items = array( __( ucwords(str_replace("_", " ", $status)), 'propertyhive' ) );

        if ( $status == 'pending' )
        {
            // confirmation status
            if ( $this->_all_confirmed == 'yes' )
            {
                $status_items[] = __( 'All Parties Confirmed', 'propertyhive' );
            }
            else
            {
                $status_items[] = __( 'Awaiting Confirmation', 'propertyhive' );
            }
        }

        if ( $status == 'carried_out' )
        {
            $feedback_status = $this->_feedback_status;
            switch ( $feedback_status )
            {
                case "interested": { $status_items[] = __( 'Applicant Interested', 'propertyhive' ); break; }
                case "not_interested": { $status_items[] = __( 'Applicant Not Interested', 'propertyhive' ); break; }
                case "not_required": { $status_items[] = __( 'Feedback Not Required', 'propertyhive' ); break; }
                default: { $status_items[] = __( 'Awaiting Feedback', 'propertyhive' ); }
            }

            if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' )
            {
                $status_items[] = ($this->_feedback_passed_on == 'yes') ? __( 'Feedback Passed On', 'propertyhive' ) : __( 'Feedback Not Passed On', 'propertyhive' );
            }
        }

        // Add text if this a second, third etc viewing
        $related_viewings = get_post_meta( $this->id, '_related_viewings', TRUE );
        if ( isset($related_viewings['previous']) && count($related_viewings['previous']) > 0 )
        {
            $status_items[] = ph_ordinal_suffix(count($related_viewings['previous'])+1) . ' ' . __( 'Viewing', 'propertyhive' );
        }

        return implode('<br>', $status_items);
    }

    /**
     * Get the full address of the property attached to the viewing
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
}
