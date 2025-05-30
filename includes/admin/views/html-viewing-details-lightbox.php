<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="propertyhive-lightbox-details">

	<div class="propertyhive-lightbox-left">

		<!-- VIEWING DETAILS -->
		<div class="propertyhive-lightbox-viewing-details">

			<h3><?php echo esc_html(__( 'Viewing Details', 'propertyhive' )); ?></h3>

			<div id="propertyhive_viewing_details_meta_box_container">
				<?php
					$readonly = true;
			        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-details-meta-box.php' );
			    ?>
			</div>

			<?php
				$readonly = true;
		        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-event-meta-box.php' );
		    ?>

		</div>

		<!-- PROPERTY DETAILS -->
		<div class="propertyhive-lightbox-property-details">

			<h3><?php echo esc_html(__( 'Property Details', 'propertyhive' )); ?></h3>

			<?php
				$property_id = $viewing->property_id;
		        include( PH()->plugin_path() . '/includes/admin/views/html-lightbox-property-details.php' );
		    ?>

		</div>

		<!-- OWNER/LANDLORD DETAILS -->
		<div class="propertyhive-lightbox-contact-details">

			<h3><?php echo esc_html(__( ( get_post_meta((int)$viewing->property_id, '_department', TRUE) == 'residential-lettings' ? 'Landlord' : 'Owner' ) . ' Details', 'propertyhive' )); ?></h3>

			<?php
				$contact_ids = get_post_meta((int)$viewing->property_id, '_owner_contact_id', TRUE);
		        include( PH()->plugin_path() . '/includes/admin/views/html-lightbox-contact-details.php' );
		    ?>

		</div>

		<!-- APPLICANT DETAILS -->
		<div class="propertyhive-lightbox-contact-details">

			<h3><?php echo esc_html(__( 'Applicant Details', 'propertyhive' )); ?></h3>

			<?php
				$contact_ids = get_post_meta($post->ID, '_applicant_contact_id');
		        include( PH()->plugin_path() . '/includes/admin/views/html-lightbox-contact-details.php' );
		    ?>

		</div>

	</div>

	<!-- NOTES -->
	<div class="propertyhive-lightbox-notes">

		<h3><?php echo esc_html(__( 'Notes', 'propertyhive' )); ?></h3>

		<?php
			$section = 'viewing';

	        echo '<div class="propertyhive-notes-container" id="propertyhive_' . $section . '_notes_container">';
	            include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );
	        echo '</div>';
	    ?>

	</div>

	<!-- ACTIONS -->
	<div class="propertyhive-lightbox-actions">

		<h3><?php echo esc_html(__( 'Actions', 'propertyhive' )); ?></h3>

		<div id="propertyhive_viewing_actions_meta_box_container">
			<?php include( PH()->plugin_path() . '/includes/admin/views/html-viewing-actions.php' ); ?>
		</div>
	
	</div>

	<div style="clear:both"></div>

	<!-- BUTTONS -->
	<div class="propertyhive-lightbox-buttons">
		<a href="" class="button button-close"><?php echo esc_html(__( 'Close', 'propertyhive' )); ?></a>
		<a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>" class="button button-primary"><?php echo esc_html(__( 'Go To Viewing', 'propertyhive' )); ?></a>
		<a href="" class="button button-prev" style="display:none">&lt;</a>
		<a href="" class="button button-next" style="display:none">&gt;</a>
	</div>

</div>