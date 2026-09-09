<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only display mode; parent CRM renderer enforces access, and note mutations verify their own nonce.
$propertyhive_has_pinned_filter = isset( $_POST['pinned'] );
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only display filter, with a scalar integer conversion.
$propertyhive_pinned_only = $propertyhive_has_pinned_filter && is_scalar( $_POST['pinned'] ) && 1 === (int) $_POST['pinned'];

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$args = array(
	'post_id' => (int)$post->ID,
	'type'      => 'propertyhive_note',
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Notes use the existing serialized related_to comment metadata to include cross-record relationships.
	'meta_query' => array(
		array(
			'key' => 'related_to',
			'value' => '"' . (int)$post->ID . '"',
			'compare' => 'LIKE',
		),
	)
);

if ( $propertyhive_pinned_only )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
	$args['search'] = '"pinned";s:1:"1"';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$notes = get_comments( $args );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$pinned_notes = array();
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$unpinned_notes = array();

if ( !empty($notes) )
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
	foreach( $notes as $note )
	{
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		$comment_content = @unserialize($note->comment_content, ['allowed_classes' => false]);

		if ( $comment_content === false )
		{
			continue;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		$note_body = 'Unknown note type';
		switch ( $comment_content['note_type'] )
		{
			case "mailout":
			{
				if ( isset($comment_content['method']) && $comment_content['method'] == 'email' && isset($comment_content['email_log_id']) )
				{
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable. Prepared single-row lookup in the plugin email queue; show current delivery status changed asynchronously by the mail worker.
					$email_log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ph_email_log WHERE email_id = %d", (int) $comment_content['email_log_id'] ) );

					if ( null !== $email_log )
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$next_cron_run = '';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$email_status = '';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_suffix = '';
						switch ($email_log->status) {
							case '':
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
								$next_cron_run = $next_cron_run ?: propertyhive_human_time_difference( wp_next_scheduled( 'propertyhive_process_email_log' ) );
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
								$email_status  =  __( 'queued', 'propertyhive' );
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
								$note_suffix   = '<em>(' . __( 'Due to be sent', 'propertyhive' ) . ' ' . $next_cron_run . ')</em>';
								break;
							case 'fail1':
							case 'fail2':
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
								$email_status = '<b>' . __( 'failed', 'propertyhive' ) . '</b>';
								break;
						}
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = '';
						if ($section == 'property')
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body .= 'Included in ' . $email_status . ' email mailout to ' . get_the_title($email_log->contact_id) . '. ' . $note_suffix;
						}
						elseif ($section == 'contact')
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$property_ids = @unserialize($email_log->property_ids, ['allowed_classes' => false]);
							if ( $property_ids !== false )
							{
								// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
								$note_body .= count($property_ids) . ' propert' . ( (count($property_ids) != 1) ? 'ies' : 'y' ) . ' included in ' . $email_status . ' email mailout. ' . $note_suffix;
							}
						}
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body .= ' <a href="' . wp_nonce_url( admin_url('?view_propertyhive_email=' . $comment_content['email_log_id'] . '&email_id=' . $comment_content['email_log_id'] ), 'view-email' ) . '" target="_blank">View Mailout</a>';
					}
					else
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$keep_logs_days = (string)apply_filters( 'propertyhive_keep_email_logs_days', '3650' ); // 10 years

					    // Revert back to 3650 days if anything other than numbers has been passed
					    // This prevent SQL injection and errors
					    if ( !preg_match("/^\d+$/", $keep_logs_days) )
					    {
					        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
					        $keep_logs_days = '3650';
					    }

						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = 'The details of the email have since been deleted as we remove details of emails sent more then ' . $keep_logs_days . ' days ago';
					}
				}
				break;
			}
			case "action":
			{
				switch ( $comment_content['action'] )
				{
					case "property_price_change":
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = $comment_content['action'] . '<br>From: ' . $comment_content['original_value'] . '<br>To: ' . $comment_content['new_value'];
						break;
					}
					case "property_availability_change":
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = $comment_content['action'] . '<br>From: ' . $comment_content['original_value'] . '<br>To: ' . $comment_content['new_value'];
						break;
					}
					case "viewing_booked":
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = '<a href="' . get_edit_post_link($comment_content['viewing_id']) . '">Viewing</a> booked';
						if ( isset($comment_content['property_id']) )
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$property = new PH_Property((int)$comment_content['property_id']);
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "added_to_viewing":
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = 'Added to <a href="' . get_edit_post_link($comment_content['viewing_id']) . '">viewing</a>';
						if ( isset($comment_content['property_id']) )
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$property = new PH_Property((int)$comment_content['property_id']);
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "tenancy_booked":
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = '<a href="' . get_edit_post_link($comment_content['tenancy_id']) . '">Tenancy</a> created';
						if ( isset($comment_content['property_id']) )
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$property = new PH_Property((int)$comment_content['property_id']);
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "added_to_tenancy":
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = 'Added to <a href="' . get_edit_post_link($comment_content['tenancy_id']) . '">tenancy</a>';
						if ( isset($comment_content['property_id']) )
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$property = new PH_Property((int)$comment_content['property_id']);
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "removed_from_tenancy":
					{
						if (isset($comment_content['tenancy_id']))
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body = 'Removed from <a href="' . get_edit_post_link($comment_content['tenancy_id']) . '">tenancy</a>';
						}
						else
						{
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$note_body = '<a href="' . get_edit_post_link($comment_content['contact_id']) . '">' . get_the_title($comment_content['contact_id']) . '</a> removed from tenancy';
						}
						break;
					}
					default:
					{
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
						$note_body = $comment_content['action'];
						break;
					}
				}
				break;
			}
			case "note":
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$note_body = $comment_content['note'];

				// Regular expression pattern to match {{mention-ID|NAME}} or {{mention-ID}}
			    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			    $pattern = '/\{\{mention-(\d+)(?:\|([^}]*))?\}\}/';
			    
			    // Callback function to replace the mentions with HTML links
			    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			    $callback = function($matches) 
			    {
			        $post_id = $matches[1];
			        $title = isset($matches[2]) ? $matches[2] : '';
			        $edit_url = get_edit_post_link($post_id);
			        $post_title = get_the_title($post_id);
			        if ( get_post_type($post_id) == 'property' )
			        {
			        	$property = new PH_Property((int)$post_id);
			        	$post_title = $property->get_formatted_full_address();
			        }
			        
			        // If the post exists, create the link
			        if ( $edit_url && $post_title ) 
			        {
			            return '<a href="' . esc_url($edit_url) . '">' . esc_html($post_title) . '</a>';
			        }
			        else 
			        {
			            if ( !empty($title) )
			            {
			            	return $title;
			            }
			            else
			            {
				            return '{{mention-' . $post_id . '}}';
				        }
			        }
			    };

			    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			    $note_body = preg_replace_callback($pattern, $callback, $note_body);

				/*$pattern = '/\{\{mention-(\d+)(\|.*)?\}\}/';
			    $replacement = function($matches) {
			        $post_id = $matches[1];
			        $title = trim(trim($matches[2], '|'));
			        $edit_url = get_edit_post_link($post_id);
			        $post_title = get_the_title($post_id);
			        if ( get_post_type($post_id) == 'property' )
			        {
			        	$property = new PH_Property((int)$post_id);
			        	$post_title = $property->get_formatted_full_address();
			        }
			        
			        // If the post exists, create the link
			        if ( $edit_url && $post_title ) 
			        {
			            return '<a href="' . esc_url($edit_url) . '">' . esc_html($post_title) . '</a>';
			        }
			        else 
			        {
			            if ( !empty($title) )
			            {
			            	return $title;
			            }
			            else
			            {
				            return '{{mention-' . $post_id . '}}';
				        }
			        }
			    };
			    $note_body = preg_replace_callback($pattern, $replacement, $note_body);*/

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$note_body = nl2br($note_body);

				break;
			}
			case "unsubscribe":
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$note_body = 'Contact unsubscribed themselves from emails';
				break;
			}
			case "status_change": // Believe this is only used by maintenance jobs add on
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$note_body = 'Status changed from ' . $comment_content['previous_status'] . ' to ' . $comment_content['new_status'];
				break;
			}
			default:
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$note_body = apply_filters( 'propertyhive_note_body', $note_body, $note );
			}
		}
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		$note_content = array(
			'id' => $note->comment_ID,
			'post_id' => $note->comment_post_ID,
			'type' => $comment_content['note_type'],
			'author' => $note->comment_author,
			'body' => $note_body,
			'timestamp' => strtotime($note->comment_date),
			'internal' => true,
			'pinned' => ( isset($comment_content['pinned']) && $comment_content['pinned'] == '1' ) ? '1' : '0',
		);

		if ( $note_content['pinned'] == '1' )
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			$pinned_notes[] = $note_content;
		}
		else
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			$unpinned_notes[] = $note_content;
		}
	}
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$note_output = array_merge($pinned_notes, $unpinned_notes);

