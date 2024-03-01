<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PropertyHive PH_Address_Keyword_Polygon
 *
 * Polygon Handler
 *
 * @class 		PH_Address_Keyword_Polygon
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Address_Keyword_Polygon {

    /** @var PH_Emails The single instance of the class */
    protected static $_instance = null;

    /** @public array Stores post IDs matching layered nav, so price filter can find max price in view */
    private $address_keyword_polygon_points = array();

    /**
     * Main PH_Emails Instance.
     *
     * Ensures only one instance of PH_Emails is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return PH_Emails Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'propertyhive' ), '1.0.0' );
    }

    /**
     * Constructor for the email class hooks in all emails that can be sent.
     *
     */
    public function __construct() {

    }

    public function get_address_keyword_polygon_coordinates( $address_keyword )
    {
        global $wpdb;

        $address_keyword = trim($address_keyword);

        // see if it exists in the table
        $results = $wpdb->get_results("
            SELECT polygon_coordinates
            FROM " . $wpdb->prefix . "ph_address_keyword_polygon
            WHERE 
                address_keyword = '" . $address_keyword . "'
        ");

        foreach ( $results as $result ) 
        {
            return @unserialize($result->polygon_coordinates, ['allowed_classes' => false]);
        }

        // nothing found in the table. Let's go get it

        $url = 'https://nominatim.openstreetmap.org/search.php?q=' . urlencode($address_keyword) . '&polygon_geojson=1&format=json';

        $response = wp_remote_get( $url );

        if ( is_array( $response ) && ! is_wp_error( $response ) ) 
        {
            $body = $response['body']; // use the content

            $json = json_decode($body, true);

            if ( $json !== FALSE && !empty($json) )
            {
                foreach ( $json as $json_result )
                {
                    if ( 
                        isset($json_result['class']) && in_array($json_result['class'], array( 'boundary' )) && 
                        isset($json_result['geojson']['type']) && $json_result['geojson']['type'] == 'Polygon'
                    )
                    {
                        $polygon_coordinates = array();
                        foreach ( $json_result['geojson']['coordinates'][0] as $coordinate )
                        {
                            $polygon_coordinates[] = $coordinate[1] . ' ' . $coordinate[0];
                        }

                        if ( !empty($polygon_coordinates) )
                        {
                            // Insert into email log
                            $insert = $wpdb->insert( 
                                $wpdb->prefix . 'ph_address_keyword_polygon', 
                                array( 
                                    'address_keyword' => $address_keyword,
                                    'polygon_coordinates' => serialize($polygon_coordinates)
                                ), 
                                array( 
                                    '%s',
                                    '%s'
                                ) 
                            );

                            return $polygon_coordinates;
                        }
                    }
                }
            }
        }

        return false;
    }
}