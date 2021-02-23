<div id="propertyhive-viewing-details" class="postbox " style="display: block;">
	<div class="inside">
		<div class="propertyhive_meta_box">
			<div class="options_group">
			<p class="form-field">
				<label for="">Status</label><?php echo ucwords(str_replace("_", " ", $the_viewing->status)); ?>
			</p>
			<p class="form-field">
				<label for="">Start Date / Time</label><?php echo date("H:i jS F Y", strtotime($the_viewing->start_date_time)); ?>
			</p>
			<p class="form-field">
				<label for="">Attended By</label>
				<?php
				$negotiator_ids = get_post_meta( $post_id, '_negotiator_id' );
            	if ( is_array($negotiator_ids) && !empty($negotiator_ids) )
            	{
            		$negotiators = array();
            		foreach ( $negotiator_ids as $negotiator_id )
            		{
            			$user_info = get_userdata($negotiator_id);
            			$negotiators[] = $user_info->display_name;
            		}
            		echo implode(", ", $negotiators);
            	}
            	else
            	{
            		echo '<em>- ' . __( 'Unattended', 'propertyhive' ) . ' -</em>';
            	}
				?>
				</p>
			</div>
		</div>
	</div>
</div>