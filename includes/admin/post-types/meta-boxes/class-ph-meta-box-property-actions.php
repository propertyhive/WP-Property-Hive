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

	        	$actions = array();

	        	if ( get_option('propertyhive_module_disabled_contacts', '') != 'yes' )
            	{
		        	$actions[] = '<a 
			                href="' . esc_url(admin_url('admin.php?page=ph-matching-applicants&property_id=' . $post->ID)) . '" 
			                class="button"
			                style="width:100%; margin-bottom:7px; text-align:center" 
			            >' . esc_html(__('View Matching Applicants', 'propertyhive')) . '</a>';
		        }

		        if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
            	{
            		$actions[] = '<a 
			                href="' . esc_url(admin_url('post-new.php?post_type=enquiry&property_id=' . $post->ID)) . '" 
			                class="button"
			                style="width:100%; margin-bottom:7px; text-align:center" 
			            >' . esc_html(__('Record Enquiry', 'propertyhive')) . '</a>';
            	}

	        	if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
            	{
		        	$actions[] = '<a 
			                href="#action_panel_book_viewing" 
			                class="button property-action"
			                style="width:100%; margin-bottom:7px; text-align:center" 
			            >' . esc_html(__('Book Viewing', 'propertyhive')) . '</a>';
		        }

		        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
            	{
			        if ( 
			        	get_post_meta( $post->ID, '_department', TRUE ) == 'residential-sales' || 
			        	ph_get_custom_department_based_on(get_post_meta( $post->ID, '_department', TRUE )) == 'residential-sales' ||
			        	(
			        		(
			        			get_post_meta( $post->ID, '_department', TRUE ) == 'commercial' || 
			        			ph_get_custom_department_based_on(get_post_meta( $post->ID, '_department', TRUE )) == 'commercial' 
			        		)
			        		&&
			        		get_post_meta( $post->ID, '_for_sale', TRUE ) == 'yes'
			        	)
			        )
			        {
				        $actions[] = '<a 
				                href="#action_panel_record_offer" 
				                class="button property-action"
				                style="width:100%; margin-bottom:7px; text-align:center" 
				            >' . esc_html(__('Record Offer', 'propertyhive')) . '</a>';
			        }
			    }

				if ( get_option('propertyhive_module_disabled_tenancies', '') != 'yes' )
				{
					if ( get_post_meta( $post->ID, '_department', TRUE ) == 'residential-lettings' )
					{
						$actions[] = '<a
								href="' . esc_url(admin_url('post-new.php?post_type=tenancy&property_id=' . $post->ID)) . '"
								class="button"
								style="width:100%; margin-bottom:7px; text-align:center"
							>' . esc_html(__('Create Tenancy', 'propertyhive')) . '</a>';
					}
				}

			    $actions = apply_filters( 'propertyhive_admin_property_actions', $actions, $post->ID );
			    $actions = apply_filters( 'propertyhive_admin_post_actions', $actions, $post->ID );

		        if ( !empty($actions) )
		        {
		        	echo implode("", $actions);
		        }
		        else
		        {
		        	echo '<div style="text-align:center">' . esc_html(__( 'No actions to display', 'propertyhive' )) . '</div>';
		        }

	        echo '</div>';

        echo '</div>';

        // Book viewing action panel
        echo '<div id="action_panel_book_viewing" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
		             
    		<div class="options_group">

	    		<div class="form-field">

		            <label for="_viewing_start_date">' . esc_html(__( 'Viewing Date/Time', 'propertyhive' )) . '</label>

	            	<input type="date" class="small" name="_viewing_start_date" id="_viewing_start_date" value="' . esc_attr(date("Y-m-d")) . '" placeholder="" style="width:55%">
					<select id="_viewing_start_time_hours" name="_viewing_start_time_hours" class="select short" style="max-width:20%">';
	            	for ( $i = 0; $i <= 23; ++$i )
	            	{
	            		$j = str_pad($i, 2, '0', STR_PAD_LEFT);
	            		echo '<option value="' . esc_attr($j) . '"';
	            		if ( $j == date("H") ) { echo ' selected'; }
	            		echo '>' . esc_html($j) . '</option>';
	            	}
	            	echo '</select><select id="_viewing_start_time_minutes" name="_viewing_start_time_minutes" class="select short" style="max-width:20%">';
	            	for ( $i = 0; $i <= 59; $i+=5 )
	            	{
	            		$j = str_pad($i, 2, '0', STR_PAD_LEFT);
	            		echo '<option value="' . esc_attr($j) . '">' . esc_html($j) . '</option>';
	            	}
	            	echo '</select>

		        </div>

		        <hr>

		        <div class="form-field">

		            <label for="viewing_applicant_search">
		            	<div style="float:right;"><a href="#" id="viewing-applicant-search-new-toggle" style="text-decoration:none;">' . esc_html(__( 'Applicant Doesn\'t Exist', 'propertyhive' )) . '</a></div>
		            	' . esc_html(__( 'Applicant', 'propertyhive' )) . '
		            </label>

		            <div id="viewing_existing_applicant_search" style="position:relative">

		            	<input type="text" name="viewing_applicant_search" id="viewing_applicant_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Existing Contacts', 'propertyhive' )) . '..." autocomplete="false">

		            	<div id="viewing_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            	<div id="viewing_selected_applicants" style="display:none;"></div>

		            </div>

		            <div id="viewing_new_applicant" style="display:none">
		            	<input type="text" name="viewing_applicant_name" id="viewing_applicant_name" style="width:100%;" placeholder="' . esc_attr(__( 'Enter Applicant Name', 'propertyhive' )) . '">
		            	<em>Upon booking a new ' . esc_html(str_replace("-", " ", get_post_meta( $post->ID, '_department', TRUE))) . ' applicant will be created with this name.</em>

		            	<div style="margin:8px 0"><input type="email" name="viewing_applicant_email_address" id="viewing_applicant_email_address" style="width:100%;" placeholder="' . esc_attr(__( 'Email Address', 'propertyhive' )) . '"></div>

		            	<div style="margin:8px 0"><input type="text" name="viewing_applicant_telephone_number" id="viewing_applicant_telephone_number" style="width:100%;" placeholder="' . esc_attr(__( 'Telephone Number', 'propertyhive' )) . '"></div>

		            	<div style="margin-top:8px"><textarea name="viewing_applicant_address" id="viewing_applicant_address" style="width:100%;" placeholder="' . esc_attr(__( 'Address', 'propertyhive' )) . '"></textarea></div>
		            </div>

		        </div>

		        <hr>

		        <div class="form-field" style="position:relative">

		            <label for="viewing_negotiator_search">' . esc_html(__( 'Attending Negotiator(s)', 'propertyhive' )) . '</label>

		            <input type="text" name="viewing_negotiator_search" id="viewing_negotiator_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Negotiators', 'propertyhive' )) . '..." autocomplete="false">

		            <div id="viewing_search_negotiator_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            <div id="viewing_selected_negotiators" style="display:none;"></div>

		        </div>

		        <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
		        <a class="button button-primary viewing-action-submit" href="#">' . esc_html(__( 'Book Viewing', 'propertyhive' )) . '</a>

			</div>

		</div>';

		// Record offer action panel
        echo '<div id="action_panel_record_offer" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
		             
    		<div class="options_group">

	    		<div class="form-field">

		            <label for="_offer_date">' . esc_html(__( 'Offer Date/Time', 'propertyhive' )) . '</label>

	            	<input type="date" class="small" name="_offer_date" id="_offer_date" value="' . esc_attr(date("Y-m-d")) . '" placeholder="" style="width:55%">
					<select id="_offer_time_hours" name="_offer_time_hours" class="select short" style="max-width:20%">';
	            	for ( $i = 0; $i <= 23; ++$i )
	            	{
	            		$j = str_pad($i, 2, '0', STR_PAD_LEFT);
	            		echo '<option value="' . esc_attr($j) . '"';
	            		if ( $j == date("H") ) { echo ' selected'; }
	            		echo '>' . esc_html($j) . '</option>';
	            	}
	            	echo '</select><select id="_offer_time_minutes" name="_offer_time_minutes" class="select short" style="max-width:20%">';
	            	for ( $i = 0; $i <= 59; $i+=5 )
	            	{
	            		$j = str_pad($i, 2, '0', STR_PAD_LEFT);
	            		echo '<option value="' . esc_attr($j) . '">' . esc_html($j) . '</option>';
	            	}
	            	echo '</select>

		        </div>

		        <hr>

		        <div class="form-field">

		            <label for="offer_applicant_search">
		            	<div style="float:right;"><a href="#" id="offer-applicant-search-new-toggle" style="text-decoration:none;">' . esc_html(__( 'Applicant Doesn\'t Exist', 'propertyhive' )) . '</a></div>
		            	' . esc_html(__( 'Applicant', 'propertyhive' )) . '
		            </label>

		            <div id="offer_existing_applicant_search" style="position:relative">

		            	<input type="text" name="offer_applicant_search" id="offer_applicant_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Existing Contacts', 'propertyhive' )) . '..." autocomplete="false">

		            	<div id="offer_search_applicant_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            	<div id="offer_selected_applicants" style="display:none;"></div>

		            </div>

		            <div id="offer_new_applicant" style="display:none">
		            	<input type="text" name="offer_applicant_name" id="offer_applicant_name" style="width:100%;" placeholder="' . esc_attr(__( 'Enter Applicant Name', 'propertyhive' )) . '">
		            	<em>Upon booking a new ' . esc_html(str_replace("-", " ", get_post_meta( $post->ID, '_department', TRUE))) . ' applicant will be created with this name.</em>
		            
		            	<div style="margin:8px 0"><input type="email" name="offer_applicant_email_address" id="offer_applicant_email_address" style="width:100%;" placeholder="' . esc_attr(__( 'Email Address', 'propertyhive' )) . '"></div>

		            	<div style="margin:8px 0"><input type="text" name="offer_applicant_telephone_number" id="offer_applicant_telephone_number" style="width:100%;" placeholder="' . esc_attr(__( 'Telephone Number', 'propertyhive' )) . '"></div>

		            	<div style="margin-top:8px"><textarea name="offer_applicant_address" id="offer_applicant_address" style="width:100%;" placeholder="' . esc_attr(__( 'Address', 'propertyhive' )) . '"></textarea></div>
		            </div>

		        </div>

		        <hr>

		        <div class="form-field" style="position:relative">

		            <label for="_offer_amount">' . esc_html(__( 'Offer Amount', 'propertyhive' )) . ' (&pound;)</label>

		            <input type="text" name="_offer_amount" id="_offer_amount" style="width:100%;">

		        </div>

		        <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
		        <a class="button button-primary offer-action-submit" href="#">' . esc_html(__( 'Record Offer', 'propertyhive' )) . '</a>

			</div>

		</div>';

		// Success action panel
        echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
		             
    		<div class="options_group" style="padding-top:8px;">

    			<div id="success_actions"></div>

    			<a class="button action-cancel" style="width:100%;" href="#">' . esc_html(__( 'Back To Actions', 'propertyhive' )) . '</a>

    		</div>

    	</div>';

    	do_action( 'propertyhive_admin_property_action_options', $post->ID );
    	do_action( 'propertyhive_admin_post_action_options', $post->ID );

