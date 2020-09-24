<ul class="record_notes" style="max-height:300px; overflow-y:auto">
	<?php

	if ( !empty($note_output) )
	{
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

					<a href="#" class="toggle_note_pinned"><?php _e( ( $note['pinned'] == '0' ) ? 'Pin To Top' : 'Unpin', 'propertyhive' ); ?></a>

					<?php if ( $note['type'] == 'note' ) { ?><a href="#" class="delete_note"><?php _e( 'Delete', 'propertyhive' ); ?></a><?php } ?>
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
	<li id="no_notes" style="text-align:center;<?php echo (!empty($note_output)) ? 'display:none;' : '';  ?>"><?php echo __( 'There are no notes to display', 'propertyhive' ); ?></li>
</ul>


<div class="add_note">
	<h4><?php _e( 'Add Note', 'propertyhive' ); ?></h4>
	<p>
		<textarea type="text" name="note" id="add_note" class="input-text" cols="20" rows="6"></textarea>
		<br>
		<input type="checkbox" name="pinned" id="pinned" value="1"> <?php _e( 'Pin Note', 'propertyhive' ); ?>
	</p>
	<p>
		<a href="#" class="add_note button"><?php _e( 'Add', 'propertyhive' ); ?></a>
	</p>
</div>