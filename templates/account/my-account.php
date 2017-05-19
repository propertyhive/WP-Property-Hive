<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/my-account.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//ph_print_notices();

?>

<div class="propertyhive-my-account">

	<?php
		/**
		 * propertyhive_my_account_content hook
		 *
         * @hooked propertyhive_my_account_navigation - 10
         * @hooked propertyhive_my_account_sections - 20
		 */
		do_action( 'propertyhive_my_account_content' );
	?>

</div>
