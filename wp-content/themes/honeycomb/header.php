<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything upto <div id="content">
 *
 * @package honeycomb
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<?php
	/**
	 * Functions hooked in to honeycomb_before_header
	 *
	 * @hooked honeycomb_above_header_widget_region - 10
	 */
	do_action( 'honeycomb_before_header' ); ?>

	<header id="masthead" class="site-header" role="banner" style="<?php honeycomb_header_styles(); ?>">
		<div class="col-full">

			<?php
			/**
			 * Functions hooked into honeycomb_header action
			 *
			 * @hooked honeycomb_skip_links                       - 0
			 * @hooked honeycomb_social_icons                     - 10
			 * @hooked honeycomb_site_branding                    - 20
			 * @hooked honeycomb_primary_navigation               - 30
			 */
			do_action( 'honeycomb_header' ); ?>

		</div>
	</header><!-- #masthead -->

	<?php
	/**
	 * Functions hooked in to honeycomb_before_content
	 *
	 * @hooked honeycomb_below_header_widget_region - 10
	 * @hooked honeycomb_page_banner - 20
	 */
	do_action( 'honeycomb_before_content' ); ?>

	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">

		<?php
		do_action( 'honeycomb_content_top' );
