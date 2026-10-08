<?php
/**
 * Outputs the 'My Account' navigation
 *
 * Override this template by copying it to yourtheme/propertyhive/account/navigation.php.
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<nav class="my-account-navigation" aria-label="<?php esc_attr_e( 'My account', 'propertyhive' ); ?>">

	<ul>
	<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
		$i = 0;
		foreach ( $pages as $id => $page )
		{
			echo '<li class="my-account-navigation-' . esc_attr($id) . '' . ( ( $i == 0 ) ? ' active' : '' ) . '"><a' . ( ( $i == 0 ) ? ' aria-current="true"' : '' ) . ' href="' . ( ( isset($page['href']) ) ? esc_url( $page['href'] ) : '#my-account-' . esc_attr($id) ) . '">' . esc_html($page['name']) . '</a></li>';

			++$i;
		}
	?>
	</ul>

</nav>