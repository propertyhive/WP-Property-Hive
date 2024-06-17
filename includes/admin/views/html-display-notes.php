<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$args = array(
	'post_id' => (int)$post->ID,
	'type'      => 'propertyhive_note',
	'meta_query' => array(
		array(
			'key' => 'related_to',
			'value' => '"' . (int)$post->ID . '"',
			'compare' => 'LIKE',
		),
	)
);

if ( isset($_POST['pinned']) && (int)$_POST['pinned'] == 1 )
{
	$args['search'] = '"pinned";s:1:"1"';
}

$notes = get_comments( $args );

$pinned_notes = array();
$unpinned_notes = array();

if ( !empty($notes) )
{
	foreach( $notes as $note )
	{
		$comment_content = @unserialize($note->comment_content, ['allowed_classes' => false]);

		if ( $comment_content === false )
		{
			continue;
		}

		$note_body = 'Unknown note type';
		switch ( $comment_content['note_type'] )
		{
			case "mailout":
			{
				if ( isset($comment_content['method']) && $comment_content['method'] == 'email' && isset($comment_content['email_log_id']) )
				{
					$email_log = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "ph_email_log WHERE email_id = '" . $comment_content['email_log_id'] . "'" );

					if ( null !== $email_log )
					{
						$next_cron_run = '';
						$email_status = '';
						$note_suffix = '';
						switch ($email_log->status) {
							case '':
								$next_cron_run = $next_cron_run ?: propertyhive_human_time_difference( wp_next_scheduled( 'propertyhive_process_email_log' ) );
								$email_status  =  __( 'queued', 'propertyhive' );
								$note_suffix   = '<em>(' . __( 'Due to be sent', 'propertyhive' ) . ' ' . $next_cron_run . ')</em>';
								break;
							case 'fail1':
							case 'fail2':
								$email_status = '<b>' . __( 'failed', 'propertyhive' ) . '</b>';
								break;
						}
						$note_body = '';
						if ($section == 'property')
						{
							$note_body .= 'Included in ' . $email_status . ' email mailout to ' . get_the_title($email_log->contact_id) . '. ' . $note_suffix;
						}
						elseif ($section == 'contact')
						{
							$property_ids = @unserialize($email_log->property_ids, ['allowed_classes' => false]);
							if ( $property_ids !== false )
							{
								$note_body .= count($property_ids) . ' propert' . ( (count($property_ids) != 1) ? 'ies' : 'y' ) . ' included in ' . $email_status . ' email mailout. ' . $note_suffix;
							}
						}
						$note_body .= ' <a href="' . wp_nonce_url( admin_url('?view_propertyhive_email=' . $comment_content['email_log_id'] . '&email_id=' . $comment_content['email_log_id'] ), 'view-email' ) . '" target="_blank">View Mailout</a>';
					}
					else
					{
						$keep_logs_days = (string)apply_filters( 'propertyhive_keep_email_logs_days', '3650' ); // 10 years

					    // Revert back to 3650 days if anything other than numbers has been passed
					    // This prevent SQL injection and errors
					    if ( !preg_match("/^\d+$/", $keep_logs_days) )
					    {
					        $keep_logs_days = '3650';
					    }

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
						$note_body = $comment_content['action'] . '<br>From: ' . $comment_content['original_value'] . '<br>To: ' . $comment_content['new_value'];
						break;
					}
					case "property_availability_change":
					{
						$note_body = $comment_content['action'] . '<br>From: ' . $comment_content['original_value'] . '<br>To: ' . $comment_content['new_value'];
						break;
					}
					case "viewing_booked":
					{
						$note_body = '<a href="' . get_edit_post_link($comment_content['viewing_id']) . '">Viewing</a> booked';
						if ( isset($comment_content['property_id']) )
						{
							$property = new PH_Property((int)$comment_content['property_id']);
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "added_to_viewing":
					{
						$note_body = 'Added to <a href="' . get_edit_post_link($comment_content['viewing_id']) . '">viewing</a>';
						if ( isset($comment_content['property_id']) )
						{
							$property = new PH_Property((int)$comment_content['property_id']);
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "tenancy_booked":
					{
						$note_body = '<a href="' . get_edit_post_link($comment_content['tenancy_id']) . '">Tenancy</a> created';
						if ( isset($comment_content['property_id']) )
						{
							$property = new PH_Property((int)$comment_content['property_id']);
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "added_to_tenancy":
					{
						$note_body = 'Added to <a href="' . get_edit_post_link($comment_content['tenancy_id']) . '">tenancy</a>';
						if ( isset($comment_content['property_id']) )
						{
							$property = new PH_Property((int)$comment_content['property_id']);
							$note_body .= ' on <a href="' . get_edit_post_link($comment_content['property_id']) . '">' . $property->get_formatted_full_address() . '</a>';
						}
						break;
					}
					case "removed_from_tenancy":
					{
						if (isset($comment_content['tenancy_id']))
						{
							$note_body = 'Removed from <a href="' . get_edit_post_link($comment_content['tenancy_id']) . '">tenancy</a>';
						}
						else
						{
							$note_body = '<a href="' . get_edit_post_link($comment_content['contact_id']) . '">' . get_the_title($comment_content['contact_id']) . '</a> removed from tenancy';
						}
						break;
					}
					default:
					{
						$note_body = $comment_content['action'];
						break;
					}
				}
				break;
			}
			case "note":
			{
				$note_body = $comment_content['note'];

				// Regular expression pattern to match {{mention-ID|NAME}} or {{mention-ID}}
			    $pattern = '/\{\{mention-(\d+)(?:\|([^}]*))?\}\}/';
			    
			    // Callback function to replace the mentions with HTML links
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

				$note_body = nl2br($note_body);

				break;
			}
			case "unsubscribe":
			{
				$note_body = 'Contact unsubscribed themselves from emails';
				break;
			}
			case "status_change": // Believe this is only used by maintenance jobs add on
			{
				$note_body = 'Status changed from ' . $comment_content['previous_status'] . ' to ' . $comment_content['new_status'];
				break;
			}
			default:
			{
				$note_body = apply_filters( 'propertyhive_note_body', $note_body, $note );
			}
		}
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
			$pinned_notes[] = $note_content;
		}
		else
		{
			$unpinned_notes[] = $note_content;
		}
	}
}

$note_output = array_merge($pinned_notes, $unpinned_notes);

if ($section != 'enquiry')
{
	$note_output = apply_filters( 'propertyhive_notes', $note_output, $post );
	$note_output = apply_filters( 'propertyhive_' . $section . '_notes', $note_output, $post );
}
?>
<ul class="record_notes" style="max-height:300px; overflow-y:auto">
	<?php
	if ( !empty($note_output) )
	{
		foreach ( $note_output as $key => $note )
		{
			// Set pinned parameter for any notes added by third party plugins
			if ( !isset($note['pinned']) )
			{
				$note_output[$key]['pinned'] = 0;
			}
		}

		$datetime_format = get_option('date_format')." \a\\t ".get_option('time_format');

		// order by date desc. Older PHP versions don't support array_column so just can't order for them
		if ( function_exists('array_column') )
		{
			$pinned = array_column($note_output, 'pinned');
			$timestamp = array_column($note_output, 'timestamp');

			array_multisort($pinned, SORT_DESC,
							$timestamp, SORT_DESC,
							$note_output);
		}

		foreach ( $note_output as $note )
		{
			$note_classes = array( 'note' );

			$note_classes[] = 'note-type-' . $note['type'];
			?>
			<li rel="<?php echo absint( $note['id'] ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
				<div class="note_content<?php echo ($note['pinned'] == '1') ? ' pinned' : '' ?>">
					<?php echo wp_kses_post( $note['body'] ); ?>
				</div>
				<p class="meta">
					<abbr class="exact-date" title="<?php echo date("Y-m-d H:i:s", $note['timestamp']); ?>">
						<?php 
							
							$time_diff =  current_time( 'timestamp', 1 ) - $note['timestamp'];

							if ($time_diff > 86400) {
								echo date( $datetime_format, $note['timestamp'] );
							} else {
								printf( __( '%s ago', 'propertyhive' ), human_time_diff( $note['timestamp'], current_time( 'timestamp', 1 ) ) );
							}
						?>
					</abbr>
					<?php if ( $note['author'] !== __( 'Property Hive', 'propertyhive' ) && $note['author'] != '' ) printf( ' ' . __( 'by %s', 'propertyhive' ), $note['author'] );?>

					<a href="#" data-section="<?php echo $section; ?>" class="toggle_note_pinned"><?php _e( ( $note['pinned'] == '0' ) ? 'Pin To Top' : 'Unpin', 'propertyhive' ); ?></a>

					<?php if ( $note['type'] == 'note' ) { ?><a href="#" data-section="<?php echo $section; ?>" class="delete_note"><?php _e( 'Delete', 'propertyhive' ); ?></a><?php } ?>
					<?php
						if ( $post->ID != $note['post_id'] )
						{
					?>
					<br>
					<?php echo __( 'Note originally entered on', 'propertyhive' ); ?> <a href="<?php echo get_edit_post_link($note['post_id']); ?>" style="color:inherit;"><?php echo __( ucfirst(get_post_type($note['post_id'])), 'propertyhive' ); ?></a>
					<?php
						}
					?>
				</p>
			</li>
	<?php
		}
	}
	?>
	<li id="no_notes" style="text-align:center;<?php echo (!empty($note_output)) ? 'display:none;' : '';  ?>"><?php if ( isset($_POST['pinned']) && (int)$_POST['pinned'] == 1 ) { echo __( 'There are no pinned notes to display', 'propertyhive' ); }else{ echo __( 'There are no notes to display', 'propertyhive' ); } ?></li>
</ul>

<?php if ( !isset($_POST['pinned']) ) { ?>
<div class="add_note">
	<h4><?php _e( 'Add Note', 'propertyhive' ); ?></h4>
	<p>
		<textarea type="text" name="note" id="add_note" class="input-text" cols="20" rows="6" placeholder="Enter your note<?php if ( apply_filters('propertyhive_disable_notes_mention', false) === false ) { ?><br>Type <code style='background:#f9f9f9; border:1px solid #DDD; padding:0 2px; border-radius:5px; vertical-align:middle'>@</code> to tag a contact and property<?php } ?>"></textarea>
		<br>
		<input type="checkbox" name="pinned" id="pinned" value="1"> <?php _e( 'Pin Note', 'propertyhive' ); ?>
	</p>
	<p>
		<a href="#" class="add_note button-primary" data-section="<?php echo $section; ?>"><?php _e( 'Save Note', 'propertyhive' ); ?></a>
	</p>
</div>
<?php } ?>