<?php
/**
 * Outputs the 'Requirements' form shown within 'My Account'
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/requirements.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_account_requirements_form" class="propertyhive-form account-requirements-form" action="" method="post">
 	
	<div id="requirementsSuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php echo esc_html(__( 'Thank you. Your requirements have been updated successfully.', 'propertyhive' )); ?>
    </div>
    <div id="requirementsError" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'An error occurred whilst trying to update your requirements. Please try again.', 'propertyhive' )); ?>
    </div>
    <div id="requirementsValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'Please ensure all required fields have been completed', 'propertyhive' )); ?>
    </div>

    <?php do_action( 'propertyhive_account_requirements_form_start' ); ?>

    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <?php do_action( 'propertyhive_account_requirements_form' ); ?>

    <input type="submit" value="<?php echo esc_attr(__( 'Update Requirements', 'propertyhive' )); ?>">

    <?php do_action( 'propertyhive_account_requirements_form_end' ); ?>

</form>