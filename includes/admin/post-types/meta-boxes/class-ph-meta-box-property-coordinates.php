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
            <a href="#" id="ph-obtain-coords">' . esc_html(__( 'Obtain Co-ordinates', 'propertyhive' )) . '</a>
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
        
        echo '<small id="help-marker-not-set" style="display:' . ( ($markerSet) ? 'none' : 'block') . ';">' . esc_html(__('Manually enter the property\'s co-ordinates, or click on the map to specify the exact location.', 'propertyhive')) . '</small>';
        echo '<small id="help-marker-set" style="display:' . ( (!$markerSet) ? 'none' : 'block') . ';">' . esc_html(__('Edit the co-ordinates by manually entering them, or click and drag the marker.', 'propertyhive')) . '</small>';
        
        echo '</div>';
        
        echo '</div>';
        
        echo '
            <script>

                var map;
                var marker;
                var markerSet = ' . ( ($markerSet) ? 'true' : 'false') . ';
                var geocoder;

                var phGeoTimer = null;
                var phGeoXhr = null;
                var phGeoInFlight = false;

                jQuery(document).ready(function()
                {
                    jQuery(\'#_address_postcode\').change(function()
                    {
                        schedule_lookup(false);
                    });
                    jQuery(\'#_address_country\').change(function()
                    {
                        schedule_lookup(false);
                    });
                    jQuery(\'#ph-obtain-coords\').on(\'click\', function(e)
                    {
                        e.preventDefault();
                        schedule_lookup(true);
                    });
                });

                function schedule_lookup(force)
                {
                    clearTimeout(phGeoTimer);
                    phGeoTimer = setTimeout(function()
                    {
                        do_address_lookup(!!force);
                    }, 800);
                }

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
                        
                        ';

                        if ( get_option('propertyhive_geocoding_provider') == 'mapbox' )
                        {
                            echo '
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
                            ';
                            $mapbox_geocoding_api_key = get_option( 'propertyhive_mapbox_geocoding_api_key', '' );
                            if ( empty($mapbox_geocoding_api_key) )
                            {
                                $mapbox_geocoding_api_key = get_option( 'propertyhive_mapbox_api_key', '' );
                            }
                            echo '
                            var url = \'https://api.mapbox.com/geocoding/v5/mapbox.places/\' + encodeURIComponent(address) + \'.json?access_token=' . $mapbox_geocoding_api_key . '\';
                            ';

                            echo '
                            // Perform the request
                            fetch(url)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.features && data.features.length > 0) 
                                    {
                                        const coordinates = data.features[0].geometry.coordinates;
                                        const lng = coordinates[0]; // Longitude
                                        const lat = coordinates[1]; // Latitude

                                        jQuery(\'#_latitude\').val(lat);
                                        jQuery(\'#_longitude\').val(lng);

                                        ';
                                if ( get_option('propertyhive_maps_provider') == 'mapbox' )
                                {
                                    echo '
                                        if ( marker != null )
                                        {
                                            marker.remove();
                                        }

                                        marker = new mapboxgl.Marker({
                                            //color: "#FFFFFF",
                                            draggable: true
                                        }).setLngLat([lng, lat])
                                            .addTo(map);

                                        marker.on(\'dragend\', marker_move_end);
                                        
                                        map.setCenter([lng, lat]);

                                        jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                                        {
                                            jQuery(\'#help-marker-set\').fadeIn();
                                        });
                                    ';
                                }
                                elseif ( get_option('propertyhive_maps_provider') == 'osm' )
                                {    
                                    echo '
                                        if ( marker != null )
                                        {
                                            marker.remove();
                                        }

                                        marker = L.marker([lat, lng], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);

                                        map.panTo([lat, lng]);

                                        jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                                        {
                                            jQuery(\'#help-marker-set\').fadeIn();
                                        });
                                    ';
                                }
                                else
                                {
                                    echo '
                                        map.panTo(new google.maps.LatLng(lat, lng));
                                        
                                        marker = ph_create_marker(lat, lng);
                                    ';
                                }
                                echo '
                                    } else {
                                        console.error(\'No results found for the given address.\');
                                    }
                                })
                                .catch(error => {
                                    console.error(\'Error fetching geocoding data from Mapbox:\', error);
                                });';
                        }
                        elseif ( get_option('propertyhive_geocoding_provider') == 'osm' )
                        {
                            echo '
                            // Abort older request if they changed input quickly
                            if (phGeoXhr && phGeoXhr.readyState !== 4) phGeoXhr.abort();

                            if (phGeoInFlight && !force) return;
                            phGeoInFlight = true;

                            var data = {
                                \'action\': \'propertyhive_osm_geocoding_request\',
                                \'address\': address,
                                \'country\': jQuery(\'#_address_country\').val(),
                                \'security\': \'' . esc_js(wp_create_nonce( 'osm_geocoding_request' )) . '\'
                            };

                            phGeoXhr = jQuery.post( ajaxurl, data, function(response) {
                                
                                if ( response.error != \'\' )
                                {
                                    console.log(data);
                                    console.log(response);
                                    alert(response.error);
                                }
                                else
                                {
                                    //map.setZoom(16);
                            
                                    jQuery(\'#_latitude\').val(response.lat);
                                    jQuery(\'#_longitude\').val(response.lng);
                            ';

                            if ( get_option('propertyhive_maps_provider') == 'mapbox' )
                            {
                                echo '
                                    if ( marker != null )
                                    {
                                        marker.remove();
                                    }

                                    marker = new mapboxgl.Marker({
                                        //color: "#FFFFFF",
                                        draggable: true
                                    }).setLngLat([response.lng, response.lat])
                                        .addTo(map);

                                    marker.on(\'dragend\', marker_move_end);
                                    
                                    map.setCenter([response.lng, response.lat]);

                                    jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                                    {
                                        jQuery(\'#help-marker-set\').fadeIn();
                                    });
                                ';
                            }
                            elseif ( get_option('propertyhive_maps_provider') == 'osm' )
                            {    
                                echo '
                                    if ( marker != null )
                                    {
                                        marker.remove();
                                    }

                                    marker = L.marker([response.lat, response.lng], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);

                                    map.panTo([response.lat, response.lng]);

                                    jQuery(\'#help-marker-not-set\').fadeOut(\'fast\', function()
                                    {
                                        jQuery(\'#help-marker-set\').fadeIn();
                                    });
                                ';
                            }
                            else
                            {
                                echo '
                                    map.panTo(new google.maps.LatLng(response.lat, response.lng));
                                    
                                    marker = ph_create_marker(response.lat, response.lng);
                                ';
                            }
                            echo '
                                }
                            }, \'json\').always(function(){ phGeoInFlight = false; });
                            ';
                        }
                        else
                        {
                            echo '
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
                            ';
                        }
                    echo '
                    }
                }

            </script>
        ';

        if ( get_option('propertyhive_maps_provider') == 'mapbox' )
        {
            echo '
            <script>

                function geocode_callback( results, status )
                {
                    if (status == google.maps.GeocoderStatus.OK) 
                    {
                        //map.setZoom(16);
                        
                        jQuery(\'#_latitude\').val(results[0].geometry.location.lat());
                        jQuery(\'#_longitude\').val(results[0].geometry.location.lng());
                        
                        if ( marker != null )
                        {
                            marker.remove();
                        }

                        marker = new mapboxgl.Marker({
                            //color: "#FFFFFF",
                            draggable: true
                        }).setLngLat([results[0].geometry.location.lng(), results[0].geometry.location.lat()])
                            .addTo(map);

                        marker.on(\'dragend\', marker_move_end);
                        
                        map.setCenter([results[0].geometry.location.lng(), results[0].geometry.location.lat()]);

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
            ';
            if ( get_option('propertyhive_geocoding_provider') == '' )
            {
                echo '
                    geocoder = new google.maps.Geocoder();
                ';
            }
            echo '
                    mapboxgl.accessToken = \'' . get_option( 'propertyhive_mapbox_api_key', '' ) . '\';
                    map = new mapboxgl.Map({
                        container: "map_canvas", // container ID
                        center: [' . (float)$longitude . ', ' . (float)$latitude . '], // starting position [lng, lat]. Note that lat must be set between -90 and 90
                        zoom: ' . (int)$zoom . ' // starting zoom
                    });

                    if (markerSet)
                    {
                        marker = new mapboxgl.Marker({
                            //color: "#FFFFFF",
                            draggable: true
                        }).setLngLat([' . (float)$longitude . ', ' . (float)$latitude . '])
                            .addTo(map);

                        marker.on(\'dragend\', marker_move_end);
                    }
                }

                function marker_move_end()
                {
                    const lngLat = marker.getLngLat();

                    jQuery(\'#_latitude\').val( lngLat.lat );
                    jQuery(\'#_longitude\').val( lngLat.lng );
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
                            //map.setZoom(16);

                            if ( marker != null )
                            {
                                marker.remove();
                            }

                            marker = new mapboxgl.Marker({
                                //color: "#FFFFFF",
                                draggable: true
                            }).setLngLat([longitude, latitude])
                                .addTo(map);

                            marker.on(\'dragend\', marker_move_end);
                            
                            map.setCenter([longitude, latitude]);
                        }
                    });
                    
                    jQuery(\'#_longitude\').keyup(function()
                    {
                        var latitude = jQuery(\'#_latitude\').val();
                        var longitude = jQuery(this).val();
                        
                        if ( latitude != \'\' && longitude != \'\' && latitude != \'0\' && longitude != \'0\' )
                        {
                            // Both lat and lng exist
                            //map.setZoom(16);
                            
                            if ( marker != null )
                            {
                                marker.remove();
                            }
                            
                            marker = new mapboxgl.Marker({
                                //color: "#FFFFFF",
                                draggable: true
                            }).setLngLat([longitude, latitude])
                                .addTo(map);

                            marker.on(\'dragend\', marker_move_end);
                            
                            map.setCenter([longitude, latitude]);
                        }
                    });
                });

            </script>
            ';
        }
        elseif ( get_option('propertyhive_maps_provider') == 'osm' )
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
                ';
            if ( get_option('propertyhive_geocoding_provider') == '' )
            {
                echo '
                    geocoder = new google.maps.Geocoder();
                ';
            }
            echo '
                    map = L.map("map_canvas").setView([' . (float)$latitude . ', ' . (float)$longitude . '], ' . (int)$zoom . ');

                    L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                        attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\',
                        maxZoom: 19,
                    }).addTo(map);

                    if (markerSet)
                    {
                        marker = L.marker([' . (float)$latitude . ', ' . (float)$longitude . '], { draggable:true }).addTo(map).on(\'moveend\', marker_move_end);
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
            ';
            if ( get_option('propertyhive_geocoding_provider') == '' )
            {
                echo '
                    geocoder = new google.maps.Geocoder();
                ';
            }
            echo '      
                    var starting_lat_lng = new google.maps.LatLng(' . (float)$latitude . ', ' . (float)$longitude . ');
                    var mapOptions = {
                      center: starting_lat_lng,
                      zoom: ' . (int)$zoom . ',
                      scrollwheel: false 
                    };
                    map = new google.maps.Map(document.getElementById(\'map_canvas\'), mapOptions);
                    
                    if (markerSet)
                    {
                        // To add the marker to the map, use the \'map\' property
                        marker = ph_create_marker(' . (float)$latitude . ', ' . (float)$longitude . ');
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

                    markerSet = true;
                    
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
