<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template set preview and demo callbacks.
 */
trait PH_Template_Set_Preview {

	/**
	 * Absolute (protocol-relative) URL for a bundled demo image.
	 *
	 * @param string $file File name within assets/images/template-demo.
	 * @return string
	 */
	private static function demo_asset( $file ) {
		return str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/images/template-demo/' . $file;
	}

	/**
	 * Credible demo agency / negotiator identity used in preview mode.
	 *
	 * @return array
	 */
	private static function get_demo_agency() {
		return array(
			'office'  => 'Ashford & Rowe Prime',
			'branch'  => 'Marylebone',
			'agent'   => 'James Ashford',
			'role'    => 'Associate Director',
			'phone'   => '020 7946 0958',
			'email'   => 'james.ashford@ashfordrowe.co.uk',
			'address' => '18 Cavendish Parade, Marylebone, London W1U 4QT',
			'portrait' => 'agent-james-ashford.png',
		);
	}

	/**
	 * Prepare the archive page as a clean homepage-module preview.
	 */
	public static function prepare_module_preview() {
		if ( ! self::is_module_preview() ) {
			return;
		}

		remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_search_form', 10 );
		remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_result_count', 20 );
		remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_catalog_ordering', 30 );
	}

	/**
	 * Swap the default property gallery for the template-set gallery and facts
	 * strip on the live frontend as well as in preview.
	 *
	 * This is what makes a selected template render consistently across themes:
	 * the theme-agnostic template gallery (and the facts strip) replace the
	 * default PropertyHive flexslider whenever the template set is active for the
	 * property detail page. The richer demo-content swaps (price, features,
	 * summary, etc.) remain preview-only in prepare_detail_preview().
	 */
	public static function prepare_detail_layout() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		remove_action( 'propertyhive_before_single_property_summary', 'propertyhive_show_property_images', 10 );
		add_action( 'propertyhive_before_single_property_summary', array( __CLASS__, 'render_detail_gallery' ), 10 );
		add_action( 'propertyhive_before_single_property_summary', array( __CLASS__, 'render_detail_facts_strip' ), 11 );

