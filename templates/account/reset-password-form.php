<?php
/**
 * Outputs the reset password form allowing users to reset their password
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/reset-password-form.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_reset_password_form" class="propertyhive-form reset-password-form" action="" method="post">

    <p><?php echo __( 'Enter a new password below.', 'propertyhive' ); ?></p>
 	
    <div id="resetPasswordError" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'The passwords entered must match', 'propertyhive' ); ?>
    </div>

    <div id="resetPasswordSuccess" style="display:none;" class="alert alert-success">
        <?php _e( 'Success. Your password has been changed successfully. You can now login with your new password.', 'propertyhive' ); ?><br>
        <a href="<?php echo get_permalink( get_option( 'propertyhive_applicant_login_page_id', '' ) ); ?>">Login</a>
    </div>

    <?php 
        do_action( 'propertyhive_reset_password_form_start' );

        ph_form_field( 'password_1', 
            array( 
                'type' => 'password',
                'label' => __( 'New Password', 'propertyhive' ),
                'required' => true
            )
        );

        ph_form_field( 'password_2', 
            array( 
                'type' => 'password',
                'label' => __( 'Re-enter Password', 'propertyhive' ),
                'required' => true
            )
        );

        do_action( 'propertyhive_reset_password_form' );
    ?>

    <input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['reset_key'] ); ?>" />
    <input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['reset_login'] ); ?>" />
    
    <input type="submit" value="<?php _e( 'Reset Password', 'propertyhive' ); ?>">

    <?php do_action( 'propertyhive_reset_password_form_end' ); ?>

</form>