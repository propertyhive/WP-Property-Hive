<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Property detail template set callbacks.
 */
trait PH_Template_Set_Detail {

	/**
	 * Render supporting property modules in preview mode.
	 */
	public static function render_detail_modules() {
		if ( ! is_property() || ( ! self::is_demo_preview() && ( ! self::is_enabled() || ! self::detail_template_uses_rich_modules( self::get_detail_template() ) ) ) ) {
			return;
		}

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$template       = self::get_detail_template();
		// Portal Split shows key facts in the shared facts strip, not again in modules.
		$facts          = ( self::detail_template_uses_rich_modules( $template ) && 'conversion-first-sales-detail' !== $template )
			? self::get_detail_key_facts( $property )
			: array();
		$rooms          = self::get_detail_room_items( $property );
		$material       = self::get_detail_material_information( $property );
		$features       = $property->get_features();
		$gallery_images = self::get_property_gallery_images( $property );
		$duet_images    = ( 'premium-editorial-detail' === $template ) ? array_slice( $gallery_images, 1, 2 ) : array();
		$description    = empty( $rooms ) ? $property->get_formatted_description() : '';

		// The rich module partials provide their own context-specific heading.
		// PropertyHive's formatted fallback starts with its own heading, so remove
		// that one leading heading to avoid rendering two titles for one section.
		if ( $description ) {
			$description = preg_replace( '/^\s*<h[1-6]\b[^>]*>.*?<\/h[1-6]>\s*/is', '', $description );
			$description = preg_replace_callback(
				'/^\s*(<p\b[^>]*\bclass=(["\'])[^"\']*\broom\b[^"\']*\2[^>]*>)\s*<strong\b[^>]*\bclass=(["\'])[^"\']*\bname\b[^"\']*\3[^>]*>(.*?)<\/strong>\s*(?:&nbsp;)?\s*<br\s*\/?>\s*/is',
				function ( $matches ) {
					$name = strtolower( trim( wp_strip_all_tags( $matches[4] ) ) );

					return in_array( $name, self::get_detail_generic_room_labels(), true ) ? $matches[1] : $matches[0];
				},
				$description
			);
		}
		$overview       = has_excerpt( $property->id ) ? get_the_excerpt( $property->id ) : '';
		$location_label = self::get_property_location_label( $property );
		$address        = $property->get_formatted_full_address();
		$documents      = self::get_property_document_labels( $property );
		$office         = self::get_display_office_name( $property );
		$has_floorplan  = self::should_render_floorplans( $property );
		$phone          = $property->get_negotiator_telephone_number();
		$email          = $property->get_negotiator_email_address();
		$agent          = $property->get_negotiator_name();
		$portrait       = $property->get_negotiator_photo();
		$agent_role     = self::get_contact_agent_role( $agent, $office ? $office : __( 'Agent', 'propertyhive' ), $office );
		$button         = self::get_primary_cta_label( $property, $template );

		if ( empty( $facts ) && empty( $rooms ) && ! $description && empty( $features ) && ! $overview && empty( $material ) && ! $location_label && ! $address && empty( $documents ) && ! $has_floorplan && ! $agent && ! $office ) {
			return;
		}

		global $post;

		PH_Template_Set_Template_Loader::render(
			'detail',
			$template,
			'modules',
			array(
				'property'       => $property,
				'template'       => $template,
				'facts'          => $facts,
				'rooms'          => $rooms,
				'material'       => $material,
				'features'       => $features,
				'description'    => $description,
				'overview'       => $overview,
				'location_label' => $location_label,
				'address'        => $address,
				'documents'      => $documents,
				'office'         => $office,
				'has_floorplan'  => $has_floorplan,
				'duet_images'    => $duet_images,
				'post_id'        => isset( $post->ID ) ? absint( $post->ID ) : 0,
				'button'         => $button,
				'phone'          => $phone,
				'email'          => $email,
				'agent'          => $agent,
				'agent_role'     => $agent_role,
				'agent_initials' => self::get_contact_agent_initials( $agent ),
				'portrait'       => $portrait,
			)
		);
	}

	/**
	 * Render a similar-properties strip in preview mode.
	 */
	public static function render_similar_properties() {
		if ( ! is_property() || ( ! self::is_demo_preview() && ( ! self::is_enabled() || ! self::detail_template_uses_rich_modules( self::get_detail_template() ) ) ) ) {
			return;
		}

		if ( 'yes' !== PH_Template_Set_Request_Context::get_show_recommended() && ! self::is_template_editor_active() ) {
			return;
		}

		$current_property = self::get_current_property();

		if ( ! $current_property ) {
			return;
		}

		$display_count = self::get_recommended_property_count();
		$query_count   = self::is_template_editor_active() ? self::get_recommended_property_max_count() : $display_count;
		$image_size    = self::get_recommended_property_wp_image_size();
		$layout        = self::get_recommended_property_layout();
		$image_style   = self::get_recommended_property_image_size();
		$properties    = self::get_nearby_similar_property_ids( $current_property, $query_count );

		if ( empty( $properties ) ) {
			return;
		}

		$cards = array();

		foreach ( $properties as $index => $property_id ) {
			$related = new PH_Property( $property_id );
			$title   = get_the_title( $property_id );
			$image   = $related->get_main_photo_src( $image_size );

			if ( ! $image && ph_placeholder_img_src() ) {
				$image = ph_placeholder_img_src();
			}

			$cards[] = array(
				'index'  => $index,
				'hidden' => $index >= $display_count,
				'image'  => $image,
				'price'  => $related->get_formatted_price(),
				'facts'  => self::get_fact_summary( $related ),
				'title'  => $title,
				'url'    => get_permalink( $property_id ),
			);
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			self::get_detail_template(),
			'similar-properties',
			array(
				'property'      => $current_property,
				'template'      => self::get_detail_template(),
				'cards'         => $cards,
				'layout'        => $layout,
				'image_style'   => $image_style,
				'display_count' => $display_count,
			)
		);
	}

