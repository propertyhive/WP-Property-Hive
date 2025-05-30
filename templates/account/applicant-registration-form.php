<?php
/**
 * Outputs the applicant registration form
 *
 * Override this template by copying it to yourtheme/propertyhive/account/applicant-registration-form.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_applicant_registration_form" class="propertyhive-form applicant-registration-form" action="" method="post">
 	
	<div id="registrationSuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php echo esc_html(__( 'Thank you. You have registered successfully.', 'propertyhive' )); ?>
    </div>
    <div id="registrationError" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'An error occurred whilst trying to register. Please try again.', 'propertyhive' )); ?>
    </div>
    <div id="registrationValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'Please ensure all required fields have been completed', 'propertyhive' )); ?>
    </div>

    <?php do_action( 'propertyhive_registration_form_start' ); ?>

    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <?php do_action( 'propertyhive_registration_form' ); ?>

    <input type="submit" value="<?php echo esc_attr(__( 'Register', 'propertyhive' )); ?>">

    <?php do_action( 'propertyhive_registration_form_end' ); ?>

</form>