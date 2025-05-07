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

<nav class="my-account-navigation">

	<ul>
	<?php
		$i = 0;
		foreach ( $pages as $id => $page )
		{
			echo '<li class="my-account-navigation-' . esc_attr($id) . '' . ( ( $i == 0 ) ? ' active' : '' ) . '"><a href="' . ( ( isset($page['href']) ) ? $page['href'] : '#my-account-' . esc_attr($id) ) . '">' . esc_html($page['name']) . '</a></li>';

			++$i;
		}
	?>
	</ul>

</nav>