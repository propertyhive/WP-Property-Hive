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

        $actions = array();

        if ( get_option('propertyhive_module_disabled_enquiries', '') != 'yes' )
        {
        	$actions[] = '<a 
                href="' . admin_url( 'post-new.php' ) . '?post_type=enquiry&name=' . urlencode(get_the_title($post->ID)) . '&email=' . urlencode(get_post_meta($post->ID, '_email_address', TRUE)) . '&telephone=' .  urlencode(get_post_meta($post->ID, '_telephone_number', TRUE)) . '"
                class="button"
                style="width:100%; margin-bottom:7px; text-align:center" 
            >' . __('Record Enquiry', 'propertyhive') . '</a>';
        }
        
        $contact_types = get_post_meta( $post->ID, '_contact_types', TRUE );
        if ( is_array($contact_types) && in_array('applicant', $contact_types) )
        {
        	if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
        	{
	        	$actions[] = '<a 
		                href="#action_panel_book_viewing" 
		                class="button contact-action"
		                style="width:100%; margin-bottom:7px; text-align:center" 
		            >' . __('Book Viewing', 'propertyhive') . '</a>';
	        }

	        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
        	{
		        $show_offers = false;
	            $show_sales = false;

	            $contact_types = get_post_meta( $post->ID, '_contact_types', TRUE );
	            if ( is_array($contact_types) && in_array('applicant', $contact_types) )
	            {
	                $show_viewings = true;

	                $num_applicant_profiles = get_post_meta( $post->ID, '_applicant_profiles', TRUE );
	                if ( $num_applicant_profiles == '' )
	                {
	                    $num_applicant_profiles = 0;
	                }

	                if ( $num_applicant_profiles > 0 ) 
	                {
	                    for ( $i = 0; $i < $num_applicant_profiles; ++$i )
	                    {
	                        $applicant_profile = get_post_meta( $post->ID, '_applicant_profile_' . $i, TRUE );

	                        if ( 
	                        	isset($applicant_profile['department']) && 
	                        	(
	                        		( $applicant_profile['department'] == 'residential-sales' || ph_get_custom_department_based_on($applicant_profile['department']) == 'residential-sales' ) ||
	                        		( 
	                        			( $applicant_profile['department'] == 'commercial' || ph_get_custom_department_based_on($applicant_profile['department']) == 'commercial' )
	                        			&&
	                        			( isset($applicant_profile['available_as']) && in_array('sale', $applicant_profile['available_as']) )
	                        		)
	                        	)
	                        )
	                        {
	                            $show_offers = true;
	                            $show_sales = true;
	                        }
	                    }
	                }
	            }

	            if ( $show_offers )
	            {
	            	$actions[] = '<a 
		                href="#action_panel_record_offer" 
		                class="button contact-action"
		                style="width:100%; margin-bottom:7px; text-align:center" 
		            >' . __('Record Offer', 'propertyhive') . '</a>';
	            }
	        }

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

			        <div class="form-field" style="position:relative;">

			            <label for="viewing_property_search">
			            	' . esc_html(__( 'Property', 'propertyhive' )) . '
			            </label>

		            	<input type="text" name="viewing_property_search" id="viewing_property_search" style="width:100%;" placeholder="' . esc_html(__( 'Search Properties', 'propertyhive' )) . '..." autocomplete="false">

		            	<div id="viewing_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            	<div id="viewing_selected_properties" style="display:none;"></div>

			        </div>

			        <hr>

			        <div class="form-field" style="position:relative">

			            <label for="viewing_negotiator_search">' . esc_html(__( 'Attending Negotiator(s)', 'propertyhive' )) . '</label>

			            <input type="text" name="viewing_negotiator_search" id="viewing_negotiator_search" style="width:100%;" placeholder="' . esc_html(__( 'Search Negotiators', 'propertyhive' )) . '..." autocomplete="false">

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

			        <div class="form-field" style="position:relative;">

			            <label for="offer_property_search">
			            	' . esc_html(__( 'Property', 'propertyhive' )) . '
			            </label>

		            	<input type="text" name="offer_property_search" id="offer_property_search" style="width:100%;" placeholder="' . esc_attr(__( 'Search Properties', 'propertyhive' )) . '..." autocomplete="false">

		            	<div id="offer_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

		            	<div id="offer_selected_properties" style="display:none;"></div>

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
	    }

	    // Show user options if has a valid email
        if ( get_post_meta( $post->ID, '_email_address', TRUE ) != '' && is_email(get_post_meta( $post->ID, '_email_address', TRUE )) )
        {
	        $user_id = get_post_meta( $post->ID, '_user_id', TRUE );
	        if ( $user_id != '' )
	        {
	        	// Has a user associated
		        $actions[] = '<a 
			                href="' . get_edit_user_link( $user_id ) . '&wp_http_referer=' . urlencode(get_edit_post_link($post->ID)) . '" 
			                class="button"
			                style="width:100%; margin-bottom:7px; text-align:center" 
			            >' . __('View User / Change Password', 'propertyhive') . '</a>';
		    }
		    else
		    {
		    	$actions[] = '<a 
			                href="#action_panel_create_login" 
			                class="button contact-action"
			                style="width:100%; margin-bottom:7px; text-align:center" 
			            >' . __('Create User Login', 'propertyhive') . '</a>';
		    }
		}

		// Create user action panel
        echo '<div id="action_panel_create_login" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
		             
    		<div class="options_group">

	    		<div class="form-field">

		            <label for="_password">' . esc_html(__( 'Password', 'propertyhive' )) . '</label>
		            
	            	<input type="text" id="_password" name="_password" style="width:100%;" value="' . esc_attr( wp_generate_password( 16 ) ) . '">
	            	
		        </div>

		        <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
		        <a class="button button-primary login-action-submit" href="#">' . esc_html(__( 'Create Login', 'propertyhive' )) . '</a>

			</div>

		</div>';

		// Success action panel
        echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
		             
    		<div class="options_group" style="padding-top:8px;">

    			<div id="success_actions"></div>

    			<a class="button action-cancel" style="width:100%;" href="#">' . esc_html(__( 'Back To Actions', 'propertyhive' )) . '</a>

    		</div>

    	</div>';

    	do_action( 'propertyhive_admin_contact_action_options', $post->ID );
    	do_action( 'propertyhive_admin_post_action_options', $post->ID );

	    echo '<div class="propertyhive_meta_box" id="propertyhive_contact_actions_meta_box">';
	        
		    echo '<div class="options_group" style="padding-top:8px;">';

		    	$actions = apply_filters( 'propertyhive_admin_contact_actions', $actions, $post->ID );
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

