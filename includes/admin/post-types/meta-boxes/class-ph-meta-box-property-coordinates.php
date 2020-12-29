<?php
/**
 * Property Coordinates
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Address
 */
class PH_Meta_Box_Property_Coordinates {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $wp_query, $thepostid;

        $parent_post = false;
        if ( isset($_GET['post_parent']) && $_GET['post_parent'] != '' )
        {
            $parent_post = (int)$_GET['post_parent'];
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $latitude = get_post_meta($thepostid, '_latitude', TRUE);
        $longitude = get_post_meta($thepostid, '_longitude', TRUE);
        
        $args = array( 
            'id' => '_latitude', 
            'label' => __( 'Latitude', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $latitude = get_post_meta( $parent_post, '_latitude', TRUE );
            $args['value'] = $latitude;
        }
        propertyhive_wp_text_input( $args );
        
        $args = array( 
            'id' => '_longitude', 
            'label' => __( 'Longitude', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        );
        if ( $parent_post !== FALSE )
        {
            $longitude = get_post_meta( $parent_post, '_longitude', TRUE );
            $args['value'] = $longitude;
        }
        propertyhive_wp_text_input( $args );
        
        echo '<p class="form-field">
            <label>&nbsp;</label>
            <a href="#" onclick="do_address_lookup( true ); return false;">' . __( 'Obtain Co-ordinates', 'propertyhive' ) . '</a>
        </p>';

        do_action('propertyhive_property_coordinates_fields');

        echo '<div class="map_canvas" id="map_canvas" style="height:350px;"></div>';
        
        
        $zoom = 16;
        
        $markerSet = true;
        if ($latitude == '' || $longitude == '' || $latitude == '0' || $longitude == '0')
        {
            // No lat,lng. Default to whole of UK
            $latitude = 54.617959;
            $longitude = -3.66309;
            $zoom = 5;
            
            $markerSet = false;
        }
        
        echo '<small id="help-marker-not-set" style="display:' . ( ($markerSet) ? 'none' : 'block') . ';">' . __('Manually enter the property\'s co-ordinates, or click on the map to specify the exact location.', 'propertyhive') . '</small>';
        echo '<small id="help-marker-set" style="display:' . ( (!$markerSet) ? 'none' : 'block') . ';">' . __('Edit the co-ordinates by manually entering them, or click and drag the marker.', 'propertyhive') . '</small>';
        
        echo '</div>';
        
        echo '</div>';
        
        echo '
            <script>

                var map;
                var marker;
                var markerSet = ' . ( ($markerSet) ? 'true' : 'false') . ';
                var geocoder;

                jQuery(document).ready(function()
                {
                    jQuery(\'#_address_postcode\').change(function()
                    {
                        do_address_lookup();
                    });
                    jQuery(\'#_address_country\').change(function()
                    {
                        do_address_lookup();
                    });

                });

                function do_address_lookup( force )
                {
                    var force = force || false;

                    if ((!markerSet || force) && (jQuery(\'#_address_postcode\').val() != \'\' || force) && jQuery(\'#_address_country\').val() != \'\')
                    {
                        var address = jQuery(\'#_address_postcode\').val();
                        var location_filter = \'\';
                        if ( jQuery(\'#_address_postcode\').val() != \'\' )
                        {
                            location_filter = jQuery(\'#_address_postcode\').val().split(" ");
                            location_filter = location_filter[0];
                        }
                        if (jQuery(\'#_address_four\').val() != \'\')
                        {
                            address = jQuery(\'#_address_four\').val() + \', \' + address;
                        }
                        if (jQuery(\'#_address_three\').val() != \'\')
                        {
                            address = jQuery(\'#_address_three\').val() + \', \' + address;
                        }
                        if (jQuery(\'#_address_two\').val() != \'\')
                        {
                            address = jQuery(\'#_address_two\').val() + \', \' + address;
                        }
                        if (jQuery(\'#_address_street\').val() != \'\')
                        {
                            address = jQuery(\'#_address_street\').val() + \', \' + address;
                        }
                        if (jQuery(\'#_address_name_number\').val() != \'\')
                        {
                            address = jQuery(\'#_address_name_number\').val() + \' \' + address;
                        }
                        if (jQuery(\'#_address_country\').val() != \'\')
                        {
                            address = address + \', \' + jQuery(\'#_address_country\').val();
                        }

                        var geocoding_data = { \'address\': address };
                        if ( location_filter != \'\' )
                        {   
                            // Removed as for some reason it was generating a lot of ZERO_RESULTS_FOUND errors
                            /*geocoding_data.componentRestrictions = {
                                postalCode : location_filter
                            }*/
                        }
                        
                        geocoder.geocode( geocoding_data, geocode_callback );
                    }
                }

            </script>
        ';

        if ( get_option('propertyhive_maps_provider') == 'osm' )
        {
            echo '
            <script>

                function geocode_callback( results, status )
                {
                    if (status == google.maps.GeocoderStatus.OK) 
                    {
                        map.setZoom(16);
                        
                        jQuery(\'#_latitude\').val(results[0].geometry.location.lat());
                        jQuery(\'#_longitude\').val(results[0].geometry.location.lng());
                        
                        if ( marker != null )
                        {
                            marker.remove();
                        }

                        marker = L.marker([results[0].geometry.location.lat(), results[0].geometry.location.lng()], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);

                        map.panTo([results[0].geometry.location.lat(), results[0].geometry.location.lng()]);

                        jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                        {
                            jQuery(\'#help-marker-set\').fadeIn();
                        });
                    }
                    else
                    {
                        alert(\'Geocode was not successful for the following reason: \' + status);
                    }
                }

                function ph_initialize() {
                        
                    geocoder = new google.maps.Geocoder();

                    map = L.map("map_canvas").setView([' . $latitude . ', ' . $longitude . '], ' . $zoom . ');

                    L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                        attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
                        maxZoom: 19,
                    }).addTo(map);

                    if (markerSet)
                    {
                        marker = L.marker([' . $latitude . ', ' . $longitude . '], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);
                    }

                    map.on(\'click\', function(e){
                        if ( marker != null )
                        {
                            marker.remove();
                        }
                        marker = L.marker(e.latlng, { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);
                        jQuery(\'#_latitude\').val(e.latlng.lat);
                        jQuery(\'#_longitude\').val(e.latlng.lng);

                        jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                        {
                            jQuery(\'#help-marker-set\').fadeIn();
                        });
                    });
                }

                function marker_move_end(e)
                {
                    jQuery(\'#_latitude\').val(e.target._latlng.lat);
                    jQuery(\'#_longitude\').val(e.target._latlng.lng);
                }

                jQuery(document).ready(function()
                {
                    // Watch for lat lng changing
                    jQuery(\'#_latitude\').keyup(function()
                    {
                        var latitude = jQuery(this).val();
                        var longitude = jQuery(\'#_longitude\').val();
                        
                        if ( latitude != \'\' && longitude != \'\' && latitude != \'0\' && longitude != \'0\' )
                        {
                            // Both lat and lng exist
                            map.setZoom(16);

                            if ( marker != null )
                            {
                                marker.remove();
                            }
                            
                            marker = L.marker([latitude, longitude], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);

                            map.panTo( [latitude, longitude] );
                        }
                    });
                    
                    jQuery(\'#_longitude\').keyup(function()
                    {
                        var latitude = jQuery(\'#_latitude\').val();
                        var longitude = jQuery(this).val();
                        
                        if ( latitude != \'\' && longitude != \'\' && latitude != \'0\' && longitude != \'0\' )
                        {
                            // Both lat and lng exist
                            map.setZoom(16);
                            
                            if ( marker != null )
                            {
                                marker.remove();
                            }
                            
                            marker = L.marker([latitude, longitude], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);
                            
                            map.panTo( [latitude, longitude] );
                        }
                    });
                });

            </script>
            ';
        }
        else
        {
            echo '
            <script>
            
                function geocode_callback( results, status )
                {
                    if (status == google.maps.GeocoderStatus.OK) 
                    {
                        map.panTo(results[0].geometry.location);
                        
                        map.setZoom(16);
                        
                        jQuery(\'#_latitude\').val(results[0].geometry.location.lat());
                        jQuery(\'#_longitude\').val(results[0].geometry.location.lng());
                        
                        marker = ph_create_marker(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                    }
                    else
                    {
                        alert(\'Geocode was not successful for the following reason: \' + status);
                    }
                }

                function ph_initialize() {
                        
                    geocoder = new google.maps.Geocoder();
                    
                    var starting_lat_lng = new google.maps.LatLng(' . $latitude . ', ' . $longitude . ');
                    var mapOptions = {
                      center: starting_lat_lng,
                      zoom: ' . $zoom . ',
                      scrollwheel: false 
                    };
                    map = new google.maps.Map(document.getElementById(\'map_canvas\'), mapOptions);
                    
                    if (markerSet)
                    {
                        // To add the marker to the map, use the \'map\' property
                        marker = ph_create_marker(' . $latitude . ', ' . $longitude . ');
                    }

                    google.maps.event.addListener(map, \'click\', function(event) 
                    {
                        marker = ph_create_marker(event.latLng.lat(), event.latLng.lng());
                        jQuery(\'#_latitude\').val(event.latLng.lat());
                        jQuery(\'#_longitude\').val(event.latLng.lng());
                    });
                }

                jQuery(document).ready(function()
                {
                    // Watch for lat lng changing
                    jQuery(\'#_latitude\').keyup(function()
                    {
                        var latitude = jQuery(this).val();
                        var longitude = jQuery(\'#_longitude\').val();
                        
                        if ( latitude != \'\' && longitude != \'\' && latitude != \'0\' && longitude != \'0\' )
                        {
                            // Both lat and lng exist
                            map.setZoom(16);
                            
                            if (!markerSet)
                            {
                                marker = ph_create_marker(latitude, longitude);

                                markerSet = true;
                            }
                            else
                            {
                                marker.setPosition( new google.maps.LatLng( latitude, longitude ) );
                            }
                            map.panTo( new google.maps.LatLng( latitude, longitude ) );
                        }
                    });
                    
                    jQuery(\'#_longitude\').keyup(function()
                    {
                        var latitude = jQuery(\'#_latitude\').val();
                        var longitude = jQuery(this).val();
                        
                        if ( latitude != \'\' && longitude != \'\' && latitude != \'0\' && longitude != \'0\' )
                        {
                            // Both lat and lng exist
                            map.setZoom(16);
                            
                            if (!markerSet)
                            {
                                marker = ph_create_marker(latitude, longitude);

                                markerSet = true;
                            }
                            else
                            {
                                marker.setPosition( new google.maps.LatLng( latitude, longitude ) );
                            }
                            map.panTo( new google.maps.LatLng( latitude, longitude ) );
                        }
                    });
                });

                function ph_create_marker(lat, lng)
                {
                    if ( marker != null )
                    {
                        marker.setMap(null);
                    }

                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(lat, lng),
                        map: map,
                        draggable: true,
                        title: \''. __( 'Click and drag me to set the exact coordinates', 'propertyhive') . '\'
                    });
                    
                    jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                    {
                        jQuery(\'#help-marker-set\').fadeIn();
                    });
                    
                    google.maps.event.addListener(marker, \'dragend\', function() 
                    {
                        var newPosition = marker.getPosition();
                        jQuery(\'#_latitude\').val(newPosition.lat());
                        jQuery(\'#_longitude\').val(newPosition.lng());
                    });
                    
                    return marker;
                }
            
            </script>
            ';
        }

        echo '
            <script>
                if (window.addEventListener) {
                    window.addEventListener(\'load\', ph_initialize);
                }else{
                    window.attachEvent(\'onload\', ph_initialize);
                }
            </script>
        ';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_latitude', ph_clean($_POST['_latitude']) );
        update_post_meta( $post_id, '_longitude', ph_clean($_POST['_longitude']) );

        do_action('propertyhive_save_property_coordinates', $post_id);
    }

}
