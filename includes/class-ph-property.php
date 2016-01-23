<?php
/**
 * Property
 *
 * The PropertyHive property class handles property data.
 *
 * @class       PH_Property
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Property {

    /** @public int Property (post) ID */
    public $id;

    /**
     * Get the property if ID is passed, otherwise the property is new and empty.
     *
     * @access public
     * @param string|object $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id != '' ) 
        {
            if ( is_int($id) && $id > 0 )
            {
                
            }
            else
            {
                // Must be post object
                $id = $id->ID;
            }       
            $this->get_property( $id );
        }
    }

    /**
     * Gets a property from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_property( $id = 0 ) {
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
        return metadata_exists( 'post', $this->id, $key );
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
     * Populates a property from the loaded post data.
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
        $this->post_excerpt        = $result->post_excerpt;
    }
    
    /**
     * get_gallery_attachment_ids function.
     *
     * @access public
     * @return array
     */
    public function get_gallery_attachment_ids() 
    {
        return apply_filters( 'propertyhive_property_gallery_attachment_ids', array_filter( $this->photos ), $this );
    }
    
    /**
     * Gets the first photo
     *
     * @access public
     * @param string $size
     * @return string
     */
    public function get_main_photo_src( $size = 'thumbnail' ) {
            
        $photos = $this->_photos;
        
        $return = false;
        
        if (isset($photos) && is_array($photos) && !empty($photos))
        {
            $image_attributes = wp_get_attachment_image_src( $photos[0], $size );
            if( $image_attributes ) 
            {
                $return = $image_attributes[0];
            }
        }
        
        return $return;
    }
    
    /**
     * get_floorplan_attachment_ids function.
     *
     * @access public
     * @return array
     */
    public function get_floorplan_attachment_ids() 
    {
        return apply_filters( 'propertyhive_property_floorplan_attachment_ids', array_filter( $this->_floorplans ), $this );
    }
    
    /**
     * get_brochure_attachment_ids function.
     *
     * @access public
     * @return array
     */
    public function get_brochure_attachment_ids() 
    {
        return apply_filters( 'propertyhive_property_brochure_attachment_ids', array_filter( $this->_brochures ), $this );
    }
    
    /**
     * get_epc_attachment_ids function.
     *
     * @access public
     * @return array
     */
    public function get_epc_attachment_ids() 
    {
        return apply_filters( 'propertyhive_property_epc_attachment_ids', array_filter( $this->_epcs ), $this );
    }

    /**
     * get_virtual_tour_urls function.
     *
     * @access public
     * @return array
     */
    public function get_virtual_tour_urls() 
    {
        $num_property_virtual_tours = get_post_meta($this->id, '_virtual_tours', TRUE);
        if ($num_property_virtual_tours == '') { $num_property_virtual_tours = 0; }

        $virtual_tour_urls = array();
        for ($i = 0; $i < $num_property_virtual_tours; ++$i)
        {
            $virtual_tour_urls[] = get_post_meta($this->id, '_virtual_tour_' . $i, TRUE);
        }

        return apply_filters( 'propertyhive_property_virtual_tour_urls', array_filter( $virtual_tour_urls ), $this );
    }
    
    /**
     * Get the formatted price based on department. Show POA if on frontend and 'POA' ticked
     *
     * @access public
     * @return string
     */
    public function get_formatted_price( ) {
        
        if (!is_admin() && $this->_poa == 'yes')
        {
            return __( 'POA', 'propertyhive' );
        }
        else
        {
            $ph_countries = new PH_Countries();

            $currency = $ph_countries->get_currency( $this->_currency );
            $prefix = ( ($currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
            $suffix = ( (!$currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
            switch ($this->_department)
            {
                case "residential-sales":
                {
                    return ( ( $this->_price != '' ) ? $prefix . number_format($this->_price, 0) . $suffix : '-' );
                    break;
                }
                case "residential-lettings":
                {
                    return ( ( $this->_rent != '' ) ? $prefix . number_format($this->_rent, 0) . $suffix . ' ' . __( $this->_rent_frequency, 'propertyhive' ) : '-' );
                    break;
                }
            }
        }
        
        return '';
    }
    
    /**
     * Get the formatted deposit
     *
     * @access public
     * @return string
     */
    public function get_formatted_deposit( ) 
    {
        $ph_countries = new PH_Countries();

        $currency = $ph_countries->get_currency( $this->_currency );
        $prefix = ( ($currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
        $suffix = ( (!$currency['currency_prefix']) ? $currency['currency_symbol'] : '' );

        return $prefix . number_format($this->_deposit, 0) . $suffix;
    }
    
    /**
     * Get the available date, or 'Now' if date is in past
     *
     * @access public
     * @return string
     */
    public function get_available_date( ) 
    {
        if (strtotime($this->_available_date))
        {
            return date( get_option( 'date_format' ), strtotime($this->_available_date) );
        }
        else
        {
            return __( 'Now', 'propertyhive' );
        }
    }
    
    /**
     * Get the full description by constructing the rooms
     *
     * @access public
     * @return string
     */
    public function get_formatted_rooms( ) {
        
        $rooms = $this->_rooms;
        
        $return = '';
        
        if (isset($rooms) && $rooms != '' && $rooms > 0)
        {
            for ($i = 0; $i < $rooms; ++$i)
            {
                $return .= '<p class="room">';
                if ($this->{'_room_name_' . $i} != '')
                {
                    $return .= '<strong class="name">' . $this->{'_room_name_' . $i} . '</strong>';
                }
                if ($this->{'_room_dimensions_' . $i} != '')
                {  
                    $return .= ' <strong class="dimension">(' . $this->{'_room_dimensions_' . $i} . ')</strong>';
                }
                if ($this->{'_room_name_' . $i} != '' || $this->{'_room_dimensions_' . $i} != '')
                {
                    $return .= '<br>';
                }
                $return .= nl2br($this->{'_room_description_' . $i}) . '
                </p>
                ';
            }
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
        $term_list = wp_get_post_terms($this->id, 'property_type', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the availability taxononmy
     *
     * @access public
     * @return string
     */
    public function get_availability()
    {
        $term_list = wp_get_post_terms($this->id, 'availability', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get an array of property features
     *
     * @access public
     * @return array
     */
    public function get_features( ) 
    {
        $features = array();
        
        $num_property_features = $this->_features;
        if ($num_property_features == '') { $num_property_features = 0; }
        
        for ($i = 0; $i < $num_property_features; ++$i)
        {   
            if ( $this->{'_feature_' . $i} != '' )
            {
                $features[] = $this->{'_feature_' . $i};
            }
        }
        
        return $features;
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
     * Returns boolean whether the property is featured or not
     *
     * @access public
     * @return string
     */
    public function is_featured() {

        if (isset($the_property->_featured) && $the_property->_featured == 'yes')
        {
            return true;
        }

        return false;

    }
    
    /**
     * Adds a note (comment) to the property
     *
     * @access public
     * @param string $note Note to add
     * @return id Comment ID
     */
    public function add_note( $note ) {

        if ( is_user_logged_in() ) 
        {
            $user = get_user_by( 'id', get_current_user_id() );
        
            $commentdata = array(
                'comment_post_ID' => $this->id,
                'comment_author' => $user->display_name,
                'comment_author_email' => $user->user_email,
                'comment_author_url' => '',
                'comment_content' => $note,
                'comment_type' => 'propertyhive_note',
                'comment_parent' => 0,
                'user_id' => $user->ID,
                'comment_agent' => 'PropertyHive',
                'comment_date' => current_time('mysql'),
                'comment_approved' => 1,
            );
            
            $comment_id = wp_insert_comment( $commentdata );
            
            return $comment_id;
        }
    }




}
