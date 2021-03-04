<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="propertyhive-lightbox-details">

	<div class="propertyhive-lightbox-left">

		<!-- VIEWING DETAILS -->
		<div class="propertyhive-lightbox-property-details">

			<h3><?php echo __( 'Viewing Details', 'propertyhive' ); ?></h3>

			<div id="propertyhive_viewing_details_meta_box_container">
				<?php
			        include( PH()->plugin_path() . '/includes/admin/views/html-viewing-details-meta-box.php' );
			    ?>
			</div>

		</div>

		<!-- PROPERTY DETAILS -->
		<div class="propertyhive-lightbox-property-details">

			<h3><?php echo __( 'Property Details', 'propertyhive' ); ?></h3>

			<?php
				$property_id = $viewing->property_id;
		        include( PH()->plugin_path() . '/includes/admin/views/html-lightbox-property-details.php' );
		    ?>

		</div>

		<!-- APPLICANT DETAILS -->
		<div class="propertyhive-lightbox-applicant-details">

			<h3><?php echo __( 'Applicant Details', 'propertyhive' ); ?></h3>

			<?php
				$contact_ids = get_post_meta($post->ID, '_applicant_contact_id');
		        include( PH()->plugin_path() . '/includes/admin/views/html-lightbox-contact-details.php' );
		    ?>

		</div>

	</div>

	<!-- NOTES -->
	<div class="propertyhive-lightbox-notes">

		<h3><?php echo __( 'Notes', 'propertyhive' ); ?></h3>

		<?php
			$section = 'viewing';

	        echo '<div class="propertyhive-notes-container" id="propertyhive_' . $section . '_notes_container">';
	            include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );
	        echo '</div>';
	    ?>

	</div>

	<!-- ACTIONS -->
	<div class="propertyhive-lightbox-actions">

		<h3><?php echo __( 'Actions', 'propertyhive' ); ?></h3>

		<div id="propertyhive_viewing_actions_meta_box_container">
			<?php include( PH()->plugin_path() . '/includes/admin/views/html-viewing-actions.php' ); ?>
		</div>
	
	</div>

	<div style="clear:both"></div>

	<!-- BUTTONS -->
	<div class="propertyhive-lightbox-buttons">
		<a href="" class="button button-close"><?php echo __( 'Close', 'propertyhive' ); ?></a>
		<a href="<?php echo get_edit_post_link($post->ID); ?>" class="button button-primary"><?php echo __( 'Go To Viewing', 'propertyhive' ); ?></a>
		<a href="" class="button button-prev">&lt;</a>
		<a href="" class="button button-next">&gt;</a>
	</div>

</div>