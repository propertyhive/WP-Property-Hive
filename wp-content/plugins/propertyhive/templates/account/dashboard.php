<?php
/**
 * Outputs the dashboard contents
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/dashboard.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $current_user;
?>

<p><?php
	_e( 'Hello', 'propertyhive' );
	echo ' <strong>' . $current_user->display_name . '</strong>.';
?></p>

<p><?php
	_e('From within your account you can manage your details and amend your requirements.', 'propertyhive' );
?></p>

<?php do_action( 'propertyhive_account_dashboard' );