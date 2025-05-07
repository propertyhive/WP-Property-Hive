<?php
/**
 * Displayed when a user is trying to access a page they shouldn't be (i.e. when trying to view 'My Account' but they're not logged in)
 *
 * Override this template by copying it to yourtheme/propertyhive/account/invalid-access.php.
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<p class="propertyhive-info"><?php 
	echo esc_html(__( 'You must be logged in to view this.', 'propertyhive' )); 
?></p>