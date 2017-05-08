<form name="ph_applicant_registration_form" class="applicant-registration-form" action="" method="post">
 	
	<div id="enquirySuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php _e( 'Thank you. You have registered succesfully.', 'propertyhive' ); ?>
    </div>
    <div id="enquiryError" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'An error occurred whilst trying to register. Please try again.', 'propertyhive' ); ?>
    </div>
    <div id="enquiryValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'Please ensure all required fields have been completed', 'propertyhive' ); ?>
    </div>

    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <input type="submit" value="<?php _e( 'Register', 'propertyhive' ); ?>">

</form>