	/**
	 * Get nearby/similar properties for the template-set detail preview.
	 *
	 * @param PH_Property $property Current property object.
	 * @return array Property IDs.
	 */
	private static function get_nearby_similar_property_ids( $property, $limit = null ) {
		$property_id = absint( isset( $property->id ) ? $property->id : get_queried_object_id() );

		if ( ! $property_id ) {
			return array();
		}

		$limit = null === $limit ? self::get_recommended_property_count() : absint( $limit );
		$limit = absint( apply_filters( 'propertyhive_template_set_similar_properties_limit', $limit, $property ) );

		if ( ! $limit ) {
			return array();
		}

		$selected = array();
		$tiers    = array(
			'location'      => self::get_location_term_ids( $property_id ),
			'address_three' => sanitize_text_field( get_post_meta( $property_id, '_address_three', true ) ),
			'address_four'  => sanitize_text_field( get_post_meta( $property_id, '_address_four', true ) ),
			'postcode'      => self::get_outward_postcode( get_post_meta( $property_id, '_address_postcode', true ) ),
			'fallback'      => true,
		);

		foreach ( $tiers as $tier => $tier_value ) {
			if ( empty( $tier_value ) ) {
				continue;
			}

			$remaining = $limit - count( $selected );

			if ( $remaining <= 0 ) {
				break;
			}

			$matches = self::query_nearby_similar_properties( $property, $tier, $tier_value, $remaining, $selected );

			foreach ( $matches as $match_id ) {
				$match_id = absint( $match_id );

				if ( $match_id && $match_id !== $property_id && ! in_array( $match_id, $selected, true ) ) {
					$selected[] = $match_id;
				}
			}
		}

		$selected = array_slice( $selected, 0, $limit );
		$selected = apply_filters( 'propertyhive_template_set_similar_properties_ids', $selected, $property, $limit );
		$selected = array_values( array_unique( array_filter( array_map( 'absint', (array) $selected ) ) ) );
		$selected = array_values( array_diff( $selected, array( $property_id ) ) );

		return array_slice( $selected, 0, $limit );
	}

	/**
	 * Get the selected recommended-property count.
	 *
	 * @return int
	 */
	private static function get_recommended_property_count() {
		return PH_Template_Set_Request_Context::get_recommended_count();
	}

	/**
	 * Get the largest recommended-property count available in the editor.
	 *
	 * @return int
	 */
	private static function get_recommended_property_max_count() {
		return max( array_map( 'absint', array_keys( self::get_recommended_property_counts() ) ) );
	}

	/**
	 * Get the selected recommended-property layout.
	 *
	 * @return string
	 */
	private static function get_recommended_property_layout() {
		return PH_Template_Set_Request_Context::get_recommended_layout();
	}

	/**
	 * Get the selected recommended-property image treatment.
	 *
	 * @return string
	 */
	private static function get_recommended_property_image_size() {
		return PH_Template_Set_Request_Context::get_recommended_image_size();
	}

	/**
	 * Map the recommended-property image treatment to a WordPress image size.
	 *
	 * @return string
	 */
	private static function get_recommended_property_wp_image_size() {
		$image_size = self::get_recommended_property_image_size();

		if ( 'compact' === $image_size ) {
			return 'medium';
		}

		if ( 'large' === $image_size ) {
			return 'large';
		}

		return 'medium_large';
	}

