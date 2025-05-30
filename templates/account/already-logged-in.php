<?php
/**
 * Displayed when user is trying to register but they're already logged in
 *
 * Override this template by copying it to yourtheme/propertyhive/account/already-logged-in.php.
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<p><?php 
	_e( 'It looks like you\'re already logged in.', 'propertyhive' ); 
?></p>

<?php 
	if ( get_option( 'propertyhive_my_account_page_id', '' ) != '' )
	{
		// Login/registration is enabled. Show link to 'My Account'

		echo '<p>';

		echo '<a href="' . esc_url(get_permalink( ph_get_page_id( 'my_account' ) )) . '">' . esc_html(__( 'Go To My Account', 'propertyhive' )) . '</a>';

		echo '</p>';
	}
?>