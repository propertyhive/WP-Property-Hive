<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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

        if ( 'property_type' == $key ) 
        {
            return $this->get_property_type();
        }
        if ( 'availability' == $key ) 
        {
            return $this->get_availability();
        }
        if ( 'location' == $key ) 
        {
            return $this->get_location();
        }
        if ( 'price_qualifier' == $key ) 
        {
            return $this->get_price_qualifier();
        }
        if ( 'tenure' == $key ) 
        {
            return $this->get_tenure();
        }
        if ( 'sale_by' == $key ) 
        {
            return $this->get_sale_by();
        }
        if ( 'furnished' == $key ) 
        {
            return $this->get_furnished();
        }
        if ( 'parking' == $key ) 
        {
            return $this->get_parking();
        }
        if ( 'outside_space' == $key ) 
        {
            return $this->get_outside_space();
        }
        if ( 'marketing_flag' == $key ) 
        {
            return $this->get_marketing_flag();
        }
        if ( 'imported_id' == $key ) 
        {
            return $this->get_imported_id();
        }
        if ( 'office_name' == $key ) 
        {
            return $this->get_office_name();
        }
        if ( 'office_address' == $key ) 
        {
            return $this->get_office_address();
        }
        if ( 'office_telephone_number' == $key ) 
        {
            return $this->get_office_telephone_number();
        }
        if ( 'office_email_address' == $key ) 
        {
            return $this->get_office_email_address();
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
        $photos = $this->photos;
        if ( is_array($photos) )
        {
            $photos = array_filter( $photos );
        }
        else
        {
            $photos = array();
        }
        return apply_filters( 'propertyhive_property_gallery_attachment_ids', $photos, $this );
    }
    
    /**
     * Gets the first photo
     *
     * @access public
     * @param string $size
     * @return string
     */
    public function get_main_photo_src( $size = 'thumbnail' ) {
        
        $return = false;

        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photos = $this->_photo_urls;

            if (isset($photos) && is_array($photos) && !empty($photos) && isset($photos[0]) && isset($photos[0]['url']))
            {
                $return = $photos[0]['url'];
            }
        }
        else
        {
            $photos = $this->_photos;
        
            
            
            if (isset($photos) && is_array($photos) && !empty($photos) && isset($photos[0]))
            {
                $image_attributes = wp_get_attachment_image_src( $photos[0], $size );
                if( $image_attributes ) 
                {
                    $return = $image_attributes[0];
                }
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
        $floorplans = $this->_floorplans;
        if ( is_array($floorplans) )
        {
            $floorplans = array_filter( $floorplans );
        }
        else
        {
            $floorplans = array();
        }
        return apply_filters( 'propertyhive_property_floorplan_attachment_ids', $floorplans, $this );
    }
    
    /**
     * get_brochure_attachment_ids function.
     *
     * @access public
     * @return array
     */
    public function get_brochure_attachment_ids() 
    {
        $brochures = $this->_brochures;
        if ( is_array($brochures) )
        {
            $brochures = array_filter( $brochures );
        }
        else
        {
            $brochures = array();
        }
        return apply_filters( 'propertyhive_property_brochure_attachment_ids', $brochures, $this );
    }
    
    /**
     * get_epc_attachment_ids function.
     *
     * @access public
     * @return array
     */
    public function get_epc_attachment_ids() 
    {
        $epcs = $this->_epcs;
        if ( is_array($epcs) )
        {
            $epcs = array_filter( $epcs );
        }
        else
        {
            $epcs = array();
        }
        return apply_filters( 'propertyhive_property_epc_attachment_ids', $epcs, $this );
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
        
        $return = '';

        $currency = array();
        $prefix = '';
        $suffix = '';

        if ( $this->_department == 'commercial' )
        {
            $price = '';

            // Price Details
            $price .= $this->get_formatted_commercial_price();

            // Rent Details
            $rent = $this->get_formatted_commercial_rent();
            if ( $price != '' && $rent != '' )
            {
                $price .= '<br>';
            }
            $price .= $rent;

            $return = $price;
        }
        else
        {
            if ( ( !is_admin() || ( is_admin() && defined('DOING_AJAX') && DOING_AJAX ) ) && $this->_poa == 'yes')
            {
                $return = __( 'POA', 'propertyhive' );
            }
            else
            {
                $ph_countries = new PH_Countries();

                if ( !is_admin() )
                {
                    if ( isset($_GET['currency']) )
                    {
                        if ( $_GET['currency'] != '' )
                        {
                            $requested_currency = $ph_countries->get_currency( sanitize_text_field($_GET['currency']) );
                            if ( $requested_currency !== FALSE )
                            {
                                $currency = $requested_currency;
                                $currency['exchange_rate'] = 1;
                                $exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );
                                if ( isset($exchange_rates[$_GET['currency']]) )
                                {
                                    $currency['exchange_rate'] = $exchange_rates[sanitize_text_field($_GET['currency'])];
                                }
                            }
                        }
                        else
                        {
                            $default_currency = apply_filters( 'propertyhive_default_display_currency', '' );
                            if ( $default_currency != '' )
                            {
                                $requested_currency = $ph_countries->get_currency( $default_currency );
                                if ( $requested_currency !== FALSE )
                                {
                                    $currency = $requested_currency;
                                    $currency['exchange_rate'] = 1;
                                    $exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );
                                    if ( isset($exchange_rates[$default_currency]) )
                                    {
                                        $currency['exchange_rate'] = $exchange_rates[$default_currency];
                                    }
                                }
                            }
                        }
                    }
                    elseif ( isset($_COOKIE['propertyhive_currency']) && $_COOKIE['propertyhive_currency'] != '' )
                    {
                        $currency = unserialize(html_entity_decode($_COOKIE['propertyhive_currency']));
                    }
                    else
                    {
                        $default_currency = apply_filters( 'propertyhive_default_display_currency', '' );
                        if ( $default_currency != '' )
                        {
                            $requested_currency = $ph_countries->get_currency( $default_currency );
                            if ( $requested_currency !== FALSE )
                            {
                                $currency = $requested_currency;
                                $currency['exchange_rate'] = 1;
                                $exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );
                                if ( isset($exchange_rates[$default_currency]) )
                                {
                                    $currency['exchange_rate'] = $exchange_rates[$default_currency];
                                }
                            }
                        }
                    }
                }

                if ( empty($currency) )
                {
                    if ($this->_currency != '')
                    {
                        $currency = $ph_countries->get_currency( $this->_currency );
                    }
                    else
                    {
                        $currency = $ph_countries->get_currency( 'GBP' );
                    }
                }
                $prefix = ( ($currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
                $suffix = ( (!$currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
                switch ($this->_department)
                {
                    case "residential-sales":
                    {
                        $price = $this->_price;
                        if ( isset($currency['exchange_rate']) && $price != '' )
                        {
                            $price = $this->_price_actual * $currency['exchange_rate'];
                        }
                        $return = ( ( $price != '' ) ? $prefix . number_format($price, 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix : '-' );
                        break;
                    }
                    case "residential-lettings":
                    {
                        $price = $this->_rent;
                        if ( isset($currency['exchange_rate']) && $price != '' )
                        {
                            $price = $this->_price_actual * $currency['exchange_rate'];
                            switch ( $this->_rent_frequency )
                            {
                                case "pw": { $price = ($price * 12) / 52; break; }
                                case "pq": { $price = ($price * 12) / 4; break; }
                                case "pa": { $price = ($price * 12); break; }
                            }
                        }
                        $return = ( ( $price != '' ) ? $prefix . number_format($price, 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix . ' ' . __( $this->_rent_frequency, 'propertyhive' ) : '-' );
                        break;
                    }
                }
            }
        }
        
        return apply_filters( 'propertyhive_price_output', $return, $this, $currency, $prefix, $suffix );
    }

    /**
     * Get the formatted commercial price. Show POA if on frontend and 'POA' ticked
     *
     * @access public
     * @return string
     */
    public function get_formatted_commercial_price( ) {

        $price = '';

        if ( $this->_for_sale == 'yes' )
        {
            if ( !is_admin() && $this->_price_poa == 'yes' )
            {
                $price .= __( 'POA', 'propertyhive' );
            }
            else
            {
                $ph_countries = new PH_Countries();
                if ( $this->_commercial_price_currency != '' )
                {
                    $currency = $ph_countries->get_currency( $this->_commercial_price_currency );
                }
                else
                {
                    $currency = $ph_countries->get_currency( 'GBP' );
                }
                $prefix = ( ($currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
                $suffix = ( (!$currency['currency_prefix']) ? $currency['currency_symbol'] : '' );

                if ( $this->_price_from != '' )
                {
                    $explode_price = explode(".", $this->_price_from);
                    if ( count($explode_price) == 2 )
                    {
                        $price .= $prefix . number_format($explode_price[0], 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . get_option('propertyhive_price_decimal_separator', '.') . $explode_price[1] . $suffix;
                    }
                    else
                    {
                        $price .= $prefix . number_format($this->_price_from, 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix;
                    }
                }
                if ( $this->_price_to != '' && $this->_price_to != $this->_price_from )
                {
                    if ( $price != '' )
                    {
                        $price .= ' - ';
                    }
                    $explode_price = explode(".", $this->_price_to);
                    if ( count($explode_price) == 2 )
                    {
                        $price .= $prefix . number_format($explode_price[0], 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . get_option('propertyhive_price_decimal_separator', '.') . $explode_price[1] . $suffix;
                    }
                    else
                    {
                        $price .= $prefix . number_format($this->_price_to, 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix;
                    }
                }
                if ( $price != '' )
                {
                    $price_units = get_commercial_price_units( );
                    $price .= ( isset($price_units[$this->_price_units]) ) ? ' ' . $price_units[$this->_price_units] : '';
                }
            }
        }

        return $price;
    }

    /**
     * Get the formatted commercial rent. Show POA if on frontend and 'POA' ticked
     *
     * @access public
     * @return string
     */
    public function get_formatted_commercial_rent( ) {

        $rent = '';

        if ( $this->_to_rent == 'yes' )
        {
            if ( !is_admin() && $this->_rent_poa == 'yes' )
            {
                $rent .= __( 'POA', 'propertyhive' );
            }
            else
            {
                $ph_countries = new PH_Countries();
                if ( $this->_commercial_rent_currency != '' )
                {
                    $currency = $ph_countries->get_currency( $this->_commercial_rent_currency );
                }
                else
                {
                    $currency = $ph_countries->get_currency( 'GBP' );
                }
                $prefix = ( ($currency['currency_prefix']) ? $currency['currency_symbol'] : '' );
                $suffix = ( (!$currency['currency_prefix']) ? $currency['currency_symbol'] : '' );

                if ( $this->_rent_from != '' )
                {
                    $explode_rent = explode(".", $this->_rent_from);
                    if ( count($explode_rent) == 2 )
                    {
                        $rent .= $prefix . number_format($explode_rent[0], 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . get_option('propertyhive_price_decimal_separator', '.') . $explode_rent[1] . $suffix;
                    }
                    else
                    {
                        $rent .= $prefix . number_format($this->_rent_from, 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix;
                    }
                }
                if ( $this->_rent_to != '' && $this->_rent_to != $this->_rent_from )
                {
                    if ( $rent != '' )
                    {
                        $rent .= ' - ';
                    }
                    $explode_rent = explode(".", $this->_rent_to);
                    if ( count($explode_rent) == 2 )
                    {
                        $rent .= $prefix . number_format($explode_rent[0], 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . get_option('propertyhive_price_decimal_separator', '.') . $explode_rent[1] . $suffix;
                    }
                    else
                    {
                        $rent .= $prefix . number_format($this->_rent_to, 0, get_option('propertyhive_price_decimal_separator', '.'), get_option('propertyhive_price_thousand_separator', ',')) . $suffix;
                    }
                }
                if ( $rent != '' )
                {
                    $price_units = get_commercial_price_units( );
                    $rent .= ' ' . ( isset($price_units[$this->_rent_units]) ? $price_units[$this->_rent_units] : $this->_rent_units );
                }
            }
        }

        return $rent;
    }

    public function get_formatted_floor_area( ) {
        
        $area = '';

        if ( $this->_floor_area_from != '' )
        {
            $explode_area = explode(".", $this->_floor_area_from);
            if ( count($explode_area) == 2 )
            {
                $area .= number_format($explode_area[0], 0) . '.' . $explode_area[1];
            }
            else
            {
                $area .=  number_format($this->_floor_area_from, 0);
            }
        }
        if ( $this->_floor_area_to != '' && $this->_floor_area_to != $this->_floor_area_from )
        {
            if ( $area != '' )
            {
                $area .= ' - ';
            }
            $explode_area = explode(".", $this->_floor_area_to);
            if ( count($explode_area) == 2 )
            {
                $area .= number_format($explode_area[0], 0) . '.' . $explode_area[1];
            }
            else
            {
                $area .=  number_format($this->_floor_area_to, 0);
            }
        }

        if ( $area != '' )
        {
            $area_units = get_area_units( );
            $area .= ( isset($area_units[$this->_floor_area_units]) ) ? ' ' . $area_units[$this->_floor_area_units] : '';
        }

        return $area;

    }

    public function get_formatted_site_area( ) {
        
        $area = '';

        if ( $this->_site_area_from != '' )
        {
            $explode_area = explode(".", $this->_site_area_from);
            if ( count($explode_area) == 2 )
            {
                $area .= number_format($explode_area[0], 0) . '.' . $explode_area[1];
            }
            else
            {
                $area .=  number_format($this->_site_area_from, 0);
            }
        }
        if ( $this->_site_area_to != '' && $this->_site_area_to != $this->_site_area_from )
        {
            if ( $area != '' )
            {
                $area .= ' - ';
            }
            $explode_area = explode(".", $this->_site_area_to);
            if ( count($explode_area) == 2 )
            {
                $area .= number_format($explode_area[0], 0) . '.' . $explode_area[1];
            }
            else
            {
                $area .=  number_format($this->_site_area_to, 0);
            }
        }

        if ( $area != '' )
        {
            $area_units = get_area_units( );
            $area .= ( isset($area_units[$this->_site_area_units]) ) ? ' ' . $area_units[$this->_site_area_units] : '';
        }

        return $area;

    }

    /**
     * Get the formatted deposit
     *
     * @access public
     * @return string
     */
    public function get_formatted_deposit( ) 
    {
        if ( $this->_deposit == '' )
        {
            return '';
        }

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
     * Get the full description by constructing the rooms or commercial description (dependant on department)
     *
     * @access public
     * @return string
     */
    public function get_formatted_description( ) {

        if ( $this->_department == 'commercial' )
        {
            return $this->get_formatted_descriptions(); // Haven't called this commercial_descriptions as we might use generic descriptions for other area going forward
        }
        else
        {
            return $this->get_formatted_rooms();
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
                $return .= str_replace("\r\n", "", nl2br($this->{'_room_description_' . $i})) . '</p>';
            }
        }
        
        return $return;
    }

    /**
     * Get the full description by constructing the descriptions
     *
     * @access public
     * @return string
     */
    public function get_formatted_descriptions( ) {
        
        $descriptions = $this->_descriptions;
        
        $return = '';
        
        if (isset($descriptions) && $descriptions != '' && $descriptions > 0)
        {
            for ($i = 0; $i < $descriptions; ++$i)
            {
                $return .= '<p class="description-section">';
                if ($this->{'_description_name_' . $i} != '')
                {
                    $return .= '<strong class="description-title">' . $this->{'_description_name_' . $i} . '</strong>';
                }
                if ($this->{'_description_' . $i} != '')
                {
                    $return .= '<br>';
                }
                $return .= str_replace("\r\n", "", nl2br($this->{'_description_' . $i})) . '</p>';
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
        $term_list = wp_get_post_terms($this->id, ( ( $this->_department == 'commercial' ) ? 'commercial_' : '' ) . 'property_type', array("fields" => "names"));
        
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
     * Get the location taxononmy
     *
     * @access public
     * @return string
     */
    public function get_location()
    {
        $term_list = wp_get_post_terms($this->id, 'location', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the price qualifier taxononmy
     *
     * @access public
     * @return string
     */
    public function get_price_qualifier()
    {
        $term_list = wp_get_post_terms($this->id, 'price_qualifier', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the tenure taxononmy
     *
     * @access public
     * @return string
     */
    public function get_tenure()
    {
        $term_list = wp_get_post_terms($this->id, 'tenure', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the sale by taxononmy
     *
     * @access public
     * @return string
     */
    public function get_sale_by()
    {
        $term_list = wp_get_post_terms($this->id, 'sale_by', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the furnished taxononmy
     *
     * @access public
     * @return string
     */
    public function get_furnished()
    {
        $term_list = wp_get_post_terms($this->id, 'furnished', array("fields" => "names"));
        
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

    /**
     * Get the marketing flag taxononmy
     *
     * @access public
     * @return string
     */
    public function get_marketing_flag()
    {
        $term_list = wp_get_post_terms($this->id, 'marketing_flag', array("fields" => "names"));
        
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            return implode(", ", $term_list);
        }
        
        return '';
    }

    /**
     * Get the ID of property from third party system
     *
     * @access public
     * @return string
     */
    public function get_imported_id()
    {
        global $wpdb;

        $row = $wpdb->get_row(
            "
            SELECT meta_value 
            FROM {$wpdb->prefix}postmeta 
            WHERE 
                meta_key LIKE '_imported_ref_%'
            AND
                post_id = '" . $this->id . "'
            LIMIT 1
            ",
            ARRAY_A
        );

        if ( null !== $row ) 
        {
            return $row['meta_value'];
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
        
        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            $term_list = wp_get_post_terms($this->id, 'property_feature', array("fields" => "names"));
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                foreach ( $term_list as $term_name )
                {
                    $features[] = trim($term_name);
                }
            }
        }
        else
        {
            $num_property_features = $this->_features;
            if ($num_property_features == '') { $num_property_features = 0; }
            
            for ($i = 0; $i < $num_property_features; ++$i)
            {   
                if ( trim($this->{'_feature_' . $i}) != '' )
                {
                    $features[] = trim($this->{'_feature_' . $i});
                }
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

        if ( $return == '' )
        {
            $return = $this->post_title;
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

        if ( $return == '' )
        {
            $return = $this->post_title;
        }
        
        return $return;
    }

    public function get_office_name()
    {
        return get_the_title( $this->_office_id );
    }

    public function get_office_address( $separator = ', ' )
    {
        $return = '';
        
        for ( $i = 1; $i <= 4; ++$i )
        {
            $address = get_post_meta( $this->_office_id, '_office_address_' . $i, TRUE );
            if ($address != '')
            {
                if ($return != '') { $return .= $separator; }
                $return .= $address;
            }
        }
        $address = get_post_meta( $this->_office_id, '_office_address_postcode', TRUE );
        if ($address != '')
        {
            if ($return != '') { $return .= $separator; }
            $return .= $address;
        }

        return $return;
    }

    public function get_office_telephone_number()
    {
        return get_post_meta( $this->_office_id, '_office_telephone_number_' . ( str_replace("residential-", "", $this->_department) ), TRUE );
    }

    public function get_office_email_address()
    {
        return get_post_meta( $this->_office_id, '_office_email_address_' . ( str_replace("residential-", "", $this->_department) ), TRUE );
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
