<?php
/**
 * Honeycomb template functions.
 *
 * @package honeycomb
 */

if ( ! function_exists( 'honeycomb_page_banner' ) ) {
	/**
	 * Honeycomb display page banner
	 *
	 * @since  1.0.0
	 */
	function honeycomb_page_banner() {
		global $post;

		if ( !isset($post->ID) )
		{
			return false;
		}

		$post_id = $post->ID;
		if ( is_post_type_archive('property') )
		{
			$post_id = ph_get_page_id( 'search_results' );
		}

		$banner_type = get_post_meta( $post_id, '_banner_type', TRUE );

		switch ( $banner_type )
		{
			case "map":
			{
				if ( class_exists( 'PH_Map_Search' ) )
				{
					$query = '';

					if ( is_post_type_archive('property') )
					{
						global $wp_query;

				        $query = $wp_query->request;

				        // Remove limit
				        $last_limit_pos = strrpos(strtolower($query), "limit");
				        if ($last_limit_pos !== FALSE)
				        {
				            // We found a limit
				            $query = substr($query, 0, $last_limit_pos - 1); // -1 because strrpos return starts at zero
				        }

				        $query = base64_encode($query);
					}

					echo do_shortcode('[propertyhive_map_search scrollwheel="false" query="' . $query . '"]');
				}
				else
				{
					echo __( 'The Property Hive Map Search add on does not exist or is not activated', 'honeycomb' );
				}
				break;
			}
			case "revslider":
			{
				if ( class_exists( 'RevSlider' ) ) 
				{
					$rev_slider = esc_html( get_post_meta( $post_id, '_banner_rev_slider', TRUE ) ); 
					putRevSlider($rev_slider);
				}
				else
				{
					echo __( 'Revolution Slider does not exist or is not activated', 'honeycomb' );
				}
				break;
			}
			case "featured":
			{
				if ( has_post_thumbnail($post_id) ) 
				{
					$url = get_the_post_thumbnail_url($post_id, 'full');
					echo '<div class="featured-image-page-banner" style="background-image:url(\'' . $url . '\');"></div>';
				}
				break;
			}
		}
	}
}

if ( ! function_exists( 'honeycomb_display_comments' ) ) {
	/**
	 * Honeycomb display comments
	 *
	 * @since  1.0.0
	 */
	function honeycomb_display_comments() {
		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || '0' != get_comments_number() ) :
			comments_template();
		endif;
	}
}

if ( ! function_exists( 'honeycomb_comment' ) ) {
	/**
	 * Honeycomb comment template
	 *
	 * @param array $comment the comment array.
	 * @param array $args the comment args.
	 * @param int   $depth the comment depth.
	 * @since 1.0.0
	 */
	function honeycomb_comment( $comment, $args, $depth ) {
		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
		?>
		<<?php echo esc_attr( $tag ); ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ) ?> id="comment-<?php comment_ID() ?>">
		<div class="comment-body">
		<div class="comment-meta commentmetadata">
			<div class="comment-author vcard">
			<?php echo get_avatar( $comment, 128 ); ?>
			<?php printf( wp_kses_post( '<cite class="fn">%s</cite>', 'honeycomb' ), get_comment_author_link() ); ?>
			</div>
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<em class="comment-awaiting-moderation"><?php esc_attr_e( 'Your comment is awaiting moderation.', 'honeycomb' ); ?></em>
				<br />
			<?php endif; ?>

			<a href="<?php echo esc_url( htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ); ?>" class="comment-date">
				<?php echo '<time datetime="' . get_comment_date( 'c' ) . '">' . get_comment_date() . '</time>'; ?>
			</a>
		</div>
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="div-comment-<?php comment_ID() ?>" class="comment-content">
		<?php endif; ?>
		<div class="comment-text">
		<?php comment_text(); ?>
		</div>
		<div class="reply">
		<?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		<?php edit_comment_link( __( 'Edit', 'honeycomb' ), '  ', '' ); ?>
		</div>
		</div>
		<?php if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
	<?php
	}
}

