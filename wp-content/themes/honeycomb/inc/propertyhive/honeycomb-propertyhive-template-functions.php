<?php
/**
 * Property Hive Template Functions.
 *
 * @package honeycomb
 */

if ( ! function_exists( 'honeycomb_before_content' ) ) {
	/**
	 * Before Content
	 * Wraps all Property Hive content in wrappers which match the theme markup
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function honeycomb_before_content() {
		?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
		<?php
	}
}

if ( ! function_exists( 'honeycomb_after_content' ) ) {
	/**
	 * After Content
	 * Closes the wrapping divs
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function honeycomb_after_content() {
		?>
			</main><!-- #main -->
		</div><!-- #primary -->

		<?php 
		$do_sidebar = true;

		if ( is_post_type_archive('property') )
		{
			$search_results_page_id = ph_get_page_id( 'search_results' );
			$template = get_page_template_slug( $search_results_page_id );
			
			if ( $template == 'template-fullwidth.php' )
			{
				$do_sidebar = false;
			}
		}

		if ($do_sidebar)
		{
			do_action( 'honeycomb_sidebar' );
		}
	}
}

if ( ! function_exists( 'honeycomb_propertyhive_template_single_map' ) ) {
	function honeycomb_propertyhive_template_single_map()
	{
		echo do_shortcode('[property_map]');
	}
}

if ( ! function_exists( 'honeycomb_sorting_wrapper' ) ) {
	/**
	 * Sorting wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function honeycomb_sorting_wrapper() {
		echo '<div class="honeycomb-sorting">';
	}
}

if ( ! function_exists( 'honeycomb_sorting_wrapper_close' ) ) {
	/**
	 * Sorting wrapper close
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function honeycomb_sorting_wrapper_close() {
		echo '</div>';
	}
}


if ( ! function_exists( 'honeycomb_propertyhive_pagination' ) ) {
	/**
	 * Honeycomb Property Hive Pagination
	 * determine whether or not to display the pagination.
	 *
	 * @since 1.0.0
	 */
	function honeycomb_propertyhive_pagination() {
		if ( honeycomb_properties_will_display() ) {
			propertyhive_pagination();
		}
	}
}

if ( ! function_exists( 'honeycomb_handheld_footer_bar' ) ) {
	/**
	 * Display a menu intended for use on handheld devices
	 *
	 * @since 1.0.0
	 */
	function honeycomb_handheld_footer_bar() {
		$links = array(
			'search'     => array(
				'priority' => 20,
				'callback' => 'honeycomb_handheld_footer_bar_search',
			),
		);

		$links = apply_filters( 'honeycomb_handheld_footer_bar_links', $links );
		?>
		<div class="honeycomb-handheld-footer-bar">
			<ul class="columns-<?php echo count( $links ); ?>">
				<?php foreach ( $links as $key => $link ) : ?>
					<li class="<?php echo esc_attr( $key ); ?>">
						<?php
						if ( $link['callback'] ) {
							call_user_func( $link['callback'], $key, $link );
						}
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}

if ( ! function_exists( 'honeycomb_handheld_footer_bar_search' ) ) {
	/**
	 * The search callback function for the handheld footer bar
	 *
	 * @since 1.0.0
	 */
	function honeycomb_handheld_footer_bar_search() {
		//echo '<a href="">' . esc_attr__( 'Search', 'honeycomb' ) . '</a>';
		//honeycomb_property_search();
	}
}