	/**
	 * Query one matching tier for nearby/similar properties.
	 *
	 * @param PH_Property       $property Current property object.
	 * @param string            $tier Matching tier name.
	 * @param string|array|bool $tier_value Value for the matching tier.
	 * @param int               $limit Number of properties to fetch.
	 * @param array             $selected Already selected property IDs.
	 * @return array Property IDs.
	 */
	private static function query_nearby_similar_properties( $property, $tier, $tier_value, $limit, $selected ) {
		$property_id = absint( isset( $property->id ) ? $property->id : get_queried_object_id() );
		$department  = get_post_meta( $property_id, '_department', true );
		$meta_query  = array(
			array(
				'key'   => '_on_market',
				'value' => 'yes',
			),
		);

		if ( '' !== $department ) {
			$meta_query[] = array(
				'key'   => '_department',
				'value' => $department,
			);
		}

		$args = array(
			'post_type'           => 'property',
			'post_status'         => self::get_similar_property_post_statuses(),
			'posts_per_page'      => $limit,
			'post__not_in'        => array_values( array_unique( array_merge( array( $property_id ), array_map( 'absint', $selected ) ) ) ),
			'fields'              => 'ids',
			'ignore_sticky_posts' => 1,
			'has_password'        => false,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'meta_query'          => $meta_query,
		);

		if ( 'location' === $tier ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'location',
					'terms'    => array_map( 'absint', (array) $tier_value ),
					'operator' => 'IN',
				),
			);
		} elseif ( 'address_three' === $tier ) {
			$args['meta_query'][] = array(
				'key'   => '_address_three',
				'value' => $tier_value,
			);
		} elseif ( 'address_four' === $tier ) {
			$args['meta_query'][] = array(
				'key'   => '_address_four',
				'value' => $tier_value,
			);
		} elseif ( 'postcode' === $tier ) {
			$args['meta_query'][] = array(
				'key'     => '_address_postcode',
				'value'   => $tier_value,
				'compare' => 'LIKE',
			);
		}

		$args = apply_filters( 'propertyhive_template_set_similar_properties_query_args', $args, $property, $tier, $selected );

		if ( ! is_array( $args ) || empty( $args ) ) {
			return array();
		}

		$posts = get_posts( $args );
		$ids   = array();

		foreach ( (array) $posts as $post ) {
			$ids[] = is_object( $post ) && isset( $post->ID ) ? $post->ID : $post;
		}

		return array_values( array_filter( array_map( 'absint', $ids ) ) );
	}

	/**
	 * Get post statuses visible to the current visitor.
	 *
	 * @return string|array
	 */
	private static function get_similar_property_post_statuses() {
		return ( is_user_logged_in() && current_user_can( 'manage_propertyhive' ) ) ? array( 'publish', 'private' ) : 'publish';
	}

	/**
	 * Get assigned location term IDs for a property.
	 *
	 * @param int $property_id Property ID.
	 * @return array
	 */
	private static function get_location_term_ids( $property_id ) {
		if ( ! taxonomy_exists( 'location' ) ) {
			return array();
		}

		$terms = wp_get_post_terms( $property_id, 'location', array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'absint', $terms ) ) );
	}

	/**
	 * Get a useful outward postcode prefix for nearby matching.
	 *
	 * @param string $postcode Full postcode.
	 * @return string
	 */
	private static function get_outward_postcode( $postcode ) {
		$postcode = strtoupper( trim( (string) $postcode ) );

		if ( '' === $postcode ) {
			return '';
		}

		if ( false !== strpos( $postcode, ' ' ) ) {
			$parts = preg_split( '/\s+/', $postcode );
			return sanitize_text_field( $parts[0] );
		}

		if ( strlen( $postcode ) > 3 ) {
			return sanitize_text_field( substr( $postcode, 0, -3 ) );
		}

		return sanitize_text_field( $postcode );
	}

	/**
	 * Render single-property contact panel.
	 */
	public static function render_detail_contact_panel() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $post;

		$property = self::get_current_property();

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$button   = self::get_primary_cta_label( $property, $template );
		$kicker   = self::get_detail_kicker_label( $property, $template );
		$hint     = self::get_contact_hint( $template );
		$is_demo  = self::is_demo_preview();
		$phone    = $property->get_negotiator_telephone_number();
		$email    = $property->get_negotiator_email_address();
		$office   = $property->get_office_name();
		$address  = $property->get_office_address();
		$agent    = $property->get_negotiator_name();
		$portrait = $property->get_negotiator_photo();
		$price_qualifier = method_exists( $property, 'get_price_qualifier' ) ? $property->get_price_qualifier() : $property->price_qualifier;
		$price_qualifier = self::format_price_qualifier_label( $price_qualifier );

		$office_alt = $office ? $office : __( 'Agent', 'propertyhive' );
		$agent_role = self::get_contact_agent_role( $agent, $office_alt, $office );

		$shortlist_class  = 'ph-template-button ph-template-button-secondary ph-template-shortlist-button';
		$shortlist_labels = array();
		$share_button     = '';

		if ( 'immersive-cinema-detail' === $template ) {
			$shortlist_labels = array(
				'add'    => '♡ ' . __( 'Save', 'propertyhive' ),
				'remove' => __( 'Saved', 'propertyhive' ),
			);
			$share_button = self::get_share_button_markup( 'ph-template-button ph-template-button-secondary ph-template-share-button' );
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			$template,
			'contact-panel',
			array(
				'property'         => $property,
				'post_id'          => isset( $post->ID ) ? absint( $post->ID ) : 0,
				'template'         => $template,
				'button'           => $button,
				'kicker'           => $kicker,
				'hint'             => $hint,
				'is_demo'          => $is_demo,
				'phone'            => $phone,
				'email'            => $email,
				'office'           => $office,
				'office_alt'       => $office_alt,
				'address'          => $address,
				'agent'            => $agent,
				'agent_role'       => $agent_role,
				'agent_initials'   => self::get_contact_agent_initials( $agent ),
				'portrait'         => $portrait,
				'price_qualifier'  => $price_qualifier,
				'media_links'      => self::get_property_document_labels( $property ),
				'shortlist_button' => self::get_shortlist_button_markup( $shortlist_class, $shortlist_labels ),
				'share_button'     => $share_button,
			)
		);
	}

	/**
	 * Render the shared enquiry lightbox markup for rich detail templates.
	 *
	 * Rich templates replace PropertyHive's legacy actions box, which normally
	 * supplies this inline Fancybox target. Keep the target separate from the
	 * visible contact panel so each rich template has one matching form.
	 */
	public static function render_detail_enquiry_modal() {
		if ( ! self::is_enabled() || ! is_property() || ! self::detail_template_uses_rich_modules( self::get_detail_template() ) ) {
			return;
		}

		global $post;

		$post_id = isset( $post->ID ) ? absint( $post->ID ) : absint( get_queried_object_id() );

		if ( ! $post_id ) {
			return;
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			self::get_detail_template(),
			'enquiry-modal',
			array(
				'post_id' => $post_id,
			)
		);
	}

	/**
	 * Render a small detail label before the title.
	 */
	public static function render_detail_template_kicker() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$label    = self::get_detail_kicker_label( $property, $template );

		if ( ! $label ) {
			return;
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			$template,
			'kicker',
			array(
				'property' => $property,
				'template' => $template,
				'label'    => $label,
			)
		);
	}

	/**
	 * Render template-specific detail highlights.
	 */
	public static function render_detail_highlights() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$template   = self::get_detail_template();
		$highlights = self::get_detail_highlights( $property, $template );

		if ( empty( $highlights ) ) {
			return;
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			$template,
			'highlights',
			array(
				'property'   => $property,
				'template'   => $template,
				'highlights' => $highlights,
			)
		);
	}

	/**
	 * Render a context panel for detail templates that need extra emphasis.
	 */
	public static function render_detail_context_panel() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		global $property;

		if ( ! $property ) {
			return;
		}

		$template = self::get_detail_template();
		$panel    = self::get_detail_context_panel( $property, $template );

		if ( empty( $panel ) ) {
			return;
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			$template,
			'context-panel',
			array(
				'property' => $property,
				'template' => $template,
				'panel'    => $panel,
			)
		);
	}

	/**
	 * Render sticky mobile CTA bar.
	 */
	public static function render_mobile_cta_bar() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		if ( 'yes' !== PH_Template_Set_Request_Context::get_show_mobile_cta() && ! self::is_template_editor_active() ) {
			return;
		}

		global $post, $property;

		if ( ! $property ) {
			return;
		}

		$phone    = $property->get_negotiator_telephone_number();
		$template = self::get_detail_template();
		$button   = self::get_primary_cta_label( $property, $template );

		$shortlist_class  = 'ph-template-button ph-template-button-secondary ph-template-shortlist-button';
		$shortlist_labels = array();

		if ( 'immersive-cinema-detail' === $template ) {
			$shortlist_labels = array(
				'add'    => '♡ ' . __( 'Save', 'propertyhive' ),
				'remove' => __( 'Saved', 'propertyhive' ),
			);
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			$template,
			'mobile-cta',
			array(
				'property'         => $property,
				'post_id'          => isset( $post->ID ) ? absint( $post->ID ) : 0,
				'template'         => $template,
				'phone'            => $phone,
				'button'           => $button,
				'shortlist_button' => self::get_shortlist_button_markup( $shortlist_class, $shortlist_labels ),
			)
		);
	}

	/**
	 * Render short trust note near enquiry actions.
	 */
	public static function render_trust_note() {
		if ( ! self::is_enabled() || ! is_property() ) {
			return;
		}

		PH_Template_Set_Template_Loader::render(
			'detail',
			self::get_detail_template(),
			'trust-note',
			array(
				'template' => self::get_detail_template(),
			)
		);
	}

	/**
	 * Get detail kicker label.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return string
	 */
	private static function get_detail_kicker_label( $property, $template ) {
		$department = ph_get_custom_department_based_on( $property->department );
		$department = $department ? $department : $property->department;

		if ( 'lettings-detail' === $template || 'residential-lettings' === $department ) {
			return __( 'To let', 'propertyhive' );
		}

		if ( 'commercial' === $department ) {
			$for_sale = 'yes' === $property->for_sale;
			$to_rent  = 'yes' === $property->to_rent;

			if ( $for_sale && $to_rent ) {
				return __( 'For sale or to let', 'propertyhive' );
			}

			if ( $to_rent ) {
				return __( 'To let', 'propertyhive' );
			}

			return $for_sale ? __( 'For sale', 'propertyhive' ) : __( 'Commercial property', 'propertyhive' );
		}

		$tenure = trim( wp_strip_all_tags( (string) $property->tenure ) );
		$labels = array(
			'standard-sales-detail'         => __( 'For sale', 'propertyhive' ),
			'conversion-first-sales-detail' => $tenure ? sprintf( __( 'For sale · %s', 'propertyhive' ), $tenure ) : __( 'For sale', 'propertyhive' ),
			'immersive-cinema-detail'       => __( 'For sale', 'propertyhive' ),
			'premium-editorial-detail'      => $tenure ? sprintf( __( 'For sale · %s', 'propertyhive' ), $tenure ) : __( 'For sale', 'propertyhive' ),
			'new-homes-development-detail'  => __( 'New homes release', 'propertyhive' ),
		);

		if ( isset( $labels[ $template ] ) ) {
			return $labels[ $template ];
		}

		return __( 'Property', 'propertyhive' );
	}

	/**
	 * Get detail highlights for the active detail template.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return array
	 */
	private static function get_detail_highlights( $property, $template ) {
		$photo_count = self::get_photo_count( $property );
		$photos      = $photo_count > 1 ? sprintf(
			/* translators: %d: number of property photos */
			_n( '%d photo', '%d photos', $photo_count, 'propertyhive' ),
			$photo_count
		) : __( 'Photos available', 'propertyhive' );
		$button      = self::get_primary_cta_label( $property, $template );
		$phone       = $property->get_negotiator_telephone_number();

		if ( 'conversion-first-sales-detail' === $template ) {
			return array();
		}

		if ( 'immersive-cinema-detail' === $template ) {
			return array(
				array( 'label' => __( 'Gallery', 'propertyhive' ), 'value' => $photos ),
				array( 'label' => __( 'Facts', 'propertyhive' ), 'value' => self::get_fact_summary( $property ) ),
				array( 'label' => __( 'Viewing', 'propertyhive' ), 'value' => $button ),
			);
		}

		if ( 'premium-editorial-detail' === $template ) {
			$brief = trim( wp_strip_all_tags( get_the_excerpt( $property->id ) ) );

			return $brief ? array(
				array(
					'label' => __( 'Brief', 'propertyhive' ),
					'value' => $brief,
				),
			) : array();
		}

		if ( 'lettings-detail' === $template ) {
			return self::get_rental_highlights( $property );
		}

		if ( 'new-homes-development-detail' === $template ) {
			return array(
				array(
					'label' => __( 'Release', 'propertyhive' ),
					'value' => __( 'Development enquiry', 'propertyhive' ),
				),
				array(
					'label' => __( 'Documents', 'propertyhive' ),
					'value' => self::get_document_summary( $property ),
				),
				array(
					'label' => __( 'Interest', 'propertyhive' ),
					'value' => $button,
				),
			);
		}

		if ( 'standard-sales-detail' === $template ) {
			return array();
		}

		return array(
			array(
				'label' => __( 'Gallery', 'propertyhive' ),
				'value' => $photos,
			),
			array(
				'label' => __( 'Facts', 'propertyhive' ),
				'value' => self::get_fact_summary( $property ),
			),
			array(
				'label' => __( 'Viewing', 'propertyhive' ),
				'value' => $button,
			),
		);
	}

	/**
	 * Get template-specific context panel content for detail pages.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return array
	 */
	private static function get_detail_context_panel( $property, $template ) {
		$button = self::get_primary_cta_label( $property, $template );
		$phone  = $property->get_negotiator_telephone_number();

		if ( 'conversion-first-sales-detail' === $template ) {
			return array();
		}

		if ( 'premium-editorial-detail' === $template ) {
			return array();
		}

		if ( 'lettings-detail' === $template ) {
			return array(
				'kicker' => __( 'Rental details', 'propertyhive' ),
				'title'  => __( 'Costs and availability', 'propertyhive' ),
				'body'   => __( 'Renters can check the key practical details before arranging a viewing.', 'propertyhive' ),
				'items'  => self::get_rental_panel_items( $property ),
			);
		}

		if ( 'new-homes-development-detail' === $template ) {
			return array(
				'kicker' => __( 'Development', 'propertyhive' ),
				'title'  => __( 'Register for release details', 'propertyhive' ),
				'body'   => __( 'Use this enquiry route for plot updates, brochures, floorplans and appointment requests.', 'propertyhive' ),
				'items'  => array(
					array(
						'label' => __( 'Primary action', 'propertyhive' ),
						'value' => $button,
					),
					array(
						'label' => __( 'Brochure', 'propertyhive' ),
						'value' => self::has_brochure( $property ) ? __( 'Available', 'propertyhive' ) : __( 'Ask agent', 'propertyhive' ),
					),
					array(
						'label' => __( 'Floorplan', 'propertyhive' ),
						'value' => self::has_floorplan( $property ) ? __( 'Available', 'propertyhive' ) : __( 'Ask agent', 'propertyhive' ),
					),
				),
			);
		}

		return array();
	}

	/**
	 * Get short contact-card hint copy.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private static function get_contact_hint( $template ) {
		$hints = array(
			'conversion-first-sales-detail' => __( 'No obligation enquiry. We aim to respond the same working day.', 'propertyhive' ),
			'immersive-cinema-detail'       => __( 'Arrange a viewing or save this property for later.', 'propertyhive' ),
			'premium-editorial-detail'      => __( 'Enquiries and viewings are handled in confidence.', 'propertyhive' ),
			'lettings-detail'               => __( 'Check availability and arrange a rental viewing.', 'propertyhive' ),
			'new-homes-development-detail'  => __( 'Register for plot, brochure and appointment updates.', 'propertyhive' ),
		);

		return isset( $hints[ $template ] ) ? $hints[ $template ] : __( 'Call or request a viewing with the agent.', 'propertyhive' );
	}

	/**
	 * Build initials for the contact-card photo fallback.
	 *
	 * @param string $name Negotiator display name.
	 * @return string
	 */
	private static function get_contact_agent_initials( $name ) {
		$name = trim( wp_strip_all_tags( (string) $name ) );

		if ( '' === $name ) {
			return '';
		}

		$parts    = preg_split( '/\s+/', $name );
		$first    = isset( $parts[0] ) ? $parts[0] : '';
		$last     = count( $parts ) > 1 ? $parts[ count( $parts ) - 1 ] : '';
		$initials = self::get_string_initial( $first );

		if ( '' !== $last ) {
			$initials .= self::get_string_initial( $last );
		} else {
			$initials .= self::get_string_initial( self::get_string_slice( $first, 1 ) );
		}

		return strtoupper( substr( $initials, 0, 2 ) );
	}

	/**
	 * Get a non-repeating subline for the negotiator row.
	 *
	 * @param string $agent Agent display name.
	 * @param string $office_heading Marketed-by heading.
	 * @param string $office Office name.
	 * @return string
	 */
	private static function get_contact_agent_role( $agent, $office_heading, $office ) {
		$office         = trim( wp_strip_all_tags( (string) $office ) );
		$office_heading = trim( wp_strip_all_tags( (string) $office_heading ) );

		if ( '' === $office || '' === $agent ) {
			return '';
		}

		if ( 0 === strcasecmp( $office, $office_heading ) ) {
			return '';
		}

		return $office;
	}

	/**
	 * Get the current property object even before Property Hive sets the global.
	 *
	 * @return PH_Property|false
	 */
	private static function get_current_property() {
		global $property;

		if ( is_a( $property, 'PH_Property' ) ) {
			return $property;
		}

		$property_id = get_queried_object_id();

		if ( ! $property_id || 'property' !== get_post_type( $property_id ) ) {
			return false;
		}

		return new PH_Property( $property_id );
	}

	/**
	 * Get an office name suitable for front-end display.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_display_office_name( $property ) {
		$office = trim( wp_strip_all_tags( (string) $property->get_office_name() ) );

		if ( '' !== $office && ! preg_match( '/^(demo|example|test)(\s|$)/i', $office ) ) {
			return $office;
		}

		return __( 'Property Hive', 'propertyhive' );
	}

	/**
	 * Build readable facts for the detail preview from the property record.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_meta_items( $property ) {
		$items = array();

		if ( $property->bedrooms > 0 ) {
			$items[] = sprintf(
				/* translators: %d: number of bedrooms */
				_n( '%d bedroom', '%d bedrooms', (int) $property->bedrooms, 'propertyhive' ),
				(int) $property->bedrooms
			);
		}

		if ( $property->bathrooms > 0 ) {
			$items[] = sprintf(
				/* translators: %d: number of bathrooms */
				_n( '%d bathroom', '%d bathrooms', (int) $property->bathrooms, 'propertyhive' ),
				(int) $property->bathrooms
			);
		}

		if ( $property->reception_rooms > 0 ) {
			$items[] = sprintf(
				/* translators: %d: number of reception rooms */
				_n( '%d reception room', '%d reception rooms', (int) $property->reception_rooms, 'propertyhive' ),
				(int) $property->reception_rooms
			);
		}

		if ( $property->property_type ) {
			$items[] = $property->property_type;
		}

		if ( $property->tenure ) {
			$items[] = $property->tenure;
		}

		if ( $property->availability ) {
			$items[] = $property->availability;
		}

		if ( $property->furnished ) {
			$items[] = $property->furnished;
		}

		if ( $property->available_date ) {
			$items[] = $property->get_available_date();
		}

		if ( ( 'commercial' === $property->department || 'commercial' === ph_get_custom_department_based_on( $property->department ) ) && $property->floor_area ) {
			$items[] = $property->get_formatted_floor_area();
		}

		return array_values( array_unique( array_filter( $items ) ) );
	}

	/**
	 * Whether a detail template uses the shared, below-gallery facts strip.
	 *
	 * @param string $template Template slug.
	 * @return bool
	 */
	private static function detail_template_uses_facts_strip( $template ) {
		return in_array( sanitize_title( $template ), array( 'standard-sales-detail', 'conversion-first-sales-detail' ), true );
	}

	/**
	 * Whether a detail template replaces core content with data-mapped modules.
	 *
	 * @param string $template Template slug.
	 * @return bool
	 */
	private static function detail_template_uses_rich_modules( $template ) {
		return in_array( sanitize_title( $template ), array( 'conversion-first-sales-detail', 'immersive-cinema-detail', 'premium-editorial-detail' ), true );
	}

	/**
	 * Get room items for residential detail templates.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_room_items( $property ) {
		if ( ! $property || 'commercial' === $property->department || 'commercial' === ph_get_custom_department_based_on( $property->department ) ) {
			return array();
		}

		$count = absint( $property->_rooms );
		$rooms = array();

		for ( $index = 0; $index < $count; $index++ ) {
			$name        = trim( wp_strip_all_tags( (string) $property->{'_room_name_' . $index} ) );
			$dimensions  = trim( wp_strip_all_tags( (string) $property->{'_room_dimensions_' . $index} ) );
			$description = trim( wp_strip_all_tags( (string) $property->{'_room_description_' . $index} ) );

			if ( '' === $name && '' === $dimensions && '' === $description ) {
				continue;
			}

			$rooms[] = array(
				'name'        => $name,
				'dimensions'  => $dimensions,
				'description' => $description,
			);
		}

		if ( 1 === count( $rooms ) && in_array( strtolower( trim( $rooms[0]['name'] ) ), self::get_detail_generic_room_labels(), true ) ) {
			return array();
		}

		return $rooms;
	}

	/**
	 * Get generic room names used by single-field property descriptions.
	 *
	 * @return array
	 */
	private static function get_detail_generic_room_labels() {
		return array_unique(
			array_filter(
				array(
					'full description',
					'description',
					strtolower( trim( __( 'Full Description', 'propertyhive' ) ) ),
				)
			)
		);
	}

	/**
	 * Get the populated material-information rows used by detail templates.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_material_information( $property ) {
		if ( ! $property || ! method_exists( $property, 'get_material_information' ) ) {
			return array();
		}

		$information = $property->get_material_information();
		$utilities   = isset( $information['utilities'] ) && is_array( $information['utilities'] ) ? $information['utilities'] : array();
		$labels      = array(
			'heating'     => __( 'Heating', 'propertyhive' ),
			'electricity' => __( 'Electricity', 'propertyhive' ),
			'water'       => __( 'Water', 'propertyhive' ),
			'sewerage'    => __( 'Sewerage', 'propertyhive' ),
			'broadband'   => __( 'Broadband', 'propertyhive' ),
		);
		$items       = array();

		foreach ( $labels as $key => $label ) {
			$value = isset( $utilities[ $key ] ) ? trim( wp_strip_all_tags( (string) $utilities[ $key ] ) ) : '';
			if ( '' !== $value ) {
				$items[] = array( 'label' => $label, 'value' => $value );
			}
		}

		if ( ! empty( $information['flood_risk'] ) ) {
			$flood = is_array( $information['flood_risk'] ) ? implode( ', ', array_filter( array_map( 'wp_strip_all_tags', $information['flood_risk'] ) ) ) : wp_strip_all_tags( (string) $information['flood_risk'] );
			if ( '' !== trim( $flood ) ) {
				$items[] = array( 'label' => __( 'Flood risk', 'propertyhive' ), 'value' => trim( $flood ) );
			}
		}

		return $items;
	}

	/**
	 * Get labelled particulars for the data-mapped detail templates.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_key_facts( $property ) {
		if ( 'immersive-cinema-detail' === self::get_detail_template() ) {
			return self::get_cinema_detail_key_facts( $property );
		}

		$items = array();
		$add   = function( $label, $value ) use ( &$items ) {
			$value = trim( wp_strip_all_tags( (string) $value ) );
			if ( '' !== $value ) {
				$items[] = array( 'label' => $label, 'value' => $value );
			}
		};

		$add( __( 'Price', 'propertyhive' ), $property->get_formatted_price( true ) );
		$add( __( 'Type', 'propertyhive' ), $property->property_type );
		$add( __( 'Bedrooms', 'propertyhive' ), $property->bedrooms > 0 ? (string) (int) $property->bedrooms : '' );
		$add( __( 'Bathrooms', 'propertyhive' ), $property->bathrooms > 0 ? (string) (int) $property->bathrooms : '' );
		$add( __( 'Receptions', 'propertyhive' ), $property->reception_rooms > 0 ? (string) (int) $property->reception_rooms : '' );
		$tenure = trim( wp_strip_all_tags( (string) $property->tenure ) );
		$add( __( 'Tenure', 'propertyhive' ), $tenure );
		$add( __( 'Parking', 'propertyhive' ), $property->parking );
		$add( __( 'Outside space', 'propertyhive' ), $property->outside_space );
		$add( __( 'Council tax', 'propertyhive' ), $property->council_tax_band ? sprintf( __( 'Band %s', 'propertyhive' ), $property->council_tax_band ) : '' );
		$add( __( 'Reference', 'propertyhive' ), $property->_reference_number );

		self::append_leasehold_detail_facts( $property, $tenure, $add );

		return $items;
	}

	/**
	 * Slim "The details" rows for Immersive Cinema (type/beds live on the floating card).
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_cinema_detail_key_facts( $property ) {
		$items = array();
		$add   = function( $label, $value ) use ( &$items ) {
			$value = trim( wp_strip_all_tags( (string) $value ) );
			if ( '' !== $value ) {
				$items[] = array( 'label' => $label, 'value' => $value );
			}
		};

		$price     = trim( wp_strip_all_tags( (string) $property->get_formatted_price( true ) ) );
		$qualifier = method_exists( $property, 'get_price_qualifier' ) ? $property->get_price_qualifier() : $property->price_qualifier;
		$qualifier = self::format_price_qualifier_label( $qualifier );
		if ( $qualifier && $price ) {
			$price = $qualifier . ' ' . $price;
		}
		$add( __( 'Price', 'propertyhive' ), $price );

		$tenure = trim( wp_strip_all_tags( (string) $property->tenure ) );
		$add( __( 'Tenure', 'propertyhive' ), $tenure );
		$add( __( 'Council tax', 'propertyhive' ), $property->council_tax_band ? sprintf( __( 'Band %s', 'propertyhive' ), $property->council_tax_band ) : '' );
		$add( __( 'Parking', 'propertyhive' ), $property->parking );
		$add( __( 'Reference', 'propertyhive' ), $property->_reference_number );

		self::append_leasehold_detail_facts( $property, $tenure, $add );

		return $items;
	}

	/**
	 * Append leasehold cluster rows when tenure is leasehold / share of freehold.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $tenure   Tenure label.
	 * @param callable    $add      Row adder.
	 */
	private static function append_leasehold_detail_facts( $property, $tenure, $add ) {
		if ( false === stripos( $tenure, 'leasehold' ) && false === stripos( $tenure, 'share of freehold' ) ) {
			return;
		}

		$years = absint( $property->_leasehold_years_remaining );
		$add( __( 'Lease remaining', 'propertyhive' ), $years ? sprintf( _n( '%d year', '%d years', $years, 'propertyhive' ), $years ) : '' );
		$add( __( 'Ground rent', 'propertyhive' ), $property->_ground_rent );
		$add( __( 'Ground-rent review', 'propertyhive' ), $property->_ground_rent_review_years ? sprintf( _n( '%d year', '%d years', absint( $property->_ground_rent_review_years ), 'propertyhive' ), absint( $property->_ground_rent_review_years ) ) : '' );
		$add( __( 'Service charge', 'propertyhive' ), $property->_service_charge );
		$add( __( 'Service-charge review', 'propertyhive' ), $property->_service_charge_review_years ? sprintf( _n( '%d year', '%d years', absint( $property->_service_charge_review_years ), 'propertyhive' ), absint( $property->_service_charge_review_years ) ) : '' );
	}

	/**
	 * Build labelled facts for the below-gallery detail facts strip.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_detail_facts_strip_items( $property ) {
		$template = self::get_detail_template();
		$size     = self::split_detail_fact_size( $property->get_formatted_floor_area() );
		$items = array(
			'type'      => array(
				'label'     => __( 'Property type', 'propertyhive' ),
				'value'     => $property->property_type ? $property->property_type : '',
				'secondary' => '',
				'icon'      => 'type',
			),
			'bedrooms'  => array(
				'label'     => __( 'Bedrooms', 'propertyhive' ),
				'value'     => $property->bedrooms > 0 ? (string) (int) $property->bedrooms : '',
				'secondary' => '',
				'icon'      => 'bedrooms',
			),
			'bathrooms' => array(
				'label'     => __( 'Bathrooms', 'propertyhive' ),
				'value'     => $property->bathrooms > 0 ? (string) (int) $property->bathrooms : '',
				'secondary' => '',
				'icon'      => 'bathrooms',
			),
			'receptions' => array(
				'label'     => __( 'Receptions', 'propertyhive' ),
				'value'     => $property->reception_rooms > 0 ? (string) (int) $property->reception_rooms : '',
				'secondary' => '',
				'icon'      => 'receptions',
			),
			'size'      => array(
				'label'     => __( 'Size', 'propertyhive' ),
				'value'     => $size['value'],
				'secondary' => $size['secondary'],
				'icon'      => 'size',
			),
			'tenure'    => array(
				'label'     => __( 'Tenure', 'propertyhive' ),
				'value'     => $property->tenure ? $property->tenure : '',
				'secondary' => '',
				'icon'      => 'tenure',
			),
		);

		if ( 'standard-sales-detail' !== $template ) {
			unset( $items['size'] );
			$items['council-tax'] = array(
				'label'     => __( 'Council tax', 'propertyhive' ),
				'value'     => $property->council_tax_band ? sprintf( __( 'Band %s', 'propertyhive' ), $property->council_tax_band ) : '',
				'secondary' => '',
				'icon'      => 'tenure',
			);
		}

		// Portal Split prototype: parking sits in the facts bar with the other
		// particulars, not in a second meta list under the title.
		if ( 'conversion-first-sales-detail' === $template ) {
			$items['parking'] = array(
				'label'     => __( 'Parking', 'propertyhive' ),
				'value'     => $property->parking ? $property->parking : '',
				'secondary' => '',
				'icon'      => 'parking',
			);
		}

		return array_values(
			array_filter(
				$items,
				function( $item ) {
					return '' !== trim( (string) $item['value'] );
				}
			)
		);
	}

	/**
	 * Split a floor-area value into primary and secondary display values.
	 *
	 * @param string $size Floor area label.
	 * @return array
	 */
	private static function split_detail_fact_size( $size ) {
		$size = trim( wp_strip_all_tags( (string) $size ) );

		if ( '' === $size ) {
			return array(
				'value'     => '',
				'secondary' => '',
			);
		}

		if ( false !== strpos( $size, '/' ) ) {
			$parts = array_map( 'trim', explode( '/', $size, 2 ) );

			return array(
				'value'     => $parts[0],
				'secondary' => isset( $parts[1] ) ? $parts[1] : '',
			);
		}

		return array(
			'value'     => $size,
			'secondary' => '',
		);
	}

	/**
	 * Get a compact location label from the real address data.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_property_location_label( $property ) {
		$candidates = array(
			$property->_address_three,
			$property->_address_four,
			$property->_address_two,
			$property->_address_postcode,
			$property->get_formatted_summary_address(),
		);

		foreach ( $candidates as $candidate ) {
			if ( '' !== trim( (string) $candidate ) ) {
				return trim( (string) $candidate );
			}
		}

		return '';
	}

	/**
	 * Does the property have at least one floorplan?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_floorplan( $property ) {
		if ( ! empty( $property->get_floorplan_attachment_ids() ) ) {
			return true;
		}

		$floorplan_urls = $property->_floorplan_urls;

		return is_array( $floorplan_urls ) && ! empty( array_filter( $floorplan_urls ) );
	}

	/**
	 * Should floorplans render for this property template request?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function should_render_floorplans( $property ) {
		if ( ! $property || ! self::has_floorplan( $property ) ) {
			return false;
		}

		return 'yes' === PH_Template_Set_Request_Context::get_show_floorplans() || self::is_template_editor_active();
	}

	/**
	 * Does the property have at least one virtual tour?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_virtual_tour( $property ) {
		return $property && ! empty( $property->get_virtual_tours() );
	}

	/**
	 * Should virtual tours render for this property template request?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function should_render_virtual_tours( $property ) {
		if ( ! $property || ! self::has_virtual_tour( $property ) ) {
			return false;
		}

		return 'yes' === PH_Template_Set_Request_Context::get_show_virtual_tours() || self::is_template_editor_active();
	}

	/**
	 * Does the property have at least one EPC?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_epc( $property ) {
		if ( ! empty( $property->get_epc_attachment_ids() ) ) {
			return true;
		}

		$epc_urls = $property->_epc_urls;

		return is_array( $epc_urls ) && ! empty( array_filter( $epc_urls ) );
	}

	/**
	 * Does the property have at least one brochure?
	 *
	 * @param PH_Property $property Property object.
	 * @return bool
	 */
	private static function has_brochure( $property ) {
		if ( ! empty( $property->get_brochure_attachment_ids() ) ) {
			return true;
		}

		$brochure_urls = $property->_brochure_urls;

		return is_array( $brochure_urls ) && ! empty( array_filter( $brochure_urls ) );
	}

	/**
	 * Summarise available supporting documents.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_document_summary( $property ) {
		$documents = self::get_property_document_labels( $property );

		if ( empty( $documents ) ) {
			return __( 'Ask agent', 'propertyhive' );
		}

		return implode( ', ', array_slice( wp_list_pluck( $documents, 'label' ), 0, 3 ) );
	}

	/**
	 * Summarise core facts in one short string.
	 *
	 * @param PH_Property $property Property object.
	 * @return string
	 */
	private static function get_fact_summary( $property ) {
		$facts = array_slice( self::get_detail_meta_items( $property ), 0, 3 );

		if ( empty( $facts ) ) {
			return __( 'Details available', 'propertyhive' );
		}

		return implode( ' / ', $facts );
	}

	/**
	 * Get rental highlight items for lettings templates.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_rental_highlights( $property ) {
		return array_slice( self::get_rental_panel_items( $property ), 0, 4 );
	}

	/**
	 * Get rental-focused detail items.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_rental_panel_items( $property ) {
		$deposit = method_exists( $property, 'get_formatted_deposit' ) ? $property->get_formatted_deposit() : '';
		$items   = array(
			array(
				'label' => __( 'Available', 'propertyhive' ),
				'value' => $property->available_date ? $property->get_available_date() : __( 'Ask agent', 'propertyhive' ),
			),
			array(
				'label' => __( 'Deposit', 'propertyhive' ),
				'value' => $deposit ? $deposit : __( 'Ask agent', 'propertyhive' ),
			),
			array(
				'label' => __( 'Furnished', 'propertyhive' ),
				'value' => $property->furnished ? $property->furnished : __( 'Ask agent', 'propertyhive' ),
			),
		);

		if ( $property->council_tax_band ) {
			$items[] = array(
				'label' => __( 'Council tax', 'propertyhive' ),
				'value' => sprintf(
					/* translators: %s: council tax band */
					__( 'Band %s', 'propertyhive' ),
					$property->council_tax_band
				),
			);
		}

		return $items;
	}

	/**
	 * Get primary CTA label for a property/template.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $template Template slug.
	 * @return string
	 */
	private static function get_primary_cta_label( $property, $template ) {
		if ( 'premium-editorial-detail' === $template ) {
			return __( 'Arrange a private viewing', 'propertyhive' );
		}

		if ( 'new-homes-development-detail' === $template ) {
			return __( 'Register interest', 'propertyhive' );
		}

		if ( 'conversion-first-sales-detail' === $template ) {
			return __( 'Request viewing', 'propertyhive' );
		}

		if ( 'lettings-detail' === $template || 'residential-lettings' === $property->department || 'residential-lettings' === ph_get_custom_department_based_on( $property->department ) ) {
			return __( 'Arrange viewing', 'propertyhive' );
		}

		return __( 'Request viewing', 'propertyhive' );
	}

	/**
	 * Render media/document links in the contact card.
	 *
	 * @param PH_Property $property Property object.
	 */
	private static function render_detail_media_links( $property ) {
		$links = self::get_property_document_labels( $property );

		if ( empty( $links ) ) {
			return;
		}

		echo '<ul class="ph-template-media-links">';
		foreach ( array_slice( $links, 0, 4 ) as $link ) {
			echo '<li>' . esc_html( $link['label'] ) . '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Get supporting document labels that are available for the property.
	 *
	 * @param PH_Property $property Property object.
	 * @return array
	 */
	private static function get_property_document_labels( $property ) {
		$documents = array();

		if ( self::should_render_floorplans( $property ) ) {
			$documents[] = array(
				'label' => __( 'Floorplan', 'propertyhive' ),
				'type'  => 'floorplan',
				'url'   => self::get_first_property_document_url( $property, 'floorplan' ),
			);
		}

		if ( self::should_render_virtual_tours( $property ) ) {
			foreach ( $property->get_virtual_tours() as $index => $tour ) {
				$label = isset( $tour['label'] ) ? trim( wp_strip_all_tags( (string) $tour['label'] ) ) : '';
				$documents[] = array(
					'label' => $label ? $label : sprintf( __( 'Virtual tour %d', 'propertyhive' ), (int) $index + 1 ),
					'type'  => 'virtual-tour',
					'url'   => self::is_demo_preview() || empty( $tour['url'] ) ? '' : esc_url_raw( $tour['url'] ),
				);
			}
		}

		if ( self::has_epc( $property ) ) {
			$documents[] = array(
				'label' => __( 'EPC', 'propertyhive' ),
				'type'  => 'epc',
				'url'   => self::get_first_property_document_url( $property, 'epc' ),
			);
		}

		if ( self::has_brochure( $property ) ) {
			$documents[] = array(
				'label' => __( 'Brochure', 'propertyhive' ),
				'type'  => 'brochure',
				'url'   => self::get_first_property_document_url( $property, 'brochure' ),
			);
		}

		return $documents;
	}

	/**
	 * Get the first usable URL for a supporting property document.
	 *
	 * Demo previews deliberately remain non-navigable so an editor cannot be
	 * sent away from the preview by sample media controls.
	 *
	 * @param PH_Property $property Property object.
	 * @param string      $document_type Document type.
	 * @return string
	 */
	private static function get_first_property_document_url( $property, $document_type ) {
		if ( self::is_demo_preview() ) {
			return '';
		}

		$document_type = sanitize_key( $document_type );
		$attachment_ids = array();
		$url_entries    = array();
		$stored_as_urls = false;

		if ( 'floorplan' === $document_type ) {
			$attachment_ids = $property->get_floorplan_attachment_ids();
			$url_entries    = $property->_floorplan_urls;
			$stored_as_urls = 'urls' === get_option( 'propertyhive_floorplans_stored_as', '' );
		} elseif ( 'epc' === $document_type ) {
			$attachment_ids = $property->get_epc_attachment_ids();
			$url_entries    = $property->_epc_urls;
			$stored_as_urls = 'urls' === get_option( 'propertyhive_epcs_stored_as', '' );
		} elseif ( 'brochure' === $document_type ) {
			$attachment_ids = $property->get_brochure_attachment_ids();
			$url_entries    = $property->_brochure_urls;
			$stored_as_urls = 'urls' === get_option( 'propertyhive_brochures_stored_as', '' );
		}

		$document_sources = $stored_as_urls ? array( 'urls', 'attachments' ) : array( 'attachments', 'urls' );

		foreach ( $document_sources as $source ) {
			if ( 'attachments' === $source ) {
				foreach ( (array) $attachment_ids as $attachment_id ) {
					$url = wp_get_attachment_url( absint( $attachment_id ) );

					if ( $url ) {
						return esc_url_raw( $url );
					}
				}
				continue;
			}

			foreach ( (array) $url_entries as $entry ) {
				$url = is_array( $entry ) && isset( $entry['url'] ) ? $entry['url'] : $entry;
				$url = esc_url_raw( $url );

				if ( $url ) {
					return $url;
				}
			}
		}

		return '';
	}
}