if ( ! function_exists( 'honeycomb_footer_widgets' ) ) {
	/**
	 * Display the footer widget regions
	 *
	 * @since  1.0.0
	 * @return  void
	 */
	function honeycomb_footer_widgets() {
		if ( is_active_sidebar( 'footer-4' ) ) {
			$widget_columns = apply_filters( 'honeycomb_footer_widget_regions', 4 );
		} elseif ( is_active_sidebar( 'footer-3' ) ) {
			$widget_columns = apply_filters( 'honeycomb_footer_widget_regions', 3 );
		} elseif ( is_active_sidebar( 'footer-2' ) ) {
			$widget_columns = apply_filters( 'honeycomb_footer_widget_regions', 2 );
		} elseif ( is_active_sidebar( 'footer-1' ) ) {
			$widget_columns = apply_filters( 'honeycomb_footer_widget_regions', 1 );
		} else {
			$widget_columns = apply_filters( 'honeycomb_footer_widget_regions', 0 );
		}

		if ( $widget_columns > 0 ) : ?>

			<div class="footer-widgets col-<?php echo intval( $widget_columns ); ?> fix">

				<?php
				$i = 0;
				while ( $i < $widget_columns ) : $i++;
					if ( is_active_sidebar( 'footer-' . $i ) ) : ?>

						<div class="block footer-widget-<?php echo intval( $i ); ?>">
							<?php dynamic_sidebar( 'footer-' . intval( $i ) ); ?>
						</div>

					<?php endif;
				endwhile; ?>

			</div><!-- /.footer-widgets  -->

		<?php endif;
	}
}

if ( ! function_exists( 'honeycomb_credit' ) ) {
	/**
	 * Display the theme credit
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function honeycomb_credit() {
		?>
		<div class="site-info">
			<?php echo esc_html( apply_filters( 'honeycomb_copyright_text', $content = '&copy; ' . get_bloginfo( 'name' ) . ' ' . date( 'Y' ) ) ); ?>
			<?php if ( apply_filters( 'honeycomb_credit_link', true ) ) { ?>
			<br /> <?php echo __(sprintf( esc_attr__( '%1$s powered by %2$s.', 'honeycomb' ), 'Honeycomb', '<a href="https://wp-property-hive.com" title="Property Hive - The Leading WordPress Platform For Estate Agency Websites" rel="author">Property Hive</a>' ), 'propertyhive'); ?>
			<?php } ?>
		</div><!-- .site-info -->
		<?php
	}
}

if ( ! function_exists( 'honeycomb_above_header_widget_region' ) ) {
	/**
	 * Display above header widget region
	 *
	 * @since  1.0.0
	 */
	function honeycomb_above_header_widget_region() {
		if ( is_active_sidebar( 'header-1' ) ) {
		?>
		<div class="header-above-widget-region" role="complementary">
			<div class="col-full">
				<?php dynamic_sidebar( 'header-1' ); ?>
			</div>
		</div>
		<?php
		}
	}
}

if ( ! function_exists( 'honeycomb_below_header_widget_region' ) ) {
	/**
	 * Display below header widget region
	 *
	 * @since  1.0.0
	 */
	function honeycomb_below_header_widget_region() {
		if ( is_active_sidebar( 'header-2' ) ) {
		?>
		<div class="header-below-widget-region" role="complementary">
			<div class="col-full">
				<?php dynamic_sidebar( 'header-2' ); ?>
			</div>
		</div>
		<?php
		}
	}
}

if ( ! function_exists( 'honeycomb_site_branding' ) ) {
	/**
	 * Site branding wrapper and display
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function honeycomb_site_branding() {
		?>
		<div class="site-branding">
			<?php honeycomb_site_title_or_logo(); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'honeycomb_site_title_or_logo' ) ) {
	/**
	 * Display the site title or logo
	 *
	 * @since 2.1.0
	 * @param bool $echo Echo the string or return it.
	 * @return string
	 */
	function honeycomb_site_title_or_logo( $echo = true ) {
		if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
			$logo = get_custom_logo();
			$html = is_home() ? '<h1 class="logo">' . $logo . '</h1>' : $logo;
		} elseif ( function_exists( 'jetpack_has_site_logo' ) && jetpack_has_site_logo() ) {
			// Copied from jetpack_the_site_logo() function.
			$logo    = site_logo()->logo;
			$logo_id = get_theme_mod( 'custom_logo' ); // Check for WP 4.5 Site Logo
			$logo_id = $logo_id ? $logo_id : $logo['id']; // Use WP Core logo if present, otherwise use Jetpack's.
			$size    = site_logo()->theme_size();
			$html    = sprintf( '<a href="%1$s" class="site-logo-link" rel="home" itemprop="url">%2$s</a>',
				esc_url( home_url( '/' ) ),
				wp_get_attachment_image(
					$logo_id,
					$size,
					false,
					array(
						'class'     => 'site-logo attachment-' . $size,
						'data-size' => $size,
						'itemprop'  => 'logo'
					)
				)
			);

			$html = apply_filters( 'jetpack_the_site_logo', $html, $logo, $size );
		} else {
			$tag = is_home() ? 'h1' : 'div';

			$html = '<' . esc_attr( $tag ) . ' class="beta site-title"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a></' . esc_attr( $tag ) .'>';

			if ( '' !== get_bloginfo( 'description' ) ) {
				$html .= '<p class="site-description">' . esc_html( get_bloginfo( 'description', 'display' ) ) . '</p>';
			}
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;
	}
}