?>
<script>

var viewing_selected_applicants = {};
var offer_selected_applicants = {};

var viewing_selected_negotiators = {<?php echo get_current_user_id(); ?>: { post_title: '<?php $user_data = get_userdata(get_current_user_id()); echo esc_js($user_data->display_name); ?>' } };

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
				//$(this).find('input').eq(0).focus();
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

			$(this).text('<?php echo esc_html(__( 'Search Existing Applicants', 'propertyhive' )); ?>');
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

			$(this).text('<?php echo esc_html(__( 'Applicant Doesn\'t Exist', 'propertyhive' )); ?>');
		}
	});

	$('#_viewing_start_date').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();
      		return false;
    	}
  	});

	$('#viewing_applicant_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();

      		// Select first applicant in the list if one exists
      		if ( $.isNumeric( $('#viewing_search_applicant_results ul li:first-child a').attr('href') ) )
      		{
      			$('#viewing_search_applicant_results ul li:first-child a').trigger('click');
      		}

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
            security:       '<?php echo esc_js(wp_create_nonce( 'search-contacts' )); ?>',
            exclude_ids:    Object.keys(viewing_selected_applicants).join('|'),
        };

        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#viewing_search_applicant_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'<br><a href="new-applicant" data-name="' + keyword + '">Add as new applicant?</a></div>');
	        }
	        else
	        {
	        	$('#viewing_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
	        	for ( var i in response )
	        	{
	        		$('#viewing_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;" data-applicant-name="' + response[i].post_title + '"><strong>' + response[i].post_title + '</strong><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? response[i].address_full_formatted + '<br>' : '' ) + ( response[i].telephone_number != '' ? response[i].telephone_number + '<br>' : '' ) + ( response[i].email_address != '' ? response[i].email_address : '' ) + '</small></a></li>');
	        	}
	        }
			$('#viewing_search_applicant_results').show();
        }, 'json');
	});

	$('body').on('click', '#viewing_search_applicant_results a[href=\'new-applicant\']', function(e)
	{
		e.preventDefault();

		var name = $(this).attr('data-name');

		$('#viewing_search_applicant_results').html('');
		$('#viewing_search_applicant_results').hide();

		$('#viewing_applicant_search').val('');
		$('#viewing_applicant_name').val(name);

		$('a#viewing-applicant-search-new-toggle').trigger('click');
	});

	$('#viewing_applicant_search').on('blur', function(e)
	{
    	//$('#viewing_search_applicant_results').hide();
  	});

	$('body').on('click', '#viewing_search_applicant_results ul li a', function(e)
	{
		e.preventDefault();

		viewing_selected_applicants[$(this).attr('href')] = ({ post_title: $(this).attr('data-applicant-name') });

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

      		// Select first applicant in the list if one exists
      		if ( $.isNumeric( $('#viewing_search_negotiator_results ul li:first-child a').attr('href') ) )
      		{
      			$('#viewing_search_negotiator_results ul li:first-child a').trigger('click');
      		}

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
            security:       '<?php echo esc_js(wp_create_nonce( 'search-negotiators' )); ?>',
        };

        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
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
		if ($('#_viewing_start_date').val() == '')
		{
			$('#_viewing_start_date').focus();
			$('#_viewing_start_date').css('transition', 'background 0.6s');
			$('#_viewing_start_date').css('background', '#ff9999');
			setTimeout(function() { $('#_viewing_start_date').css('background', '#FFF'); }, 1000);
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

		$(this).attr('disabled', 'disabled');
		$(this).text('Booking...');

		// Validation passed. Submit form
		var data = {
            action:         'propertyhive_book_viewing_property',
            property_id:    <?php echo (int)$post->ID; ?>,
            start_date: 	$('#_viewing_start_date').val(),
            start_time: 	$('#_viewing_start_time_hours').val() + ':' + $('#_viewing_start_time_minutes').val() + ':00',
            applicant_name: ( (new_applicant) ? $('#viewing_applicant_name').val() : '' ),
            applicant_email_address: ( (new_applicant) ? $('#viewing_applicant_email_address').val() : '' ),
            applicant_telephone_number: ( (new_applicant) ? $('#viewing_applicant_telephone_number').val() : '' ),
            applicant_address: ( (new_applicant) ? $('#viewing_applicant_address').val() : '' ),
            applicant_ids: 	( (!new_applicant) ? Object.keys(viewing_selected_applicants) : '' ),
            negotiator_ids: Object.keys(viewing_selected_negotiators),
            security:       '<?php echo esc_js(wp_create_nonce( 'book-viewing' )); ?>',
        };

        var that = this;
		$.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
        	if (response.error)
        	{
        		alert(response.error);
        	}
        	if (response.success)
        	{
        		$('#success_actions').html('');

        		$('#success_actions').append('<a href="' + response.success.viewing.edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Viewing</a>');
        		
        		for ( var i in response.success.applicant_contacts )
        		{
        			$('#success_actions').append('<a href="' + response.success.applicant_contacts[i].edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Applicant - ' + response.success.applicant_contacts[i].post_title + '</a>');
        		}

        		$('#action_panel_book_viewing').stop().fadeOut(300, function()
				{
					$('#action_panel_success').stop().fadeIn(300);
				});
        	}

        	$(that).attr('disabled', false);
        	$(that).text('Book Viewing');
        });
	});

	// offer specific actions
	$('a#offer-applicant-search-new-toggle').click(function(e)
	{
		e.preventDefault();

		if ($('#offer_existing_applicant_search').css('display') == 'block')
		{
			$('#offer_existing_applicant_search').stop().fadeOut(300, function()
			{
				$('#offer_new_applicant').stop().fadeIn(300, function()
				{
					$('input#offer_applicant_name').focus();
				});
			});

			$(this).text('<?php echo esc_html(__( 'Search Existing Applicants', 'propertyhive' )); ?>');
		}
		else
		{
			$('#offer_new_applicant').stop().fadeOut(300, function()
			{
				$('#offer_existing_applicant_search').stop().fadeIn(300, function()
				{
					$('input#offer_applicant_search').focus();
				});
			});

			$(this).text('<?php echo esc_html(__( 'Applicant Doesn\'t Exist', 'propertyhive' )); ?>');
		}
	});

	$('#_offer_date').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();
      		return false;
    	}
  	});

	$('#offer_applicant_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();

      		// Select first applicant in the list if one exists
      		if ( $.isNumeric( $('#offer_search_applicant_results ul li:first-child a').attr('href') ) )
      		{
      			$('#offer_search_applicant_results ul li:first-child a').trigger('click');
      		}

      		return false;
    	}
  	});

  	$('#_offer_amount').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();
      		return false;
    	}
  	});

	$('#offer_applicant_search').keyup(function(e)
	{
		e.preventDefault();

		var keyword = $(this).val();

		if (keyword.length == 0)
		{
			$('#offer_search_applicant_results').html('');
			$('#offer_search_applicant_results').hide();
			return false;
		}

		if (keyword.length < 3)
		{
			$('#offer_search_applicant_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
			$('#offer_search_applicant_results').show();
			return false;
		}

		// Do search
		var data = {
            action:         'propertyhive_search_contacts',
            keyword:    	keyword,
            security:       '<?php echo esc_js(wp_create_nonce( 'search-contacts' )); ?>',
        };

        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#offer_search_applicant_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'<br><a href="new-applicant" data-name="' + keyword + '">Add as new applicant?</a></div>');
	        }
	        else
	        {
	        	$('#offer_search_applicant_results').html('<ul style="margin:0; padding:0;"></ul>');
	        	for ( var i in response )
	        	{
	        		$('#offer_search_applicant_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;"><strong>' + response[i].post_title + '</strong><small style="color:#999; padding-top:1px; display:block; line-height:1.5em">' + ( response[i].address_full_formatted != '' ? response[i].address_full_formatted + '<br>' : '' ) + ( response[i].telephone_number != '' ? response[i].telephone_number + '<br>' : '' ) + ( response[i].email_address != '' ? response[i].email_address : '' ) + '</small></a></li>');
	        	}
	        }
			$('#offer_search_applicant_results').show();
        }, 'json');
	});

	$('body').on('click', '#offer_search_applicant_results a[href=\'new-applicant\']', function(e)
	{
		e.preventDefault();

		var name = $(this).attr('data-name');

		$('#offer_search_applicant_results').html('');
		$('#offer_search_applicant_results').hide();

		$('#offer_applicant_search').val('');
		$('#offer_applicant_name').val(name);

		$('a#offer-applicant-search-new-toggle').trigger('click');
	});

	$('#offer_applicant_search').on('blur', function(e)
	{
    	//$('#offer_search_applicant_results').hide();
  	});

	$('body').on('click', '#offer_search_applicant_results ul li a', function(e)
	{
		e.preventDefault();

		offer_selected_applicants = []; // reset to only allow one applicant for now
		offer_selected_applicants[$(this).attr('href')] = ({ post_title: $(this).text() });

		$('#offer_search_applicant_results').html('');
		$('#offer_search_applicant_results').hide();

		$('#offer_applicant_search').val('');

		offer_update_selected_applicants();
	});

	$('body').on('click', 'a.offer-remove-applicant', function(e)
	{
		e.preventDefault();

		var applicant_id = $(this).attr('href');

		delete(offer_selected_applicants[applicant_id]);

		offer_update_selected_applicants();
	});

	$('a.offer-action-submit').click(function(e)
	{
		e.preventDefault();

		// Validation
		if ($('#_offer_date').val() == '')
		{
			$('#_offer_date').focus();
			$('#_offer_date').css('transition', 'background 0.6s');
			$('#_offer_date').css('background', '#ff9999');
			setTimeout(function() { $('#_offer_date').css('background', '#FFF'); }, 1000);
			return false;
		}

		var new_applicant = false;
		if ($('#offer_existing_applicant_search').css('display') == 'block')
		{
			if (Object.keys(offer_selected_applicants).length == 0)
			{
				$('#offer_applicant_search').focus();
				$('#offer_applicant_search').css('transition', 'background 0.6s');
				$('#offer_applicant_search').css('background', '#ff9999');
				setTimeout(function() { $('#offer_applicant_search').css('background', '#FFF'); }, 1000);
				return false;
			}
		}
		if ($('#offer_new_applicant').css('display') == 'block')
		{
			if ($('#offer_applicant_name').val() == '')
			{
				$('#offer_applicant_name').focus();
				$('#offer_applicant_name').css('transition', 'background 0.6s');
				$('#offer_applicant_name').css('background', '#ff9999');
				setTimeout(function() { $('#offer_applicant_name').css('background', '#FFF'); }, 1000);
				return false;
			}
			new_applicant = true;
		}

		$(this).attr('disabled', 'disabled');
		$(this).text('Saving...');

		// Validation passed. Submit form
		var data = {
            action:         'propertyhive_record_offer_property',
            property_id:    <?php echo (int)$post->ID; ?>,
            offer_date: 	$('#_offer_date').val(),
            offer_time: 	$('#_offer_time_hours').val() + ':' + $('#_offer_time_minutes').val() + ':00',
            applicant_name: ( (new_applicant) ? $('#offer_applicant_name').val() : '' ),
            applicant_email_address: ( (new_applicant) ? $('#offer_applicant_email_address').val() : '' ),
            applicant_telephone_number: ( (new_applicant) ? $('#offer_applicant_telephone_number').val() : '' ),
            applicant_address: ( (new_applicant) ? $('#offer_applicant_address').val() : '' ),
            applicant_ids: 	( (!new_applicant) ? Object.keys(offer_selected_applicants) : '' ),
            amount: 		$('#_offer_amount').val(),
            security:       '<?php echo esc_js(wp_create_nonce( 'record-offer' )); ?>',
        };

        var that = this;
		$.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
        	if (response.error)
        	{
        		alert(response.error);
        	}
        	if (response.success)
        	{
        		$('#success_actions').html('');

        		$('#success_actions').append('<a href="' + response.success.offer.edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Offer</a>');
        		
        		for ( var i in response.success.applicant_contacts )
        		{
        			$('#success_actions').append('<a href="' + response.success.applicant_contacts[i].edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;">Edit Applicant - ' + response.success.applicant_contacts[i].post_title + '</a>');
        		}

        		$('#action_panel_record_offer').stop().fadeOut(300, function()
				{
					$('#action_panel_success').stop().fadeIn(300);
				});
        	}

        	$(that).attr('disabled', false);
        	$(that).text('Record Offer');
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

function offer_update_selected_applicants()
{
	if ( Object.keys(offer_selected_applicants).length > 0 )
	{
		jQuery('#offer_selected_applicants').html('<ul></ul>');
		for ( var i in offer_selected_applicants )
		{
			jQuery('#offer_selected_applicants ul').append('<li><a href="' + i + '" class="offer-remove-applicant" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_selected_applicants[i].post_title + '</li>');
		}
		jQuery('#offer_selected_applicants').show();
	}
	else
	{
		jQuery('#offer_selected_applicants').html('');
		jQuery('#offer_selected_applicants').hide();
	}
}

</script>
<?php

    }
}