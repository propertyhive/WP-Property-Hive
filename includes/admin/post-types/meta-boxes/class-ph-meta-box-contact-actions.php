<?php
/**
 * Contact Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Contact_Actions
 */
class PH_Meta_Box_Contact_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        $contact_types = get_post_meta( $post->ID, '_contact_types', TRUE );
        if ( in_array('applicant', $contact_types) )
        {
	        echo '<div class="propertyhive_meta_box" id="propertyhive_contact_actions_meta_box">';
	        
		        echo '<div class="options_group" style="padding-top:8px;">';

		        	echo '<a 
			                href="#action_panel_book_viewing" 
			                class="button contact-action"
			                style="width:100%; margin-bottom:7px; text-align:center" 
			            >' . __('Book Viewing', 'propertyhive') . '</a>';

			        

		        echo '</div>';

	        echo '</div>';

	        // Book viewing action panel
	        echo '<div id="action_panel_book_viewing" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
			             
	    		<div class="options_group">

		    		<div class="form-field">

			            <label for="_viewing_start_time_date">' . __( 'Viewing Date/Time', 'propertyhive' ) . '</label>
			            
		            	<input type="text" id="_viewing_start_time_date" name="_viewing_start_time_date" class="date-picker" style="width:50%" placeholder="yyyy-mm-dd" value="' . date("Y-m-d") . '">
		            	<select id="_viewing_start_time_hours" name="_viewing_start_time_hours" class="select short" style="max-width:22%">';
		            	for ( $i = 0; $i <= 23; ++$i )
		            	{
		            		$j = str_pad($i, 2, '0', STR_PAD_LEFT);
		            		echo '<option value="' . $j . '"';
		            		if ( $j == date("H") ) { echo ' selected'; }
		            		echo '>' . $j . '</option>';
		            	}
		            	echo '</select><select id="_viewing_start_time_minutes" name="_viewing_start_time_minutes" class="select short" style="max-width:22%">';
		            	for ( $i = 0; $i <= 59; $i+=5 )
		            	{
		            		$j = str_pad($i, 2, '0', STR_PAD_LEFT);
		            		echo '<option value="' . $j . '">' . $j . '</option>';
		            	}
		            	echo '</select>

			        </div>

			        <hr>

			        <div class="form-field" style="position:relative;">

			            <label for="viewing_applicant_search">
			            	' . __( 'Property', 'propertyhive' ) . '
			            </label>

		            	<input type="text" name="viewing_property_search" id="viewing_property_search" style="width:100%;" placeholder="' . __( 'Search Properties', 'propertyhive' ) . '..." autocomplete="false">

		            	<div id="viewing_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            	<div id="viewing_selected_properties" style="display:none;"></div>

			        </div>

			        <hr>

			        <div class="form-field" style="position:relative">

			            <label for="viewing_negotiator_search">' . __( 'Attending Negotiator(s)', 'propertyhive' ) . '</label>

			            <input type="text" name="viewing_negotiator_search" id="viewing_negotiator_search" style="width:100%;" placeholder="' . __( 'Search Negotiators', 'propertyhive' ) . '..." autocomplete="false">

			            <div id="viewing_search_negotiator_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

			            <div id="viewing_selected_negotiators" style="display:none;"></div>

			        </div>

			        <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
			        <a class="button button-primary viewing-action-submit" href="#">' . __( 'Book Viewing', 'propertyhive' ) . '</a>

				</div>

			</div>';

			// Book viewing succes action panel
	        echo '<div id="action_panel_book_viewing_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
			             
	    		<div class="options_group" style="padding-top:8px;">

	    			<div id="book_viewing_success_actions"></div>

	    			<a class="button action-cancel" style="width:100%;" href="#">' . __( 'Back To Actions', 'propertyhive' ) . '</a>

	    		</div>

	    	</div>';
	    }

?>
<script>

var viewing_selected_properties = {};

var viewing_selected_negotiators = {<?php echo get_current_user_id(); ?>: { post_title: '<?php $user_data = get_userdata(get_current_user_id()); echo $user_data->display_name; ?>' } };