if ( ! function_exists( 'honeycomb_primary_navigation' ) ) {
	/**
	 * Display Primary Navigation
	 *
	 * @since  1.0.0
	 * @return void
	 */
	function honeycomb_primary_navigation() {
		?>
		<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_html_e( 'Primary Navigation', 'honeycomb' ); ?>">
		<button class="menu-toggle" aria-controls="site-navigation" aria-expanded="false"><span><?php echo esc_attr( apply_filters( 'honeycomb_menu_toggle_text', __( 'Menu', 'honeycomb' ) ) ); ?></span></button>
			<?php
			wp_nav_menu(
				array(
					'theme_location'	=> 'primary',
					'container_class'	=> 'primary-navigation',
					)
			);

			wp_nav_menu(
				array(
					'theme_location'	=> 'handheld',
					'container_class'	=> 'handheld-navigation',
					)
			);
			?>
		</nav><!-- #site-navigation -->
		<?php
	}
}

if ( ! function_exists( 'honeycomb_skip_links' ) ) {
	/**
	 * Skip links
	 *
	 * @since  1.4.1
	 * @return void
	 */
	function honeycomb_skip_links() {
		?>
		<a class="skip-link screen-reader-text" href="#site-navigation"><?php esc_attr_e( 'Skip to navigation', 'honeycomb' ); ?></a>
		<a class="skip-link screen-reader-text" href="#content"><?php esc_attr_e( 'Skip to content', 'honeycomb' ); ?></a>
		<?php
	}
}

if ( ! function_exists( 'honeycomb_page_header' ) ) {
	/**
	 * Display the post header with a link to the single post
	 *
	 * @since 1.0.0
	 */
	function honeycomb_page_header() {
		global $post;

		$post_id = $post->ID;
		if ( is_post_type_archive('property') )
		{
			$post_id = ph_get_page_id( 'search_results' );
		}

		$banner_type = get_post_meta( $post_id, '_banner_type', TRUE );

		$show_post_thumbnail = true;
		if ( $banner_type == 'featured' ) { $show_post_thumbnail = false; } // don't show post thumbnail if it's already been used as the page banner
		?>
		<header class="entry-header">
			<?php
			if ( $show_post_thumbnail ) honeycomb_post_thumbnail( 'full' );
			the_title( '<h1 class="entry-title">', '</h1>' );
			?>
		</header><!-- .entry-header -->
		<?php
	}
}

if ( ! function_exists( 'honeycomb_page_content' ) ) {
	/**
	 * Display the post content with a link to the single post
	 *
	 * @since 1.0.0
	 */
	function honeycomb_page_content() {
		?>
		<div class="entry-content">
			<?php the_content(); ?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . __( 'Pages:', 'honeycomb' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry-content -->
		<?php
	}
}

if ( ! function_exists( 'honeycomb_post_header' ) ) {
	/**
	 * Display the post header with a link to the single post
	 *
	 * @since 1.0.0
	 */
	function honeycomb_post_header() {
		?>
		<header class="entry-header">
		<?php
		if ( is_single() ) {
			honeycomb_posted_on();
			the_title( '<h1 class="entry-title">', '</h1>' );
		} else {
			if ( 'post' == get_post_type() ) {
				honeycomb_posted_on();
			}

			the_title( sprintf( '<h2 class="alpha entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
		}
		?>
		</header><!-- .entry-header -->
		<?php
	}
}

if ( ! function_exists( 'honeycomb_post_content' ) ) {
	/**
	 * Display the post content with a link to the single post
	 *
	 * @since 1.0.0
	 */
	function honeycomb_post_content() {
		?>
		<div class="entry-content">
		<?php

		/**
		 * Functions hooked in to honeycomb_post_content_before action.
		 *
		 * @hooked honeycomb_post_thumbnail - 10
		 */
		do_action( 'honeycomb_post_content_before' );

		the_content(
			sprintf(
				__( 'Continue reading %s', 'honeycomb' ),
				'<span class="screen-reader-text">' . get_the_title() . '</span>'
			)
		);

		do_action( 'honeycomb_post_content_after' );

		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'honeycomb' ),
			'after'  => '</div>',
		) );
		?>
		</div><!-- .entry-content -->
		<?php
	}
}

