<?php
/**
 * Property Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Actions
 */
class PH_Meta_Box_Property_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box" id="propertyhive_property_actions_meta_box">';
        
	        echo '<div class="options_group" style="padding-top:8px;">';

	        	echo '<a 
		                href="#action_panel_book_viewing" 
		                class="button property-action"
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

		        <div class="form-field">

		            <label for="viewing_applicant_search">
		            	<div style="float:right;"><a href="#" id="viewing-applicant-search-new-toggle" style="text-decoration:none;">' . __( 'Applicant Doesn\'t Exist', 'propertyhive' ) . '</a></div>
		            	' . __( 'Applicant', 'propertyhive' ) . '
		            </label>

		            <div id="viewing_existing_applicant_search" style="position:relative">

		            	<input type="text" name="viewing_applicant_search" id="viewing_applicant_search" style="width:100%;" placeholder="' . __( 'Search Existing Contacts', 'propertyhive' ) . '..." autocomplete="false">

		            	<div id="viewing_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            	<div id="viewing_selected_applicants" style="display:none;"></div>

		            </div>

		            <div id="viewing_new_applicant" style="display:none">
		            	<input type="text" name="viewing_applicant_name" id="viewing_applicant_name" style="width:100%;" placeholder="' . __( 'Enter Applicant Name', 'propertyhive' ) . '">
		            	<em>Upon booking a new ' . str_replace("-", " ", get_post_meta( $post->ID, '_department', TRUE)) . ' applicant will be created with this name.</em>
		            </div>

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

?>
<script>

var viewing_selected_applicants = {};

var viewing_selected_negotiators = {<?php echo get_current_user_id(); ?>: { post_title: '<?php $user_data = get_userdata(get_current_user_id()); echo $user_data->display_name; ?>' } };

jQuery(document).ready(function($)
{
	viewing_update_selected_negotiators();

	$('a.property-action').click(function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$('#propertyhive_property_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{
				$('input#viewing_applicant_search').focus();
			});
		});
	});

	$('a.action-cancel').click(function(e)
	{
		e.preventDefault();

		$('.propertyhive_meta_box_actions').stop().fadeOut(300, function()
		{
			$('#propertyhive_property_actions_meta_box').stop().fadeIn(300, function()
			{

			});
		});
	});

	// Viewing specific actions
	$('a#viewing-applicant-search-new-toggle').click(function(e)
	{
		e.preventDefault();

		if ($('#viewing_existing_applicant_search').css('display') == 'block')
		{
			$('#viewing_existing_applicant_search').stop().fadeOut(300, function()
			{
				$('#viewing_new_applicant').stop().fadeIn(300, function()
				{
					$('input#viewing_applicant_name').focus();
				});
			});

			$(this).text('<?php echo __( 'Search Existing Applicants', 'propertyhive' ); ?>');
		}
		else
		{
			$('#viewing_new_applicant').stop().fadeOut(300, function()
			{
				$('#viewing_existing_applicant_search').stop().fadeIn(300, function()
				{
					$('input#viewing_applicant_search').focus();
				});
			});

			$(this).text('<?php echo addslashes(__( 'Applicant Doesn\'t Exist', 'propertyhive' )); ?>');
		}
	});

	$('#viewing_applicant_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();
      		return false;
    	}
  	});

	$('#viewing_applicant_search').keyup(function(e)
	{
		e.preventDefault();

		var keyword = $(this).val();

		if (keyword.length == 0)
		{
			$('#viewing_search_applicant_results').html('');
			$('#viewing_search_applicant_results').hide();
			return false;
		}

		if (keyword.length < 3)
		{
			$('#viewing_search_applicant_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
			$('#viewing_search_applicant_results').show();
			return false;
		}

		// Do search
		var data = {
            action:         'propertyhive_search_contacts',
            keyword:    	keyword,
            security:       '<?php echo wp_create_nonce( 'search-contacts' ); ?>',
        };

        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#viewing_search_applicant_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
	        }
	        else
	        {
	        	$('#viewing_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
	        	for ( var i in response )
	        	{
	        		$('#viewing_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
	        	}
	        }
			$('#viewing_search_applicant_results').show();
        }, 'json');
	});

	$('#viewing_applicant_search').on('blur', function(e)
	{
    	//$('#viewing_search_applicant_results').hide();
  	});

	$('body').on('click', '#viewing_search_applicant_results ul li a', function(e)
	{
		e.preventDefault();

		viewing_selected_applicants = []; // reset to only allow one applicant for now
		viewing_selected_applicants[$(this).attr('href')] = ({ post_title: $(this).text() });

		$('#viewing_search_applicant_results').html('');
		$('#viewing_search_applicant_results').hide();

		$('#viewing_applicant_search').val('');

		viewing_update_selected_applicants();
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

	$('body').on('click', 'a.viewing-remove-applicant', function(e)
	{
		e.preventDefault();

		var applicant_id = $(this).attr('href');

		delete(viewing_selected_applicants[applicant_id]);

		viewing_update_selected_applicants();
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

		var new_applicant = false;
		if ($('#viewing_existing_applicant_search').css('display') == 'block')
		{
			if (Object.keys(viewing_selected_applicants).length == 0)
			{
				$('#viewing_applicant_search').focus();
				$('#viewing_applicant_search').css('transition', 'background 0.6s');
				$('#viewing_applicant_search').css('background', '#ff9999');
				setTimeout(function() { $('#viewing_applicant_search').css('background', '#FFF'); }, 1000);
				return false;
			}
		}
		if ($('#viewing_new_applicant').css('display') == 'block')
		{
			if ($('#viewing_applicant_name').val() == '')
			{
				$('#viewing_applicant_name').focus();
				$('#viewing_applicant_name').css('transition', 'background 0.6s');
				$('#viewing_applicant_name').css('background', '#ff9999');
				setTimeout(function() { $('#viewing_applicant_name').css('background', '#FFF'); }, 1000);
				return false;
			}
			new_applicant = true;
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
            action:         'propertyhive_book_viewing_property',
            property_id:    <?php echo $post->ID; ?>,
            start_date: 	$('#_viewing_start_time_date').val(),
            start_time: 	$('#_viewing_start_time_hours').val() + ':' + $('#_viewing_start_time_minutes').val() + ':00',
            applicant_name: ( (new_applicant) ? $('#viewing_applicant_name').val() : '' ),
            applicant_ids: 	( (!new_applicant) ? Object.keys(viewing_selected_applicants) : '' ),
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
        		
        		for ( var i in response.success.applicant_contacts )
        		{
        			$('#book_viewing_success_actions').append('<a href="' + response.success.applicant_contacts[i].edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Applicant - ' + response.success.applicant_contacts[i].post_title + '</a>');
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

function viewing_update_selected_applicants()
{
	if ( Object.keys(viewing_selected_applicants).length > 0 )
	{
		jQuery('#viewing_selected_applicants').html('<ul></ul>');
		for ( var i in viewing_selected_applicants )
		{
			jQuery('#viewing_selected_applicants ul').append('<li><a href="' + i + '" class="viewing-remove-applicant" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_applicants[i].post_title + '</li>');
		}
		jQuery('#viewing_selected_applicants').show();
	}
	else
	{
		jQuery('#viewing_selected_applicants').html('');
		jQuery('#viewing_selected_applicants').hide();
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