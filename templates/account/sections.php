<?php
/**
 * Outputs the 'My Account' main sections that relate to the navigation/tabs
 *
 * Override this template by copying it to yourtheme/propertyhive/account/sections.php.
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="my-account-sections">

	<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
		$i = 0;
		foreach ( $pages as $id => $page )
		{
			echo '<div id="my-account-' . esc_attr($id) . '"' . ( ( $i > 0 ) ? ' style="display:none;"' : '' ) . '>';

				do_action( 'propertyhive_my_account_section_' . $id );

			echo '</div>';

			++$i;
		}
	?>

</div>