if ( ! function_exists( 'honeycomb_post_meta' ) ) {
	/**
	 * Display the post meta
	 *
	 * @since 1.0.0
	 */
	function honeycomb_post_meta() {
		?>
		<aside class="entry-meta">
			<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search.

			?>
			<div class="author">
				<?php
					echo get_avatar( get_the_author_meta( 'ID' ), 128 );
					echo '<div class="label">' . esc_attr( __( 'Written by', 'honeycomb' ) ) . '</div>';
					the_author_posts_link();
				?>
			</div>
			<?php
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( __( ', ', 'honeycomb' ) );

			if ( $categories_list ) : ?>
				<div class="cat-links">
					<?php
					echo '<div class="label">' . esc_attr( __( 'Posted in', 'honeycomb' ) ) . '</div>';
					echo wp_kses_post( $categories_list );
					?>
				</div>
			<?php endif; // End if categories. ?>

			<?php
			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', __( ', ', 'honeycomb' ) );

			if ( $tags_list ) : ?>
				<div class="tags-links">
					<?php
					echo '<div class="label">' . esc_attr( __( 'Tagged', 'honeycomb' ) ) . '</div>';
					echo wp_kses_post( $tags_list );
					?>
				</div>
			<?php endif; // End if $tags_list. ?>

		<?php endif; // End if 'post' == get_post_type(). ?>

			<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
				<div class="comments-link">
					<?php echo '<div class="label">' . esc_attr( __( 'Comments', 'honeycomb' ) ) . '</div>'; ?>
					<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'honeycomb' ), __( '1 Comment', 'honeycomb' ), __( '% Comments', 'honeycomb' ) ); ?></span>
				</div>
			<?php endif; ?>
		</aside>
		<?php
	}
}

if ( ! function_exists( 'honeycomb_paging_nav' ) ) {
	/**
	 * Display navigation to next/previous set of posts when applicable.
	 */
	function honeycomb_paging_nav() {
		global $wp_query;

		$args = array(
			'type' 	    => 'list',
			'next_text' => _x( 'Next', 'Next post', 'honeycomb' ),
			'prev_text' => _x( 'Previous', 'Previous post', 'honeycomb' ),
			);

		the_posts_pagination( $args );
	}
}

if ( ! function_exists( 'honeycomb_post_nav' ) ) {
	/**
	 * Display navigation to next/previous post when applicable.
	 */
	function honeycomb_post_nav() {
		$args = array(
			'next_text' => '%title',
			'prev_text' => '%title',
			);
		the_post_navigation( $args );
	}
}

if ( ! function_exists( 'honeycomb_posted_on' ) ) {
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function honeycomb_posted_on() {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time> <time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() ),
			esc_attr( get_the_modified_date( 'c' ) ),
			esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			_x( 'Posted on %s', 'post date', 'honeycomb' ),
			'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		echo wp_kses( apply_filters( 'honeycomb_single_post_posted_on_html', '<span class="posted-on">' . $posted_on . '</span>', $posted_on ), array(
			'span' => array(
				'class'  => array(),
			),
			'a'    => array(
				'href'  => array(),
				'title' => array(),
				'rel'   => array(),
			),
			'time' => array(
				'datetime' => array(),
				'class'    => array(),
			),
		) );
	}
}

if ( ! function_exists( 'honeycomb_property_search_form' ) ) {
	/**
	 * Display the search form. Only used on homepage by default
	 * Hooked into the `homepage` action in the homepage template
	 *
	 * @since  1.0.0
	 * @return  void
	 */
	function honeycomb_property_search_form() {
		echo do_shortcode('[property_search_form id="default"]');
	}
}

if ( ! function_exists( 'honeycomb_homepage_content' ) ) {
	/**
	 * Display homepage content
	 * Hooked into the `homepage` action in the homepage template
	 *
	 * @since  1.0.0
	 * @return  void
	 */
	function honeycomb_homepage_content() {
		while ( have_posts() ) {
			the_post();

			get_template_part( 'content', 'page' );

		} // end of the loop.
	}
}

if ( ! function_exists( 'honeycomb_featured_properties' ) ) {
	/**
	 * Display Featured Properties
	 * Hooked into the `homepage` action in the homepage template
	 *
	 * @since  1.0.0
	 * @param array $args the property section args.
	 * @return void
	 */
	function honeycomb_featured_properties( $args ) {

		if ( honeycomb_is_propertyhive_activated() ) {

			$args = apply_filters( 'honeycomb_featured_properties_args', array(
				'limit'   => 4,
				'columns' => 4,
				'orderby' => 'rand',
				'order'   => 'desc',
				'title'   => __( 'Featured Properties', 'honeycomb' ),
			) );

			echo '<section class="honeycomb-property-section honeycomb-featured-properties" aria-label="Featured Properties">';

			do_action( 'honeycomb_homepage_before_featured_properties' );

			echo '<h2 class="section-title">' . wp_kses_post( $args['title'] ) . '</h2>';

			do_action( 'honeycomb_homepage_after_featured_properties_title' );

			echo honeycomb_do_shortcode( 'featured_properties', array(
				'per_page' => intval( $args['limit'] ),
				'columns'  => intval( $args['columns'] ),
				'orderby'  => esc_attr( $args['orderby'] ),
				'order'    => esc_attr( $args['order'] ),
			) );

			do_action( 'honeycomb_homepage_after_featured_properties' );

			echo '</section>';
		}
	}
}