		// Standard sales detail surfaces type/beds/baths/etc. in the facts strip
		// below the gallery, so the default sidebar meta list is redundant. Drop
		// it on the live frontend to match the editor preview.
		if ( 'standard-sales-detail' === self::get_detail_template() ) {
			remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_meta', 20 );
		}
	}

	/**
	 * Render the detail preview with the current property record, while keeping
	 * the gallery sandbox available for layout exploration.
	 */
	public static function prepare_detail_preview() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_price', 10 );
		add_action( 'propertyhive_single_property_summary', array( __CLASS__, 'render_demo_price' ), 10 );

		remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_meta', 20 );
		add_action( 'propertyhive_single_property_summary', array( __CLASS__, 'render_demo_meta' ), 15 );

		remove_action( 'propertyhive_single_property_summary', 'propertyhive_template_single_sharing', 30 );
		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_actions', 10 );

		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_features', 20 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_demo_features' ), 20 );

		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_summary', 30 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_demo_summary' ), 30 );

		remove_action( 'propertyhive_after_single_property_summary', 'propertyhive_template_single_description', 40 );
		add_action( 'propertyhive_after_single_property_summary', array( __CLASS__, 'render_demo_description' ), 40 );

	}

	/**
	 * Deprecated no-op retained for compatibility with older preview hooks.
	 *
	 * @param string $title Original title.
	 * @param int    $id    Post ID.
	 * @return string
	 */
	public static function filter_demo_title( $title, $id = 0 ) {
		return $title;
	}

	/**
	 * Render a quiet agency masthead at the top of preview pages so the demo
	 * brand, not the generic site title, anchors the screenshot.
	 */
	public static function render_preview_masthead() {
		if ( ! self::is_demo_preview() ) {
			return;
		}

		if ( ! is_property() && ! is_post_type_archive( 'property' ) ) {
			return;
		}

		$brand = '';
		$phone = '';

		if ( is_property() ) {
			$property = self::get_current_property();

			if ( $property ) {
				$brand = self::get_display_office_name( $property );
				$phone = $property->get_negotiator_telephone_number();
			}
		}

		if ( ! $brand ) {
			$agency = self::get_demo_agency();
			$brand  = $agency['office'];
			$phone  = $agency['phone'];
		}

		echo '<div class="ph-template-masthead"><div class="ph-template-masthead-inner">';
			echo '<span class="ph-template-masthead-brand">' . esc_html( $brand ) . '</span>';
			if ( $phone ) {
				echo '<a class="ph-template-masthead-phone" href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>';
			}
		echo '</div></div>';
	}

	/**
	 * Build gallery image data from the current property's attached photos.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_property_gallery_images( $property ) {
		$images = array();

		if ( ! $property ) {
			return $images;
		}

		if ( get_option( 'propertyhive_images_stored_as', '' ) === 'urls' ) {
			$photos = $property->_photo_urls;

			if ( ! is_array( $photos ) ) {
				return $images;
			}

			foreach ( $photos as $index => $photo ) {
				if ( empty( $photo['url'] ) ) {
					continue;
				}

				$label = ! empty( $photo['title'] ) ? $photo['title'] : sprintf(
					/* translators: %d: photo number */
					__( 'Photo %d', 'propertyhive' ),
					(int) $index + 1
				);

				$images[] = array(
					'src'     => $photo['url'],
					'thumb'   => $photo['url'],
					'alt'     => $label,
					'caption' => $label,
				);
			}

			return $images;
		}

		foreach ( $property->get_gallery_attachment_ids() as $index => $attachment_id ) {
			$src   = wp_get_attachment_image_src( $attachment_id, apply_filters( 'propertyhive_single_property_image_size', 'large' ) );
			$thumb = wp_get_attachment_image_src( $attachment_id, apply_filters( 'single_property_small_thumbnail_size', 'medium' ) );

			if ( empty( $src[0] ) ) {
				continue;
			}

			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			if ( '' === trim( (string) $alt ) ) {
				$alt = get_the_title( $attachment_id );
			}
			if ( '' === trim( (string) $alt ) ) {
				$alt = sprintf(
					/* translators: %d: photo number */
					__( 'Photo %d', 'propertyhive' ),
					(int) $index + 1
				);
			}

			$caption = wp_get_attachment_caption( $attachment_id );
			if ( '' === trim( (string) $caption ) ) {
				$caption = $alt;
			}

			$images[] = array(
				'src'     => $src[0],
				'thumb'   => ! empty( $thumb[0] ) ? $thumb[0] : $src[0],
				'alt'     => $alt,
				'caption' => $caption,
			);
		}

		return $images;
	}

	/**
	 * Render search-card gallery data for inline image navigation.
	 */
	public static function render_card_gallery_data() {
		if ( ! self::should_render_card_extras() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		self::render_card_gallery_data_script( self::get_property_gallery_images( $property ) );
	}

	/**
	 * Render card gallery data as JSON for the front-end script.
	 *
	 * @param array $images Image data.
	 */
	private static function render_card_gallery_data_script( $images ) {
		if ( empty( $images ) || count( $images ) < 2 ) {
			return;
		}

		$payload = array();

		foreach ( array_slice( $images, 0, 12 ) as $image ) {
			if ( empty( $image['src'] ) ) {
				continue;
			}

			$payload[] = array(
				'src'     => esc_url_raw( $image['src'] ),
				'alt'     => isset( $image['alt'] ) ? wp_strip_all_tags( $image['alt'] ) : '',
				'caption' => isset( $image['caption'] ) ? wp_strip_all_tags( $image['caption'] ) : '',
			);
		}

		if ( count( $payload ) < 2 ) {
			return;
		}

		echo '<script type="application/json" data-ph-card-gallery-data>' . wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>';
	}

	/**
	 * Render a hero/gallery presentation using the property's attached photos.
	 *
	 * Used on both the live frontend and in preview so the selected template's
	 * gallery renders identically across themes. The floor-map and virtual-tour
	 * panels are stylised previews, so they are limited to preview mode; the map
	 * panel (real location) and the photo rail render live too.
	 */
	public static function render_detail_gallery() {
		$property = self::get_current_property();

		$template = self::get_detail_template();
		$images   = self::get_property_gallery_images( $property );

		if ( empty( $images ) ) {
			return;
		}

		$is_preview       = self::is_demo_preview();
		$is_editorial     = ( 'premium-editorial-detail' === $template );
		$hero             = reset( $images );
		$rail             = array_slice( $images, 0, 5 );
		$count            = count( $images );
		$location         = self::get_property_location_label( $property );
		$has_floor_map    = $is_preview && self::should_render_floorplans( $property );
		$has_virtual_tour = $is_preview && self::should_render_virtual_tours( $property );
		$gallery_layout   = self::get_gallery_layout();

		echo '<div class="images ph-template-gallery ph-template-gallery-' . esc_attr( sanitize_html_class( $template ) ) . ' ph-gallery-variant-' . esc_attr( sanitize_html_class( $gallery_layout ) ) . '" data-ph-template-gallery data-ph-gallery-current-variant="' . esc_attr( $gallery_layout ) . '">';

			echo '<figure class="ph-template-gallery-hero">';
				echo '<button type="button" class="ph-template-gallery-photo-trigger" data-ph-gallery-open aria-label="' . esc_attr( sprintf(
					/* translators: %s: image label */
					__( 'Open larger photo: %s', 'propertyhive' ),
					$hero['caption']
				) ) . '">';
					echo '<img src="' . esc_url( $hero['src'] ) . '" alt="' . esc_attr( $hero['alt'] ) . '" loading="lazy" data-ph-gallery-hero-image>';
					echo '<span class="ph-template-gallery-expand-label" aria-hidden="true">' . esc_html__( 'View larger', 'propertyhive' ) . '</span>';
				echo '</button>';
				if ( $has_floor_map ) {
					echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-floorplan" hidden data-ph-gallery-panel="floorplan" aria-label="' . esc_attr__( 'Floor map preview', 'propertyhive' ) . '">';
						echo '<div class="ph-template-floorplan" aria-hidden="true">';
							echo '<span class="ph-template-floorplan-room ph-template-room-reception">' . esc_html__( 'Reception', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-kitchen">' . esc_html__( 'Kitchen', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-bed-one">' . esc_html__( 'Bed 1', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-bed-two">' . esc_html__( 'Bed 2', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-floorplan-room ph-template-room-bath">' . esc_html__( 'Bath', 'propertyhive' ) . '</span>';
						echo '</div>';
					echo '</div>';
				}
				if ( $has_virtual_tour ) {
					echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-virtual-tour" hidden data-ph-gallery-panel="virtual-tour" aria-label="' . esc_attr__( 'Virtual tour preview', 'propertyhive' ) . '">';
						echo '<div class="ph-template-virtual-tour-preview" aria-hidden="true">';
							echo '<span class="ph-template-virtual-tour-scene ph-template-virtual-tour-scene-living">' . esc_html__( 'Living room', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-virtual-tour-scene ph-template-virtual-tour-scene-kitchen">' . esc_html__( 'Kitchen', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-virtual-tour-scene ph-template-virtual-tour-scene-bedroom">' . esc_html__( 'Bedroom', 'propertyhive' ) . '</span>';
							echo '<span class="ph-template-virtual-tour-hotspot ph-template-virtual-tour-hotspot-one"></span>';
							echo '<span class="ph-template-virtual-tour-hotspot ph-template-virtual-tour-hotspot-two"></span>';
							echo '<span class="ph-template-virtual-tour-label">' . esc_html__( '360 virtual tour', 'propertyhive' ) . '</span>';
						echo '</div>';
					echo '</div>';
				}
				if ( $location ) {
					echo '<div class="ph-template-gallery-panel ph-template-gallery-panel-map" hidden data-ph-gallery-panel="map" aria-label="' . esc_attr__( 'Map preview', 'propertyhive' ) . '"><span class="ph-template-map-pin"></span><span class="ph-template-map-label">' . esc_html( $location ) . '</span></div>';
				}

				if ( ! $is_editorial ) {
					echo '<span class="ph-template-gallery-count"><span class="ph-template-gallery-count-icon" aria-hidden="true"></span>' . esc_html( sprintf(
						/* translators: %d: number of property photos */
						_n( '%d photo', '%d photos', $count, 'propertyhive' ),
						(int) $count
					) ) . '</span>';

						echo '<div class="ph-template-gallery-tabs" role="tablist" aria-label="' . esc_attr__( 'Gallery views', 'propertyhive' ) . '">';
							echo '<button type="button" class="is-active" data-ph-gallery-tab="photos" aria-selected="true">' . esc_html__( 'Photos', 'propertyhive' ) . '</button>';
							if ( $has_floor_map ) {
								echo '<button type="button" data-ph-gallery-tab="floorplan" aria-selected="false">' . esc_html__( 'Floor map', 'propertyhive' ) . '</button>';
							}
							if ( $has_virtual_tour ) {
								echo '<button type="button" data-ph-gallery-tab="virtual-tour" aria-selected="false">' . esc_html__( 'Virtual tour', 'propertyhive' ) . '</button>';
							}
							if ( $location ) {
								echo '<button type="button" data-ph-gallery-tab="map" aria-selected="false">' . esc_html__( 'Map', 'propertyhive' ) . '</button>';
							}
						echo '</div>';
					} else {
						echo '<figcaption data-ph-gallery-caption>' . esc_html( $hero['caption'] ) . '</figcaption>';
					}
				echo '</figure>';

				if ( ! empty( $rail ) ) {
					echo '<div class="ph-template-gallery-rail">';
					foreach ( $rail as $index => $image ) {
						$is_active = ( 0 === $index );
						echo '<button type="button" class="ph-template-gallery-thumb' . ( $is_active ? ' is-active' : '' ) . '" data-ph-gallery-thumb data-src="' . esc_url( $image['src'] ) . '" data-alt="' . esc_attr( $image['alt'] ) . '" data-caption="' . esc_attr( $image['caption'] ) . '" aria-label="' . esc_attr( sprintf(
							/* translators: %s: image label */
							__( 'Show %s', 'propertyhive' ),
							$image['caption']
						) ) . '"' . ( $is_active ? ' aria-current="true"' : '' ) . '>';
							echo '<img src="' . esc_url( $image['thumb'] ) . '" alt="' . esc_attr( $image['alt'] ) . '" loading="lazy">';
							if ( $is_editorial ) {
								echo '<span>' . esc_html( $image['caption'] ) . '</span>';
							}
						echo '</button>';
					}
					echo '</div>';
				}

		echo '</div>';
	}

	/**
	 * Render Rightmove-style detail facts below the gallery.
	 */
	public static function render_detail_facts_strip() {
		if ( ! self::is_enabled() || ! is_property() || 'standard-sales-detail' !== self::get_detail_template() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$facts = self::get_detail_facts_strip_items( $property );

		if ( empty( $facts ) ) {
			return;
		}

		echo '<section class="ph-template-detail-facts-strip ph-template-detail-facts-count-' . esc_attr( count( $facts ) ) . '" aria-label="' . esc_attr__( 'Property facts', 'propertyhive' ) . '">';
			echo '<ul>';
			foreach ( $facts as $fact ) {
				echo '<li class="ph-template-detail-fact ph-template-detail-fact-' . esc_attr( sanitize_html_class( $fact['icon'] ) ) . '">';
					echo '<span class="ph-template-detail-fact-label">' . esc_html( $fact['label'] ) . '</span>';
					echo '<span class="ph-template-detail-fact-content">';
						echo '<span class="ph-template-detail-fact-icon ph-template-detail-fact-icon-' . esc_attr( sanitize_html_class( $fact['icon'] ) ) . '" aria-hidden="true"></span>';
						echo '<span class="ph-template-detail-fact-values">';
							echo '<strong>' . esc_html( $fact['value'] ) . '</strong>';
							if ( ! empty( $fact['secondary'] ) ) {
								echo '<span>' . esc_html( $fact['secondary'] ) . '</span>';
							}
						echo '</span>';
					echo '</span>';
				echo '</li>';
			}
			echo '</ul>';
		echo '</section>';
	}

	/**
	 * Currency helper for ASCII source files.
	 *
	 * @param string $amount Amount without currency symbol.
	 * @return string
	 */
	private static function demo_price( $amount ) {
		return html_entity_decode( '&pound;' . $amount, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Curated listing content for preview pages.
	 *
	 * @param string $template Template slug.
	 * @return array
	 */
	private static function get_demo_listing( $template ) {
		$listing = array(
			'title'       => __( 'Cavendish House, Marylebone W1', 'propertyhive' ),
			'price'       => __( 'Guide price ', 'propertyhive' ) . self::demo_price( '1,250,000' ),
			'summary'     => __( 'A restored period home on a quiet Marylebone street, arranged for modern family living with generous entertaining space, calm bedrooms and a landscaped west-facing terrace.', 'propertyhive' ),
			'description' => array(
				__( 'Cavendish House sits behind cast-iron railings on a tree-lined address moments from the village shops, restaurants and transport links of Marylebone. The house has been finished with a measured hand, retaining original proportion while introducing modern lighting, fitted storage and warm natural materials throughout.', 'propertyhive' ),
				__( 'The raised ground floor opens into a double reception with tall sash windows and a working fireplace. To the rear, a full-width kitchen and dining room connects directly to the terrace, creating a natural space for day-to-day family life and informal entertaining.', 'propertyhive' ),
				__( 'The principal suite occupies the quieter upper floor and includes a dressing area and marble-lined shower room. Two further bedrooms, a family bathroom, guest cloakroom and practical utility storage complete the accommodation.', 'propertyhive' ),
			),
			'features'    => array(
				__( 'Three double bedrooms', 'propertyhive' ),
				__( 'Double reception room', 'propertyhive' ),
				__( 'Full-width kitchen and dining room', 'propertyhive' ),
				__( 'Landscaped west-facing terrace', 'propertyhive' ),
				__( 'Share of freehold', 'propertyhive' ),
				__( 'No onward chain', 'propertyhive' ),
			),
			'area'        => __( 'Marylebone', 'propertyhive' ),
			'floor_area'  => __( '1,480 sq ft / 137.5 sq m', 'propertyhive' ),
			'meta'        => array(
				__( '3 bedrooms', 'propertyhive' ),
				__( '2 bathrooms', 'propertyhive' ),
				__( '2 reception rooms', 'propertyhive' ),
				__( 'Freehold', 'propertyhive' ),
			),
			'epc_now'     => 72,
			'epc_next'    => 86,
			'connections' => array(
				array( __( 'Baker Street Underground', 'propertyhive' ), __( '0.3 miles', 'propertyhive' ) ),
				array( __( 'Marylebone station', 'propertyhive' ), __( '0.6 miles', 'propertyhive' ) ),
				array( __( 'Regent\'s Park', 'propertyhive' ), __( '0.5 miles', 'propertyhive' ) ),
				array( __( 'Marylebone High Street', 'propertyhive' ), __( '0.4 miles', 'propertyhive' ) ),
			),
		);

		if ( 'conversion-first-sales-detail' === $template ) {
			$listing['price']   = __( 'Offers over ', 'propertyhive' ) . self::demo_price( '1,250,000' );
			$listing['summary'] = __( 'A chain-free Marylebone house with immediate viewing availability, polished presentation and the right blend of period detail, outside space and practical family accommodation.', 'propertyhive' );
		}

		if ( 'premium-editorial-detail' === $template ) {
			$listing['title']       = __( 'A Restored Marylebone House with a Private Terrace', 'propertyhive' );
			$listing['summary']     = __( 'A composed period home where original volume, restored detailing and a warm contemporary finish come together in one of Marylebone\'s most walkable pockets.', 'propertyhive' );
			$listing['description'] = array(
				__( 'The approach is deliberately understated: railings, sash windows and a handsome brick facade give little away from the street. Inside, the plan opens into a sequence of calm, well-proportioned rooms designed around light, storage and a direct relationship with the terrace.', 'propertyhive' ),
				__( 'Materials have been chosen for longevity rather than effect. Stone, timber, bronze ironmongery and soft neutral wall finishes create a quietly refined setting for furniture, art and family life.', 'propertyhive' ),
				__( 'The result is a house that feels established rather than staged, with the comfort of a complete renovation and the character expected from a central London period address.', 'propertyhive' ),
			);
		}

		if ( 'lettings-detail' === $template ) {
			$listing = array(
				'title'       => __( 'Atlas Apartment, Riverside Quarter SW18', 'propertyhive' ),
				'price'       => self::demo_price( '2,450' ) . __( ' pcm', 'propertyhive' ),
				'summary'     => __( 'A furnished riverside apartment with concierge, gym access and a private balcony, positioned for quick links into the City and west London.', 'propertyhive' ),
				'description' => array(
					__( 'This fourth-floor apartment has been prepared for a professional tenant or couple seeking a well-managed building with strong amenities and easy access to the river path.', 'propertyhive' ),
					__( 'The open-plan reception and kitchen is furnished with considered pieces, integrated appliances and direct balcony access. Both bedrooms are proper doubles, with the principal bedroom benefitting from fitted wardrobes and an en suite shower room.', 'propertyhive' ),
					__( 'Residents have access to a twenty-four hour concierge, gym, secure cycle storage and landscaped communal terrace. The apartment is available furnished on a long let.', 'propertyhive' ),
				),
				'features'    => array(
					__( 'Two double bedrooms', 'propertyhive' ),
					__( 'Private balcony', 'propertyhive' ),
					__( 'Furnished', 'propertyhive' ),
					__( 'Concierge and gym', 'propertyhive' ),
					__( 'Secure cycle storage', 'propertyhive' ),
					__( 'Available on a long let', 'propertyhive' ),
				),
				'area'        => __( 'Riverside Quarter', 'propertyhive' ),
				'floor_area'  => __( '842 sq ft / 78.2 sq m', 'propertyhive' ),
				'meta'        => array(
					__( '2 bedrooms', 'propertyhive' ),
					__( '2 bathrooms', 'propertyhive' ),
					__( 'Furnished', 'propertyhive' ),
					__( 'Long let', 'propertyhive' ),
				),
				'epc_now'     => 84,
				'epc_next'    => 91,
				'connections' => array(
					array( __( 'Riverside station', 'propertyhive' ), __( '0.3 miles', 'propertyhive' ) ),
					array( __( 'City terminus', 'propertyhive' ), __( '24 min', 'propertyhive' ) ),
					array( __( 'River path', 'propertyhive' ), __( '2 min walk', 'propertyhive' ) ),
					array( __( 'Wharf shops and cafes', 'propertyhive' ), __( '0.2 miles', 'propertyhive' ) ),
				),
			);
		}

		if ( 'new-homes-development-detail' === $template ) {
			$listing = array(
				'title'       => __( 'Elm Yard, Wokingham RG40', 'propertyhive' ),
				'price'       => __( 'Prices from ', 'propertyhive' ) . self::demo_price( '485,000' ),
				'summary'     => __( 'A boutique collection of energy-efficient townhouses and apartments around a landscaped residents\' courtyard, with a furnished show home now open by appointment.', 'propertyhive' ),
				'description' => array(
					__( 'Elm Yard brings a small-scale, design-led new homes scheme to a well-connected Wokingham address, balancing brick architecture, generous glazing and planted communal spaces.', 'propertyhive' ),
					__( 'Each home includes open-plan living space, high-performance glazing, underfloor heating to principal rooms and a carefully selected kitchen and bathroom specification.', 'propertyhive' ),
					__( 'The first phase is available to reserve, with incentives on selected plots and completion dates staged across the coming season.', 'propertyhive' ),
				),
				'features'    => array(
					__( 'Boutique new homes development', 'propertyhive' ),
					__( 'One, two and three bedroom homes', 'propertyhive' ),
					__( 'Landscaped residents\' courtyard', 'propertyhive' ),
					__( 'EV charging to selected plots', 'propertyhive' ),
					__( 'High-performance glazing', 'propertyhive' ),
					__( 'Show home open by appointment', 'propertyhive' ),
				),
				'area'        => __( 'Wokingham', 'propertyhive' ),
				'floor_area'  => __( '642 to 1,280 sq ft', 'propertyhive' ),
				'meta'        => array(
					__( '1-3 bedrooms', 'propertyhive' ),
					__( 'New build', 'propertyhive' ),
					__( 'EPC A-rated', 'propertyhive' ),
					__( 'Reservation open', 'propertyhive' ),
				),
				'epc_now'     => 91,
				'epc_next'    => 96,
				'connections' => array(
					array( __( 'Wokingham station', 'propertyhive' ), __( '0.5 miles', 'propertyhive' ) ),
					array( __( 'Town centre', 'propertyhive' ), __( '0.4 miles', 'propertyhive' ) ),
					array( __( 'Primary school', 'propertyhive' ), __( '0.3 miles', 'propertyhive' ) ),
					array( __( 'Reading connection', 'propertyhive' ), __( '16 min', 'propertyhive' ) ),
				),
			);
		}

		return $listing;
	}

	/**
	 * Render preview price from the current property record.
	 */
	public static function render_demo_price() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$price = $property->get_formatted_price();

		if ( '' === $price ) {
			return;
		}

		$price_qualifier = method_exists( $property, 'get_price_qualifier' ) ? $property->get_price_qualifier() : $property->price_qualifier;
		$price_qualifier = self::format_price_qualifier_label( $price_qualifier );

		echo '<div class="price ph-template-demo-price">';
			if ( $price_qualifier ) {
				echo "\n" . '<span class="price-qualifier ph-template-price-qualifier">' . esc_html( $price_qualifier ) . '</span>';
			}
			echo "\n" . '<span class="ph-template-price-value">' . wp_kses_post( $price ) . '</span>';
		echo "\n" . '</div>';
	}

	/**
	 * Format a backend price qualifier for front-end display.
	 *
	 * @param string $price_qualifier Price qualifier.
	 * @return string
	 */
	private static function format_price_qualifier_label( $price_qualifier ) {
		$price_qualifier = trim( wp_strip_all_tags( (string) $price_qualifier ) );

		if ( '' === $price_qualifier ) {
			return '';
		}

		if ( false === strpos( $price_qualifier, ' ' ) && strtoupper( $price_qualifier ) === $price_qualifier ) {
			return $price_qualifier;
		}

		return ucfirst( strtolower( $price_qualifier ) );
	}

	/**
	 * Render preview meta from the current property record.
	 */
	public static function render_demo_meta() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		if ( 'standard-sales-detail' === self::get_detail_template() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$meta = self::get_detail_meta_items( $property );

		if ( empty( $meta ) ) {
			return;
		}

		echo '<div class="property_meta ph-template-demo-meta"><ul>';
		foreach ( $meta as $item ) {
			echo '<li>' . esc_html( $item ) . '</li>';
		}
		echo '</ul></div>';
	}

	/**
	 * Render current property features in preview mode.
	 */
	public static function render_demo_features() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$features = $property->get_features();

		if ( empty( $features ) ) {
			return;
		}

		echo '<div class="features ph-template-demo-features">';
			echo '<h4>' . esc_html__( 'Key features', 'propertyhive' ) . '</h4>';
			echo '<ul>';
			foreach ( $features as $feature ) {
				echo '<li>' . esc_html( $feature ) . '</li>';
			}
			echo '</ul>';
		echo '</div>';
	}

	/**
	 * Render current property summary in preview mode.
	 */
	public static function render_demo_summary() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$summary = get_post_field( 'post_excerpt', get_queried_object_id() );

		if ( '' === trim( wp_strip_all_tags( $summary ) ) ) {
			return;
		}

		echo '<div class="summary ph-template-demo-summary">';
			echo '<h4>' . esc_html__( 'Overview', 'propertyhive' ) . '</h4>';
			echo '<div class="summary-contents">' . wp_kses_post( wpautop( $summary ) ) . '</div>';
		echo '</div>';
	}

	/**
	 * Render current property description in preview mode.
	 */
	public static function render_demo_description() {
		if ( ! self::is_demo_preview() || ! is_property() ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$description = $property->get_formatted_description( false );

		if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
			return;
		}

		echo '<div class="description ph-template-demo-description">';
			echo '<h4>' . esc_html__( 'Full details', 'propertyhive' ) . '</h4>';
			echo '<div class="description-contents">' . wp_kses_post( $description ) . '</div>';
		echo '</div>';
	}

	/**
	 * Curated cards for archive/module preview pages.
	 *
	 * @return array
	 */
	private static function get_demo_property_cards() {
		return array(
			array(
				'image'   => 'cavendish-living-room.png',
				'badge'   => __( 'For sale', 'propertyhive' ),
				'title'   => __( 'Cavendish House, Marylebone W1', 'propertyhive' ),
				'price'   => __( 'Guide price ', 'propertyhive' ) . self::demo_price( '1,250,000' ),
				'summary' => __( 'A restored period home with generous entertaining space, calm bedrooms and a landscaped west-facing terrace.', 'propertyhive' ),
				'facts'   => array( __( '3 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( '1,480 sq ft', 'propertyhive' ), __( 'Freehold', 'propertyhive' ) ),
			),
			array(
				'image'   => 'cavendish-kitchen-dining.png',
				'badge'   => __( 'New instruction', 'propertyhive' ),
				'title'   => __( 'Upper Maisonette, Devonshire Street', 'propertyhive' ),
				'price'   => self::demo_price( '925,000' ),
				'summary' => __( 'A bright two bedroom maisonette with a refined kitchen, private entrance and a quiet garden outlook.', 'propertyhive' ),
				'facts'   => array( __( '2 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Share of freehold', 'propertyhive' ) ),
			),
			array(
				'image'   => 'cavendish-exterior.png',
				'badge'   => __( 'Viewing slots', 'propertyhive' ),
				'title'   => __( 'Period House, Cavendish Road', 'propertyhive' ),
				'price'   => __( 'Offers over ', 'propertyhive' ) . self::demo_price( '1,175,000' ),
				'summary' => __( 'A handsome family house with balanced reception space, off-street parking and a sheltered rear garden.', 'propertyhive' ),
				'facts'   => array( __( '4 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Garden', 'propertyhive' ) ),
			),
			array(
				'image'   => 'atlas-apartment-living.png',
				'badge'   => __( 'To let', 'propertyhive' ),
				'title'   => __( 'Atlas Apartment, Riverside Quarter', 'propertyhive' ),
				'price'   => self::demo_price( '2,450' ) . __( ' pcm', 'propertyhive' ),
				'summary' => __( 'A furnished riverside apartment with concierge, gym access, balcony and fast links into central London.', 'propertyhive' ),
				'facts'   => array( __( '2 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Furnished', 'propertyhive' ) ),
			),
			array(
				'image'   => 'elm-yard-development.png',
				'badge'   => __( 'New homes', 'propertyhive' ),
				'title'   => __( 'Elm Yard, Wokingham RG40', 'propertyhive' ),
				'price'   => __( 'From ', 'propertyhive' ) . self::demo_price( '485,000' ),
				'summary' => __( 'A boutique courtyard development with efficient homes, considered materials and a furnished show home.', 'propertyhive' ),
				'facts'   => array( __( '1-3 beds', 'propertyhive' ), __( 'EPC A-rated', 'propertyhive' ), __( 'Show home open', 'propertyhive' ) ),
			),
			array(
				'image'   => 'cavendish-garden-terrace.png',
				'badge'   => __( 'Private outside space', 'propertyhive' ),
				'title'   => __( 'Garden Flat, Weymouth Street', 'propertyhive' ),
				'price'   => self::demo_price( '1,050,000' ),
				'summary' => __( 'A lateral apartment with a planted terrace, open-plan living space and a calm position close to the park.', 'propertyhive' ),
				'facts'   => array( __( '2 beds', 'propertyhive' ), __( '2 baths', 'propertyhive' ), __( 'Terrace', 'propertyhive' ) ),
			),
		);
	}

	/**
	 * Render one curated preview card.
	 *
	 * @param array $card Card data.
	 */
	private static function render_demo_property_card( $card ) {
		$agency = self::get_demo_agency();

		echo '<li class="property ph-template-card ph-template-demo-card">';
			echo '<div class="thumbnail"><a href="javascript:;" aria-label="' . esc_attr( $card['title'] ) . '"><img src="' . esc_url( self::demo_asset( $card['image'] ) ) . '" alt="' . esc_attr( $card['title'] ) . '" loading="lazy">';
				self::render_card_gallery_data_script( self::get_demo_card_gallery_images( $card ) );
			echo '</a><span class="ph-template-badges"><span class="ph-template-badge">' . esc_html( $card['badge'] ) . '</span></span></div>';
			echo '<div class="details">';
				echo '<p class="status">' . esc_html( $card['badge'] ) . '</p>';
				echo '<h3><a href="javascript:;">' . esc_html( $card['title'] ) . '</a></h3>';
				echo '<p class="price">' . esc_html( $card['price'] ) . '</p>';
				echo '<p class="property_summary">' . esc_html( $card['summary'] ) . '</p>';
			echo '</div>';
			echo '<div class="ph-template-card-footer">';
				echo '<ul class="ph-template-facts">';
				foreach ( $card['facts'] as $fact ) {
					echo '<li>' . esc_html( $fact ) . '</li>';
				}
				echo '</ul>';
				echo '<div class="ph-template-card-branch"><span>' . esc_html( $agency['office'] ) . '</span><a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $agency['phone'] ) ) . '">' . esc_html( $agency['phone'] ) . '</a></div>';
			echo '</div>';
		echo '</li>';
	}

	/**
	 * Build image data for a demo search card.
	 *
	 * @param array $card Card data.
	 * @return array
	 */
	private static function get_demo_card_gallery_images( $card ) {
		$files = array_values(
			array_unique(
				array_filter(
					array_merge(
						array( isset( $card['image'] ) ? $card['image'] : '' ),
						array(
							'cavendish-living-room.png',
							'cavendish-kitchen-dining.png',
							'cavendish-principal-bedroom.png',
							'cavendish-garden-terrace.png',
							'cavendish-exterior.png',
							'atlas-apartment-living.png',
							'elm-yard-development.png',
						)
					)
				)
			)
		);

		$images = array();

		foreach ( array_slice( $files, 0, 5 ) as $index => $file ) {
			$label = sprintf(
				/* translators: 1: property title, 2: photo number */
				__( '%1$s photo %2$d', 'propertyhive' ),
				isset( $card['title'] ) ? $card['title'] : __( 'Property', 'propertyhive' ),
				(int) $index + 1
			);

			$images[] = array(
				'src'     => self::demo_asset( $file ),
				'alt'     => $label,
				'caption' => $label,
			);
		}

		return $images;
	}

	/**
	 * Render curated cards as a Property Hive result list.
	 *
	 * @param array  $cards Cards.
	 * @param string $class Extra class.
	 */
	private static function render_demo_property_cards( $cards, $class = '' ) {
		echo '<ul class="properties ph-template-demo-results ' . esc_attr( $class ) . '">';
		foreach ( $cards as $card ) {
			self::render_demo_property_card( $card );
		}
		echo '</ul>';
	}

	/**
	 * Render curated search cards in preview mode.
	 */
	public static function render_demo_search_results() {
		if ( ! self::is_demo_preview() || ! self::is_search_preview() ) {
			return;
		}

		// Search template previews use the real archive loop so filters, counts,
		// pagination, and property-type options stay aligned to backend data.
		return;
	}
}