?>
<script>

var viewing_selected_properties = {};
var offer_selected_properties = {};

var viewing_selected_negotiators = {<?php echo get_current_user_id(); ?>: { post_title: '<?php $user_data = get_userdata(get_current_user_id()); echo esc_js($user_data->display_name); ?>' } };

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
				//$('input#viewing_property_search').focus();
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

	// User / Login evebts
	$('a.login-action-submit').click(function(e)
	{
		e.preventDefault();

		// Validation
		if ($('#_password').val() == '')
		{
			$('#_password').focus();
			$('#_password').css('transition', 'background 0.6s');
			$('#_password').css('background', '#ff9999');
			setTimeout(function() { $('#_password').css('background', '#FFF'); }, 1000);
			return false;
		}

		$(this).attr('disabled', 'disabled');
		$(this).text('<?php echo esc_js(__( 'Saving', 'propertyhive' )); ?>...');

		// Validation passed. Submit form
		var data = {
            action:         'propertyhive_create_contact_login',
            contact_id:     <?php echo (int)$post->ID; ?>,
            password: 		$('#_password').val(),
            security:       '<?php echo esc_js(wp_create_nonce( 'create-login' )); ?>',
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

        		$('#success_actions').append('<strong><?php echo esc_html__( 'User login created successfully.', 'propertyhive' ); ?></strong><br><?php echo esc_html__( 'This contact can now login using their email address and password.', 'propertyhive' ); ?><br><br>');

        		$('#action_panel_create_login').stop().fadeOut(300, function()
				{
					$('#action_panel_success').stop().fadeIn(300);
				});

				$('a[href=\'#action_panel_create_login\']').hide();
        	}

        	$(that).attr('disabled', false);
        	$(that).text('<?php echo esc_js(__( 'Create Login', 'propertyhive' )); ?>');
        });
	})

	// Viewing events
	$('#viewing_property_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();

      		// Select first property in the list if one exists
      		if ( $.isNumeric( $('#viewing_search_property_results ul li:first-child a').attr('href') ) )
      		{
      			$('#viewing_search_property_results ul li:first-child a').trigger('click');
      		}

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
			$('#viewing_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
			$('#viewing_search_property_results').show();
			return false;
		}

		// Do search
		var data = {
            action:         'propertyhive_search_properties',
            keyword:    	keyword,
            security:       '<?php echo esc_js(wp_create_nonce( 'search-properties' )); ?>',
        };

        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#viewing_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + keyword + '\'</div>');
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

      		// Select first negotiator in the list if one exists
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
			$('#viewing_search_negotiator_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
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
	        	$('#viewing_search_negotiator_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + keyword + '\'</div>');
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
		if ($('#_viewing_start_date').val() == '')
		{
			$('#_viewing_start_date').focus();
			$('#_viewing_start_date').css('transition', 'background 0.6s');
			$('#_viewing_start_date').css('background', '#ff9999');
			setTimeout(function() { $('#_viewing_start_date').css('background', '#FFF'); }, 1000);
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
		$(this).text('<?php echo esc_js(__( 'Booking', 'propertyhive' )); ?>...');

		// Validation passed. Submit form
		var data = {
            action:         'propertyhive_book_viewing_contact',
            contact_id:     <?php echo (int)$post->ID; ?>,
            start_date: 	$('#_viewing_start_date').val(),
            start_time: 	$('#_viewing_start_time_hours').val() + ':' + $('#_viewing_start_time_minutes').val() + ':00',
            property_ids: 	Object.keys(viewing_selected_properties),
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

        		$('#success_actions').append('<a href="' + response.success.viewing.edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;"><?php echo esc_html__( 'Edit Viewing', 'propertyhive' ); ?></a>');
        		
        		for ( var i in response.success.properties )
        		{
        			$('#success_actions').append('<a href="' + response.success.properties[i].edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;"><?php echo esc_html__( 'Edit Property', 'propertyhive' ); ?></a>');
        		}

        		$('#action_panel_book_viewing').stop().fadeOut(300, function()
				{
					$('#action_panel_success').stop().fadeIn(300);
				});
        	}

        	$(that).attr('disabled', false);
        	$(that).text('<?php echo esc_js(__( 'Book Viewing', 'propertyhive' )); ?>');
        });
	});

	// Offer events
	$('#offer_property_search').on('keyup keypress', function(e)
	{
    	var keyCode = e.charCode || e.keyCode || e.which;
    	if (keyCode == 13)
    	{
      		event.preventDefault();

      		// Select first property in the list if one exists
      		if ( $.isNumeric( $('#offer_search_property_results ul li:first-child a').attr('href') ) )
      		{
      			$('#offer_search_property_results ul li:first-child a').trigger('click');
      		}
      		
      		return false;
    	}
  	});

	$('#offer_property_search').keyup(function(e)
	{
		e.preventDefault();

		var keyword = $(this).val();

		if (keyword.length == 0)
		{
			$('#offer_search_property_results').html('');
			$('#offer_search_property_results').hide();
			return false;
		}

		if (keyword.length < 3)
		{
			$('#offer_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'Enter', 'propertyhive' ); ?> ' + (3 - keyword.length ) + ' <?php echo esc_html__( 'more characters', 'propertyhive' ); ?>...</div>');
			$('#offer_search_property_results').show();
			return false;
		}

		// Do search
		var data = {
            action:         'propertyhive_search_properties',
            keyword:    	keyword,
            security:       '<?php echo esc_js(wp_create_nonce( 'search-properties' )); ?>',
        };

        $.post( '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', data, function(response) 
        {
        	if (response == '' || response.length == 0)
        	{
	        	$('#offer_search_property_results').html('<div style="padding:10px;"><?php echo esc_html__( 'No results found for', 'propertyhive' ); ?> \'' + keyword + '\'</div>');
	        }
	        else
	        {
	        	$('#offer_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
	        	for ( var i in response )
	        	{
	        		$('#offer_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
	        	}
	        }
			$('#offer_search_property_results').show();
        }, 'json');
	});

	$('#offer_property_search').on('blur', function(e)
	{
    	//$('#offer_search_applicant_results').hide();
  	});

	$('body').on('click', '#offer_search_property_results ul li a', function(e)
	{
		e.preventDefault();

		offer_selected_properties = []; // reset to only allow one property for now
		offer_selected_properties[$(this).attr('href')] = ({ post_title: $(this).text() });

		$('#offer_search_property_results').html('');
		$('#offer_search_property_results').hide();

		$('#offer_property_search').val('');

		offer_update_selected_properties();
	});

	$('body').on('click', 'a.offer-remove-property', function(e)
	{
		e.preventDefault();

		var property_id = $(this).attr('href');

		delete(offer_selected_properties[property_id]);

		offer_update_selected_properties();
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

		if (Object.keys(offer_selected_properties).length == 0)
		{
			$('#offer_property_search').focus();
			$('#offer_property_search').css('transition', 'background 0.6s');
			$('#offer_property_search').css('background', '#ff9999');
			setTimeout(function() { $('#offer_property_search').css('background', '#FFF'); }, 1000);
			return false;
		}

		$(this).attr('disabled', 'disabled');
		$(this).text('<?php echo esc_js(__( 'Saving', 'propertyhive' )); ?>...');

		// Validation passed. Submit form
		var data = {
            action:         'propertyhive_record_offer_contact',
            contact_id:     <?php echo (int)$post->ID; ?>,
            offer_date: 	$('#_offer_date').val(),
            offer_time: 	$('#_offer_time_hours').val() + ':' + $('#_offer_time_minutes').val() + ':00',
            property_ids: 	Object.keys(offer_selected_properties),
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

        		$('#success_actions').append('<a href="' + response.success.offer.edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;"><?php echo esc_html__( 'Edit Offer', 'propertyhive' ); ?></a>');
        		
        		for ( var i in response.success.properties )
        		{
        			$('#success_actions').append('<a href="' + response.success.properties[i].edit_link + '" class="button button-primary" style="width:100%; margin-bottom:5px;"><?php echo esc_html__( 'Edit Property', 'propertyhive' ); ?></a>');
        		}

        		$('#action_panel_record_offer').stop().fadeOut(300, function()
				{
					$('#action_panel_success').stop().fadeIn(300);
				});
        	}

        	$(that).attr('disabled', false);
        	$(that).text('<?php echo esc_js(__( 'Record Offer', 'propertyhive' )); ?>');
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
			jQuery('#viewing_selected_properties ul').append('<li><a href="' + i + '" class="viewing-remove-property" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + viewing_selected_properties[i].post_title + '</li>');
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
		jQuery('#viewing_selected_negotiators').html('<ul><li><em><?php echo esc_html__( 'Unattended', 'propertyhive' ); ?></em></li></ul>');
		jQuery('#viewing_selected_negotiators').show();
	}
}

function offer_update_selected_properties()
{
	if ( Object.keys(offer_selected_properties).length > 0 )
	{
		jQuery('#offer_selected_properties').html('<ul></ul>');
		for ( var i in offer_selected_properties )
		{
			jQuery('#offer_selected_properties ul').append('<li><a href="' + i + '" class="offer-remove-property" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + offer_selected_properties[i].post_title + '</li>');
		}
		jQuery('#offer_selected_properties').show();
	}
	else
	{
		jQuery('#offer_selected_properties').html('');
		jQuery('#offer_selected_properties').hide();
	}
}

</script>
<?php

    }
}