jQuery(document).ready(function($)
{
	viewing_update_selected_negotiators();

	$('a.contact-action').click(function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$('#propertyhive_contact_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				$('input#viewing_property_search').focus();
			});
		});
	});

	$('a.action-cancel').click(function(e)
	{
		e.preventDefault();

		$('.propertyhive_meta_box_actions').stop().fadeOut(300, function()
		{
			$('#propertyhive_contact_actions_meta_box').stop().fadeIn(300, function()
			{

			});
		});
	});

	$('#viewing_property_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();
      		return false;
    	}
  	});

	$('#viewing_property_search').keyup(function(e)
	{
		e.preventDefault();

		var keyword = $(this).val();

		if (keyword.length == 0)
		{
			$('#viewing_search_property_results').html('');
			$('#viewing_search_property_results').hide();
			return false;
		}

		if (keyword.length < 3)
		{
			$('#viewing_search_property_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
			$('#viewing_search_property_results').show();
			return false;
		}

		// Do search
		var data = {
            action:         'propertyhive_search_properties',
            keyword:    	keyword,
            security:       '<?php echo wp_create_nonce( 'search-properties' ); ?>',
        };

        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#viewing_search_property_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
	        }
	        else
	        {
	        	$('#viewing_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
	        	for ( var i in response )
	        	{
	        		$('#viewing_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
	        	}
	        }
			$('#viewing_search_property_results').show();
        }, 'json');
	});

	$('#viewing_property_search').on('blur', function(e)
	{
    	//$('#viewing_search_applicant_results').hide();
  	});

	$('body').on('click', '#viewing_search_property_results ul li a', function(e)
	{
		e.preventDefault();

		viewing_selected_properties = []; // reset to only allow one property for now
		viewing_selected_properties[$(this).attr('href')] = ({ post_title: $(this).text() });

		$('#viewing_search_property_results').html('');
		$('#viewing_search_property_results').hide();

		$('#viewing_property_search').val('');

		viewing_update_selected_properties();
	});

	$('#viewing_negotiator_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();
      		return false;
    	}
  	});

	$('#viewing_negotiator_search').keyup(function(e)
	{
		e.preventDefault();

		var keyword = $(this).val();

		if (keyword.length == 0)
		{
			$('#viewing_search_negotiator_results').html('');
			$('#viewing_search_negotiator_results').hide();
			return false;
		}

		if (keyword.length < 3)
		{
			$('#viewing_search_negotiator_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
			$('#viewing_search_negotiator_results').show();
			return false;
		}

		// Do search
		var data = {
            action:         'propertyhive_search_negotiators',
            keyword:    	keyword,
            security:       '<?php echo wp_create_nonce( 'search-negotiators' ); ?>',
        };

        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#viewing_search_negotiator_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
	        }
	        else
	        {
	        	$('#viewing_search_negotiator_results').html('<ul style="margin:0; padding:0;"></ul>');
	        	for ( var i in response )
	        	{
	        		$('#viewing_search_negotiator_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
	        	}
	        }
			$('#viewing_search_negotiator_results').show();
        }, 'json');
	});

	$('#viewing_negotiator_search').on('blur', function(e)
	{
    	//$('#viewing_search_negotiator_results').hide();
  	});

	$('body').on('click', '#viewing_search_negotiator_results ul li a', function(e)
	{
		e.preventDefault();

		viewing_selected_negotiators[$(this).attr('href')] = ({ post_title: $(this).text() });

		$('#viewing_search_negotiator_results').html('');
		$('#viewing_search_negotiator_results').hide();

		$('#viewing_negotiator_search').val('');

		viewing_update_selected_negotiators();
	});

	$('body').on('click', 'a.viewing-remove-property', function(e)
	{
		e.preventDefault();

		var property_id = $(this).attr('href');

		delete(viewing_selected_properties[property_id]);

		viewing_update_selected_properties();
	});

	$('body').on('click', 'a.viewing-remove-negotiator', function(e)
	{
		e.preventDefault();

		var negotiator_id = $(this).attr('href');

		delete(viewing_selected_negotiators[negotiator_id]);

		viewing_update_selected_negotiators();
	});

	$('a.viewing-action-submit').click(function(e)
	{
		e.preventDefault();

		// Validation
		if ($('#_viewing_start_time_date').val() == '')
		{
			$('#_viewing_start_time_date').focus();
			$('#_viewing_start_time_date').css('transition', 'background 0.6s');
			$('#_viewing_start_time_date').css('background', '#ff9999');
			setTimeout(function() { $('#_viewing_start_time_date').css('background', '#FFF'); }, 1000);
			return false;
		}

		if (Object.keys(viewing_selected_properties).length == 0)
		{
			$('#viewing_property_search').focus();
			$('#viewing_property_search').css('transition', 'background 0.6s');
			$('#viewing_property_search').css('background', '#ff9999');
			setTimeout(function() { $('#viewing_property_search').css('background', '#FFF'); }, 1000);
			return false;
		}

		// Don't validate on negotiator becuase it could be an unattended viewing
		/*if (Object.keys(viewing_selected_negotiators).length == 0)
		{
			$('#viewing_negotiator_search').focus();
			$('#viewing_negotiator_search').css('transition', 'background 0.6s');
			$('#viewing_negotiator_search').css('background', '#ff9999');
			setTimeout(function() { $('#viewing_negotiator_search').css('background', '#FFF'); }, 1000);
			return false;
		}*/

		$(this).attr('disabled', 'disabled');
		$(this).text('Booking...');

		// Validation passed. Submit form
		var data = {
            action:         'propertyhive_book_viewing_contact',
            contact_id:     <?php echo $post->ID; ?>,
            start_date: 	$('#_viewing_start_time_date').val(),
            start_time: 	$('#_viewing_start_time_hours').val() + ':' + $('#_viewing_start_time_minutes').val() + ':00',
            property_ids: 	Object.keys(viewing_selected_properties),
            negotiator_ids: Object.keys(viewing_selected_negotiators),
            security:       '<?php echo wp_create_nonce( 'book-viewing' ); ?>',
        };

        var that = this;
		$.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
        	if (response.error)
        	{
        		alert(response.error);
        	}
        	if (response.success)
        	{
        		$('#book_viewing_success_actions').html('');

        		$('#book_viewing_success_actions').append('<a href="' + response.success.viewing.edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Viewing</a>');
        		
        		for ( var i in response.success.properties )
        		{
        			$('#book_viewing_success_actions').append('<a href="' + response.success.properties[i].edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Property</a>');
        		}

        		$('#action_panel_book_viewing').stop().fadeOut(300, function()
				{
					$('#action_panel_book_viewing_success').stop().fadeIn(300);
				});
        	}

        	$(that).attr('disabled', false);
        	$(that).text('Book Viewing');
        });
	});

});

