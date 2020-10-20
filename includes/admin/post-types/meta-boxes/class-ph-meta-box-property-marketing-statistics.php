<?php
/**
 * Property Marketing Statistics
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Marketing_Statistics
 */
class PH_Meta_Box_Property_Marketing_Statistics {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        
        echo '<div class="date-range">';

            echo '<input type="text" name="statistics_date_from" id="statistics_date_from" class="date-picker" value="' . date("Y-m-d", strtotime('7 days ago')) . '">';
            echo ' - ';
            echo '<input type="text" name="statistics_date_to" id="statistics_date_to" class="date-picker" value="' . date("Y-m-d") . '">';

        echo '</div>';

        echo '<div id="propertyhive_property_marketing_statistics_meta_box">Loading...</div>';
        
        echo '<script>

            jQuery(window).on(\'load\', function()
            {
                jQuery(\'#statistics_date_from\').on(\'change\', function(e) {
                    reload_marketing_statistics();
                });
                jQuery(\'#statistics_date_to\').on(\'change\', function(e) {
                    reload_marketing_statistics();
                });
            });

            function reload_marketing_statistics()
            {
                var data = {
                    action: \'propertyhive_get_property_marketing_statistics_meta_box\',
                    post_id: ' . $post->ID . ',
                    statistics_date_from: jQuery(\'#statistics_date_from\').val(),
                    statistics_date_to: jQuery(\'#statistics_date_to\').val(),
                    security: \'' . wp_create_nonce( 'get_property_marketing_statistics_meta_box' ) . '\'
                }

                jQuery.post( \'' . admin_url('admin-ajax.php') . '\', data, function(response) 
                {
                    jQuery(\'#propertyhive_property_marketing_statistics_meta_box\').html(response);
                    
                    var marketing_statistics = jQuery(\'#marketing_statistics\').val();
                    marketing_statistics = jQuery.parseJSON(marketing_statistics);

                    var dataset = [
                        {
                            label: "Website views",
                            data: marketing_statistics,
                            color: "#FF0000",
                            points: { fillColor: "#FF0000", show: true },
                            lines: { show: true }
                        }
                    ];

                    jQuery.plot(
                        "#marketing_statistics_website_view_graph", 
                        dataset,
                        {
                            grid: { show: true, borderWidth: 0, hoverable: true, },
                            legend: { show:false },
                            yaxis: {
                                min:0, 
                                minTickSize: 1,
                                tickDecimals: 0
                            },
                            xaxis: {
                                tickSize: [1, "day"],
                                mode: "time",
                                timeformat: "%d/%m"
                            }
                        }
                    );
                    jQuery("#marketing_statistics_website_view_graph").useTooltip();
                }, \'html\');
            }

            // Tooltip
            jQuery("<div id=\'tooltip\'></div>").css({
                position: "absolute",
                display: "none",
                border: "1px solid #fdd",
                padding: "4px",
                backgroundColor: "#333",
                color: "#FFF",
                opacity: 0.80
            }).appendTo("body");

            jQuery.fn.useTooltip = function () {
                jQuery(this).bind("plothover", function (event, pos, item) 
                {
                    if (item) {
                        var x = item.datapoint[0];
                        var y = item.datapoint[1];

                        var time = new Date(x).getTime();
                        var date = new Date(time);

                        jQuery("#tooltip").html( (\'0\' + date.getDate()).slice(-2) + \'/\' + (\'0\' + (date.getMonth() + 1)).slice(-2) + \'/\' + date.getFullYear() + \' - \' + y + \' views\' )
                            .css({top: item.pageY+5, left: item.pageX})
                            .fadeIn(200);
                    } else {
                        jQuery("#tooltip").hide();
                    }
                });
            };
            

        </script>';
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        
    }

}
