<?php
/**
 * Outputs the lost password form allowing users to reset their password
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/lost-password-form.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !empty( get_option( 'propertyhive_applicant_reset_password_page_id', '' ) ) ) {
?>

<form name="ph_lost_password_form" class="propertyhive-form lost-password-form" action="" method="post" style="display:none">

    <p><?php echo esc_html(__( 'Lost your password? Please enter your email address and we\'ll send you a link to create a new password via email.', 'propertyhive' )); ?></p>
 	
    <div id="lostPasswordError" style="display:none;" class="alert alert-danger alert-box">
        <?php echo esc_html(__( 'Email address not found. Please try again', 'propertyhive' )); ?>
    </div>

    <div id="lostPasswordSuccess" style="display:none;" class="alert alert-success">
        <?php echo esc_html(__( 'Success. A link to reset your password has been emailed to you', 'propertyhive' )); ?>
    </div>

    <?php 
        do_action( 'propertyhive_lost_password_form_start' );

        ph_form_field( 'email_address', 
            array( 
                'type' => 'email',
                'label' => __( 'Email Address', 'propertyhive' ),
                'required' => true
            )
        );

        do_action( 'propertyhive_lost_password_form' );
    ?>

    <input type="submit" value="<?php echo esc_attr(__( 'Reset Password', 'propertyhive' )); ?>">

    <?php do_action( 'propertyhive_lost_password_form_end' ); ?>

</form>
<?php } ?>