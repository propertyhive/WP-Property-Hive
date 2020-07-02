<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package honeycomb
 */

?>

		</div><!-- .col-full -->
	</div><!-- #content -->

	<?php do_action( 'honeycomb_before_footer' ); ?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="col-full">

			<?php
			/**
			 * Functions hooked in to honeycomb_footer action
			 *
			 * @hooked honeycomb_footer_widgets - 10
			 * @hooked honeycomb_credit         - 20
			 */
			do_action( 'honeycomb_footer' ); ?>

		</div><!-- .col-full -->
	</footer><!-- #colophon -->

	<?php do_action( 'honeycomb_after_footer' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
