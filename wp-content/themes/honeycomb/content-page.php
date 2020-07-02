<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package honeycomb
 */

?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	/**
	 * Functions hooked in to honeycomb_page add_action
	 *
	 * @hooked honeycomb_page_header          - 10
	 * @hooked honeycomb_page_content         - 20
	 * @hooked honeycomb_init_structured_data - 30
	 */
	do_action( 'honeycomb_page' );
	?>
</div><!-- #post-## -->
