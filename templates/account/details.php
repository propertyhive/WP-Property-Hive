<?php
/**
 * Outputs the 'My Details' form shown within 'My Account'
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/details.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_account_details_form" class="propertyhive-form account-details-form" action="" method="post">
 	
	<div id="detailsSuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php echo esc_html(__( 'Thank you. Your details have been updated successfully.', 'propertyhive' )); ?>
    </div>
    <div id="detailsError" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'An error occurred whilst trying to update your details. Please try again.', 'propertyhive' )); ?>
    </div>
    <div id="detailsValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'Please ensure all required fields have been completed', 'propertyhive' )); ?>
    </div>

    <?php do_action( 'propertyhive_account_details_form_start' ); ?>

    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <?php do_action( 'propertyhive_account_details_form' ); ?>

    <input type="submit" value="<?php echo esc_attr(__( 'Update Details', 'propertyhive' )); ?>">

    <?php do_action( 'propertyhive_account_details_form_end' ); ?>

</form>