if ( ! function_exists( 'honeycomb_social_icons' ) ) {
	/**
	 * Display social icons
	 * If the subscribe and connect plugin is active, display the icons.
	 *
	 * @link http://wordpress.org/plugins/subscribe-and-connect/
	 * @since 1.0.0
	 */
	function honeycomb_social_icons() {
		if ( class_exists( 'Subscribe_And_Connect' ) ) {
			echo '<div class="subscribe-and-connect-connect">';
			subscribe_and_connect_connect();
			echo '</div>';
		}
	}
}

if ( ! function_exists( 'honeycomb_get_sidebar' ) ) {
	/**
	 * Display honeycomb sidebar
	 *
	 * @uses get_sidebar()
	 * @since 1.0.0
	 */
	function honeycomb_get_sidebar() {
		get_sidebar();
	}
}

if ( ! function_exists( 'honeycomb_post_thumbnail' ) ) {
	/**
	 * Display post thumbnail
	 *
	 * @var $size thumbnail size. thumbnail|medium|large|full|$custom
	 * @uses has_post_thumbnail()
	 * @uses the_post_thumbnail
	 * @param string $size the post thumbnail size.
	 * @since 1.5.0
	 */
	function honeycomb_post_thumbnail( $size = 'full' ) {
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $size );
		}
	}
}

if ( ! function_exists( 'honeycomb_primary_navigation_wrapper' ) ) {
	/**
	 * The primary navigation wrapper
	 */
	function honeycomb_primary_navigation_wrapper() {
		echo '<div class="honeycomb-primary-navigation">';
	}
}

if ( ! function_exists( 'honeycomb_primary_navigation_wrapper_close' ) ) {
	/**
	 * The primary navigation wrapper close
	 */
	function honeycomb_primary_navigation_wrapper_close() {
		echo '</div>';
	}
}

if ( ! function_exists( 'honeycomb_init_structured_data' ) ) {
	/**
	 * Generates structured data.
	 *
	 * Hooked into the following action hooks:
	 *
	 * - `honeycomb_loop_post`
	 * - `honeycomb_single_post`
	 * - `honeycomb_page`
	 *
	 * Applies `honeycomb_structured_data` filter hook for structured data customization :)
	 */
	function honeycomb_init_structured_data() {

		// Post's structured data.
		if ( is_home() || is_category() || is_date() || is_search() || is_single() && ( honeycomb_is_propertyhive_activated() && ! is_propertyhive() ) ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'normal' );
			$logo  = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );

			$json['@type']            = 'BlogPosting';

			$json['mainEntityOfPage'] = array(
				'@type'                 => 'webpage',
				'@id'                   => get_the_permalink(),
			);

			$json['publisher']        = array(
				'@type'                 => 'organization',
				'name'                  => get_bloginfo( 'name' ),
				'logo'                  => array(
					'@type'               => 'ImageObject',
					'url'                 => $logo[0],
					'width'               => $logo[1],
					'height'              => $logo[2],
				),
			);

			$json['author']           = array(
				'@type'                 => 'person',
				'name'                  => get_the_author(),
			);

			if ( $image ) {
				$json['image']            = array(
					'@type'                 => 'ImageObject',
					'url'                   => $image[0],
					'width'                 => $image[1],
					'height'                => $image[2],
				);
			}

			$json['datePublished']    = get_post_time( 'c' );
			$json['dateModified']     = get_the_modified_date( 'c' );
			$json['name']             = get_the_title();
			$json['headline']         = $json['name'];
			$json['description']      = get_the_excerpt();

		// Page's structured data.
		} elseif ( is_page() ) {
			$json['@type']            = 'WebPage';
			$json['url']              = get_the_permalink();
			$json['name']             = get_the_title();
			$json['description']      = get_the_excerpt();
		}

		if ( isset( $json ) ) {
			Honeycomb::set_structured_data( apply_filters( 'honeycomb_structured_data', $json ) );
		}
	}
}
