<?php
/**
 * Outputs the login form allowing users access to their account
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/login-form.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_login_form" class="propertyhive-form login-form" action="" method="post">
 	
    <div id="loginError" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'Invalid details provided. Please try again', 'propertyhive' ); ?>
    </div>

    <?php 
        do_action( 'propertyhive_login_form_start' );

        ph_form_field( 'email_address', 
            array( 
                'type' => 'email',
                'label' => __( 'Email Address', 'propertyhive' ),
                'required' => true
            )
        );

        ph_form_field( 'password', 
            array( 
                'type' => 'password',
                'label' => __( 'Password', 'propertyhive' ),
                'required' => true
            )
        );

        do_action( 'propertyhive_login_form' );
    ?>

    <input type="submit" value="<?php _e( 'Login', 'propertyhive' ); ?>">

    <?php if ( !empty( get_option( 'propertyhive_applicant_reset_password_page_id', '' ) ) ) { ?>
    <div class="ph-forgot-password-link"><a href="" class="ph-forgot-password"><?php echo __( 'Forgot your password?', 'propertyhive' ); ?></a></div>
    <?php } ?>

    <?php do_action( 'propertyhive_login_form_end' ); ?>

</form>

<?php ph_get_template( 'account/lost-password-form.php' ); ?>