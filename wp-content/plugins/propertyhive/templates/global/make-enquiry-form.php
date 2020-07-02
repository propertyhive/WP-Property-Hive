<?php
/**
 * Property enquiry form
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_property_enquiry" class="property-enquiry-form" action="" method="post">
    
    <div id="enquirySuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php _e( 'Thank you. Your enquiry has been sent successfully.', 'propertyhive' ); ?>
    </div>
    <div id="enquiryError" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'An error occurred whilst trying to send your enquiry. Please try again.', 'propertyhive' ); ?>
    </div>
    <div id="enquiryValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'Please ensure all required fields have been completed', 'propertyhive' ); ?>
    </div>
    
    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <input type="submit" value="<?php _e( 'Submit', 'propertyhive' ); ?>">

</form>