function viewing_update_selected_properties()
{
	if ( Object.keys(viewing_selected_properties).length > 0 )
	{
		jQuery('#viewing_selected_properties').html('<ul></ul>');
		for ( var i in viewing_selected_properties )
		{
			jQuery('#viewing_selected_properties ul').append('<li><a href="' + i + '" class="viewing-remove-applicant" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_properties[i].post_title + '</li>');
		}
		jQuery('#viewing_selected_properties').show();
	}
	else
	{
		jQuery('#viewing_selected_properties').html('');
		jQuery('#viewing_selected_properties').hide();
	}
}

function viewing_update_selected_negotiators()
{
	if ( Object.keys(viewing_selected_negotiators).length > 0 )
	{
		
		jQuery('#viewing_selected_negotiators').html('<ul></ul>');
		for ( var i in viewing_selected_negotiators )
		{
			jQuery('#viewing_selected_negotiators ul').append('<li><a href="' + i + '" class="viewing-remove-negotiator" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_negotiators[i].post_title + '</li>');
		}
		jQuery('#viewing_selected_negotiators').show();
	}
	else
	{
		jQuery('#viewing_selected_negotiators').html('<ul><li><em>Unattended</em></li></ul>');
		jQuery('#viewing_selected_negotiators').show();
	}
}

</script>
<?php

    }
}