if ($section != 'enquiry')
{
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
	$note_output = apply_filters( 'propertyhive_notes', $note_output, $post );
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
	$note_output = apply_filters( 'propertyhive_' . $section . '_notes', $note_output, $post );
}
?>
<ul class="record_notes" style="max-height:300px; overflow-y:auto">
	<?php
	if ( !empty($note_output) )
	{
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		foreach ( $note_output as $key => $note )
		{
			// Set pinned parameter for any notes added by third party plugins
			if ( !isset($note['pinned']) )
			{
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
				$note_output[$key]['pinned'] = 0;
			}
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		$datetime_format = get_option('date_format')." \a\\t ".get_option('time_format');

		// order by date desc. Older PHP versions don't support array_column so just can't order for them
		if ( function_exists('array_column') )
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			$pinned = array_column($note_output, 'pinned');
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			$timestamp = array_column($note_output, 'timestamp');

			array_multisort($pinned, SORT_DESC,
							$timestamp, SORT_DESC,
							$note_output);
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
		foreach ( $note_output as $note )
		{
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			$note_classes = array( 'note' );

			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
			$note_classes[] = 'note-type-' . $note['type'];
			?>
			<li rel="<?php echo absint( $note['id'] ) ; ?>" class="<?php echo esc_attr(implode( ' ', $note_classes )); ?>">
				<div class="note_content<?php echo ($note['pinned'] == '1') ? ' pinned' : '' ?>">
					<?php echo wp_kses_post( $note['body'] ); ?>
				</div>
				<p class="meta">
					<abbr class="exact-date" title="<?php echo esc_attr(gmdate("Y-m-d H:i:s", $note['timestamp'])); ?>">
						<?php 
							
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$time_diff =  current_time( 'timestamp', 1 ) - $note['timestamp'];

							if ($time_diff > 86400) {
								echo esc_html(gmdate( $datetime_format, $note['timestamp'] ));
							} else {
								/* translators: %s: Elapsed time. */
								printf( esc_html__( '%s ago', 'propertyhive' ), esc_html( human_time_diff( $note['timestamp'], current_time( 'timestamp', 1 ) ) ) );
							}
						?>
					</abbr> 
					<?php 
						if ( !empty($note['author']) && $note['author'] !== 'Property Hive' )
						{
							printf( 
								/* translators: %s: author name */
								esc_html__( 'by %s', 'propertyhive' ),
								esc_html($note['author']) 
							);
						}
					?>

					<a href="#" data-section="<?php echo esc_attr($section); ?>" class="toggle_note_pinned"><?php echo ( $note['pinned'] == '0' ? esc_html__( 'Pin To Top', 'propertyhive' ) : esc_html__( 'Unpin', 'propertyhive' ) ); ?></a>

					<?php if ( $note['type'] == 'note' ) { ?><a href="#" data-section="<?php echo esc_attr($section); ?>" class="delete_note"><?php echo esc_html(__( 'Delete', 'propertyhive' )); ?></a><?php } ?>
					<?php
						if ( $post->ID != $note['post_id'] )
						{
							echo '<br>';
							$post_type_object = get_post_type_object( get_post_type( $note['post_id'] ) );
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
							$post_type_label  = $post_type_object ? $post_type_object->labels->singular_name : get_post_type( $note['post_id'] );
							
							printf(
								/* translators: %s: linked post type label, for example "Property" */
								esc_html__( 'Note originally entered on %s', 'propertyhive' ),
								'<a href="' . esc_url( get_edit_post_link( $note['post_id'] ) ) . '" style="color:inherit;">' . esc_html( $post_type_label ) . '</a>'
							);
						}
					?>
				</p>
			</li>
	<?php
		}
	}
	?>
	<li id="no_notes" style="text-align:center;<?php echo (!empty($note_output)) ? 'display:none;' : '';  ?>"><?php if ( $propertyhive_pinned_only ) { echo esc_html(__( 'There are no pinned notes to display', 'propertyhive' )); }else{ echo esc_html(__( 'There are no notes to display', 'propertyhive' )); } ?></li>
</ul>

<?php if ( ! $propertyhive_has_pinned_filter ) { ?>
<div class="add_note">
	<h4><?php esc_html_e( 'Add Note', 'propertyhive' ); ?></h4>
	<p>
		<textarea type="text" name="note" id="add_note" class="input-text" cols="20" rows="6" placeholder="Enter your note<?php if ( apply_filters('propertyhive_disable_notes_mention', false) === false ) { ?><br>Type <code style='background:#f9f9f9; border:1px solid #DDD; padding:0 2px; border-radius:5px; vertical-align:middle'>@</code> to tag a contact and property<?php } ?>"></textarea>
		<br>
		<input type="checkbox" name="pinned" id="pinned" value="1"> <?php echo esc_html(__( 'Pin Note', 'propertyhive' )); ?>
	</p>
	<p>
		<a href="#" class="add_note button-primary" data-section="<?php echo esc_attr($section); ?>"><?php echo esc_html(__( 'Save Note', 'propertyhive' )); ?></a>
	</p>
</div>
<?php } ?>