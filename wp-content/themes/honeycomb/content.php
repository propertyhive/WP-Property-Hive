<?php
/**
 * Template used to display post content.
 *
 * @package honeycomb
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
	/**
	 * Functions hooked in to honeycomb_loop_post action.
	 *
	 * @hooked honeycomb_post_header          - 10
	 * @hooked honeycomb_post_meta            - 20
	 * @hooked honeycomb_post_content         - 30
	 * @hooked honeycomb_init_structured_data - 40
	 */
	do_action( 'honeycomb_loop_post' );
	?>

</article><!-- #post-## -->
