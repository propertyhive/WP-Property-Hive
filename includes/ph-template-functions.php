<?php
/**
 * PropertyHive Template
 *
 * Functions for the templating system.
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Functions
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * When the_post is called, put property data into a global.
 *
 * @param mixed $post
 * @return PH_Property
 */
function ph_setup_property_data( $post ) {
    unset( $GLOBALS['property'] );

    if ( is_int( $post ) )
        $post = get_post( $post );

    if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'property' ) ) )
        return;

    $GLOBALS['property'] = get_property( $post );

    return $GLOBALS['property'];
}
add_action( 'the_post', 'ph_setup_property_data' );

/**
 * Properties RSS Feed.
 *
 * @access public
 * @return void
 */
function ph_properties_rss_feed() {
    // Property RSS
    if ( is_post_type_archive( 'property' ) || is_singular( 'property' ) ) {

        $feed = get_post_type_archive_feed_link( 'property' );

        echo '<link rel="alternate" type="application/rss+xml"  title="' . esc_attr(__( 'Latest Properties', 'propertyhive' )) . '" href="' . esc_url( $feed ) . '" />';

    }
}

/**
 * Output generator tag to aid debugging.
 *
 * @access public
 * @return void
 */
function ph_generator_tag( $gen, $type ) {
    switch ( $type ) {
        case 'html':
            $gen .= "\n" . '<meta name="generator" content="PropertyHive ' . esc_attr( PH_VERSION ) . '">';
            break;
        case 'xhtml':
            $gen .= "\n" . '<meta name="generator" content="PropertyHive ' . esc_attr( PH_VERSION ) . '" />';
            break;
    }
    return $gen;
}

/**
 * Add body classes for PH pages
 *
 * @param  array $classes
 * @return array
 */
function ph_body_class( $classes ) {
    global $wp_query;

    $classes = (array) $classes;

    if ( is_propertyhive() ) {
        $classes[] = 'propertyhive';
        $classes[] = 'propertyhive-page';
    }

    // If we're on a single property, add body classes for taxonomy fields and department
    if ( is_single() )
    {
        $post = $wp_query->get_queried_object();
        if ( $post->post_type === 'property' )
        {
            // Duplication of the code in post-template-php that is done for search results posts in get_post_class()
            // All public taxonomies.
            $taxonomies = get_taxonomies( array( 'public' => true ) );
            foreach ( (array) $taxonomies as $taxonomy ) {
                if ( is_object_in_taxonomy( $post->post_type, $taxonomy ) ) {
                    foreach ( (array) get_the_terms( $post->ID, $taxonomy ) as $term ) {
                        if ( empty( $term->slug ) ) {
                            continue;
                        }

                        $term_class = sanitize_html_class( $term->slug, $term->term_id );
                        if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
                            $term_class = $term->term_id;
                        }

                        // 'post_tag' uses the 'tag' prefix for backward compatibility.
                        if ( 'post_tag' == $taxonomy ) {
                            $classes[] = 'tag-' . $term_class;
                        } else {
                            $classes[] = sanitize_html_class( $taxonomy . '-' . $term_class, $taxonomy . '-' . $term->term_id );
                        }
                    }
                }
            }
            //

            $property = get_property( $post->ID );
            $classes[] = 'department-' . $property->department;
            $classes[] = 'on-market-' . ( $property->on_market == 'yes' ? 'yes' : 'no' );
            $classes[] = 'featured-' . ( $property->featured == 'yes' ? 'yes' : 'no' );
            $classes[] = 'office-' . $property->office_id;
        }
    }

    return array_unique( $classes );
}

/**
 * Adds extra post classes for properties
 *
 * @since 1.0.0
 * @param array $classes
 * @param string|array $class
 * @param int $post_id
 * @return array
 */
function ph_property_post_class( $classes, $class = '', $post_id = '' ) {
    if ( ! $post_id || get_post_type( $post_id ) !== 'property' )
        return $classes;

    $property = get_property( $post_id );

    if ( $property ) {
        if ( $property->is_featured() ) {
            $classes[] = 'featured';
        }
    }

    if ( ( $key = array_search( 'hentry', $classes ) ) !== false ) {
        unset( $classes[ $key ] );
    }

    // Add 'property' class, removing it first incase it exists
    // Needed as results loaded with AJAX don't get the property class by default
    if ( ( $key = array_search( 'property', $classes ) ) !== false ) {
        unset( $classes[ $key ] );
    }
    $classes[] = 'property';
    $classes[] = 'department-' . $property->department;
    $classes[] = 'office-' . $property->office_id;

    $classes = array_values($classes);

    return $classes;
}

/** Global ****************************************************************/

if ( ! function_exists( 'propertyhive_output_content_wrapper' ) ) {

    /**
     * Output the start of the page wrapper.
     *
     * @access public
     * @return void
     */
    function propertyhive_output_content_wrapper() {
        ph_get_template( 'global/wrapper-start.php' );
    }
}

if ( ! function_exists( 'propertyhive_output_content_wrapper_end' ) ) {

    /**
     * Output the end of the page wrapper.
     *
     * @access public
     * @return void
     */
    function propertyhive_output_content_wrapper_end() {
        ph_get_template( 'global/wrapper-end.php' );
    }
}

/** Loop ******************************************************************/

if ( ! function_exists( 'propertyhive_page_title' ) ) {

    /**
     * propertyhive_page_title function.
     *
     * @param  boolean $echo
     * @return string
     */
    function propertyhive_page_title( $echo = true ) {

        if ( is_search() ) {
            $page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'propertyhive' ), get_search_query() );

            if ( get_query_var( 'paged' ) )
                $page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'propertyhive' ), get_query_var( 'paged' ) );

        } elseif ( is_tax() ) {

            $page_title = single_term_title( "", false );

        } else {

            $search_results_page_id = ph_get_page_id( 'search_results' );
            $page_title   = get_the_title( $search_results_page_id );

        }

        $page_title = apply_filters( 'propertyhive_page_title', $page_title );

        if ( $echo )
            echo $page_title;
        else
            return $page_title;
    }
}

if ( ! function_exists( 'propertyhive_property_loop_start' ) ) {

    /**
     * Output the start of a property loop. By default this is a UL
     *
     * @access public
     * @param bool $echo
     * @return string
     */
    function propertyhive_property_loop_start( $echo = true ) {
        ob_start();
        ph_get_template( 'search/loop-start.php' );
        if ( $echo )
            echo ob_get_clean();
        else
            return ob_get_clean();
    }
}
if ( ! function_exists( 'propertyhive_property_loop_end' ) ) {

    /**
     * Output the end of a property loop. By default this is a UL
     *
     * @access public
     * @param bool $echo
     * @return string
     */
    function propertyhive_property_loop_end( $echo = true ) {
        ob_start();

        ph_get_template( 'search/loop-end.php' );

        if ( $echo )
            echo ob_get_clean();
        else
            return ob_get_clean();
    }
}

if ( ! function_exists( 'propertyhive_template_loop_property_thumbnail' ) ) {

    /**
     * Get the property thumbnail for the loop.
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_template_loop_property_thumbnail() {
        echo propertyhive_get_property_thumbnail( apply_filters( 'property_search_results_thumbnail_size', 'medium' ) );
    }
}

if ( ! function_exists( 'propertyhive_get_property_thumbnail' ) ) {

    /**
     * Get the property thumbnail, or the placeholder if not set.
     *
     * @access public
     * @subpackage Loop
     * @param string $size (default: 'medium')
     * @param int $placeholder_width (default: 0)
     * @param int $placeholder_height (default: 0)
     * @return string
     */
    function propertyhive_get_property_thumbnail( $size = 'medium', $class = '', $placeholder_width = 0, $placeholder_height = 0  ) {
        global $post, $property;

        $photo_url = $property->get_main_photo_src( $size );

        if ($photo_url !== FALSE)
            return '<img src="' . $photo_url . '" alt="' . get_the_title($post->ID) . '" class="' . $class . '">';

        if ( ph_placeholder_img_src() )
            return ph_placeholder_img( $size );
    }
}

if ( ! function_exists( 'propertyhive_template_loop_floor_area' ) ) {

    /**
     * Get the property floor area for the loop.
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_template_loop_floor_area() {
        global $property;

        if ( $property->department == 'commercial' || ph_get_custom_department_based_on( $property->department ) == 'commercial' )
        {
            ph_get_template( 'search/floor-area.php' );
        }
    }
}

if ( ! function_exists( 'propertyhive_template_loop_price' ) ) {

    /**
     * Get the property price for the loop.
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_template_loop_price() {

        global $property;

        $fees = '';
        if ( get_option('propertyhive_lettings_fees_display_search_results', '') == 'yes' )
        {
            if (
                $property->department == 'residential-lettings' && 
                get_option('propertyhive_lettings_fees', '') != ''
            )
            {
                $fees = nl2br(get_option('propertyhive_lettings_fees', ''));
            }
            if (
                $property->department == 'commercial' && 
                $property->to_rent == 'yes' && 
                get_option('propertyhive_lettings_fees_commercial', '') != ''
            )
            {
                $fees = nl2br(get_option('propertyhive_lettings_fees_commercial', ''));
            }
        }

        $price_qualifier = '';
        if (
            ( 
                $property->department == 'residential-sales' || 
                ph_get_custom_department_based_on($property->department) == 'residential-sales' ||
                $property->department == 'commercial' || 
                ph_get_custom_department_based_on($property->department) == 'commercial'
            ) &&
            $property->price_qualifier != ''
        )
        {
            $price_qualifier = $property->price_qualifier;
        }

        ph_get_template( 'search/price.php', array( 'price_qualifier' => $price_qualifier, 'fees' => $fees ) );
    }
}

if ( ! function_exists( 'propertyhive_template_loop_summary' ) ) {

    /**
     * Get the property summary for the loop.
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_template_loop_summary() {
        ph_get_template( 'search/summary.php' );
    }
}

if ( ! function_exists( 'propertyhive_template_loop_actions' ) ) {

    /**
     * Get the actions for the loop (ie More Details).
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_template_loop_actions() {
        ph_get_template( 'search/actions.php' );
    }
}

if ( ! function_exists( 'propertyhive_search_form' ) ) {

    /**
     * Output the search form
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_search_form($id = 'default') {
        ph_get_search_form( ( $id != '' ) ? $id : 'default' );
    }
}

if ( ! function_exists( 'propertyhive_result_count' ) ) {

    /**
     * Output the result count text (Showing x - x of x results).
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_result_count( $paged = '', $per_page = null, $total = null, $first = null, $last = null ) {
        global $wp_query;

        $paged = $paged !== '' ? $paged : max( 1, $wp_query->get( 'paged' ) );
        $per_page = $per_page !== null ? $per_page : $wp_query->get( 'posts_per_page' );
        $total = $total !== null ? $total : $wp_query->found_posts;
        $first = $first !== null ? $first : ( $per_page * $paged ) - $per_page + 1;
        $last = $last !== null ? $last : min( $total, $wp_query->get( 'posts_per_page' ) * $paged );

        $args = array(
            'paged'    => $paged,
            'per_page' => $per_page,
            'total'    => $total,
            'first'    => $first,
            'last'     => $last,
        );

        ph_get_template( 'search/result-count.php', $args );
    }
}

if ( ! function_exists( 'propertyhive_catalog_ordering' ) ) {

    /**
     * Output the property sorting options.
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_catalog_ordering( $department = '', $orderby = '' ) {

        if ( $orderby === '' )
        {
            $orderby = isset( $_GET['orderby'] ) ? ph_clean( sanitize_text_field($_GET['orderby']) ) : apply_filters( 'propertyhive_default_search_results_orderby', get_option( 'propertyhive_default_search_results_orderby' ) );
        }

        $args = array(
            'department' => $department !== '' ? $department : ( isset($_REQUEST['department']) ? $_REQUEST['department'] : '' ),
            'orderby' =>  $orderby,
        );

        ph_get_template( 'search/orderby.php', $args );
    }
}

if ( ! function_exists( 'propertyhive_pagination' ) ) {

    /**
     * Output the pagination.
     *
     * @access public
     * @subpackage  Loop
     * @return void
     */
    function propertyhive_pagination( $max_num_pages = '' ) {

        global $wp_query;

        $args = array(
            'max_num_pages' => $max_num_pages !== '' ? $max_num_pages : $wp_query->max_num_pages,
        );

        ph_get_template( 'search/pagination.php', $args );
    }
}

/** Single Property ********************************************************/

if ( ! function_exists( 'propertyhive_template_not_on_market' ) ) {

    /**
     * Output a warning/message if property is not on the market
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_not_on_market() {
        ph_get_template( 'single-property/not-on-market.php' );
    }
}

if ( get_option('propertyhive_off_market_behaviour', '') == 'redirect' )
{
    function ph_redirect_off_market_properties()
    {
        // If we're viewing an off market property, redirect to the search form
        if (is_singular('property'))
        {
            if ( get_post_meta(get_the_ID(), '_on_market', TRUE) === '' )
            {
                if ( !is_user_logged_in() || !current_user_can('administrator') && !current_user_can('editor') )
                {
                    wp_redirect(get_permalink(ph_get_page_id('search_results')), 301);
                    exit;
                }
            }
        }
    }
    add_action( 'template_redirect', 'ph_redirect_off_market_properties', 5 );
}

if ( ! function_exists( 'propertyhive_show_property_images' ) ) {

    /**
     * Output the property image before the single property summary.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_show_property_images( $num_images = '' ) 
    {
        global $property;

        $images = array();
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photo_urls = $property->_photo_urls;
            if ( !is_array($photo_urls) ) { $photo_urls = array(); }

            if ( !empty($num_images) )
            {
                $photo_urls = array_slice($photo_urls, 0, $num_images);
            }

            foreach ( $photo_urls as $photo )
            {
                $images[] = array(
                    'title' => isset($photo['title']) ? $photo['title'] : '',
                    'url'  => isset($photo['url']) ? $photo['url'] : '',
                    'image' => '<img src="' . ( isset($photo['url']) ? $photo['url'] : '' ) . '" alt="' . ( isset($photo['title']) ? $photo['title'] : '' ) . '">',
                );
            }
        }
        else
        {
            $gallery_attachments = $property->get_gallery_attachment_ids();

            if ( !empty($gallery_attachments) ) 
            {
                if ( !empty($num_images) )
                {
                    $gallery_attachments = array_slice($gallery_attachments, 0, $num_images);
                }
            
                foreach ($gallery_attachments as $gallery_attachment)
                {
                    $images[] = array(
                        'title' => esc_attr( get_the_title( $gallery_attachment ) ),
                        'url'  => wp_get_attachment_url( $gallery_attachment ),
                        'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'propertyhive_single_property_image_size', 'original' ) ),
                        'attachment_id' => $gallery_attachment,
                    );
                }
            }
        }

        ph_get_template( 'single-property/property-images.php', array( 'images' => $images ) );
    }
}

if ( ! function_exists( 'propertyhive_show_property_thumbnails' ) ) {

    /**
     * Output the property thumbnails.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_show_property_thumbnails() {

        global $property;

        $images = array();
        if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
        {
            $photo_urls = $property->_photo_urls;
            if ( !is_array($photo_urls) ) { $photo_urls = array(); }
            
            foreach ( $photo_urls as $photo )
            {
                $images[] = array(
                    'title' => isset($photo['title']) ? $photo['title'] : '',
                    'url'  => isset($photo['url']) ? $photo['url'] : '',
                    'image' => '<img src="' . ( isset($photo['url']) ? $photo['url'] : '' ) . '" alt="' . ( isset($photo['title']) ? $photo['title'] : '' ) . '">',
                );
            }
        }
        else
        {
            $gallery_attachments = $property->get_gallery_attachment_ids();

            if ( !empty($gallery_attachments) ) 
            {
                foreach ($gallery_attachments as $gallery_attachment)
                {
                    $images[] = array(
                        'title' => esc_attr( get_the_title( $gallery_attachment ) ),
                        'url'  => wp_get_attachment_url( $gallery_attachment ),
                        'image' => wp_get_attachment_image( $gallery_attachment, apply_filters( 'single_property_small_thumbnail_size', 'thumbnail' ) ),
                        'attachment_id' => $gallery_attachment,
                    );
                }
            }
        }

        ph_get_template( 'single-property/property-thumbnails.php', array( 'images' => $images ) );
    }
}

if ( ! function_exists( 'propertyhive_template_single_title' ) ) {

    /**
     * Output the property title.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_title() {
        ph_get_template( 'single-property/title.php' );
    }
}

if ( ! function_exists( 'propertyhive_template_single_floor_area' ) ) {

    /**
     * Output the property floor area.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_floor_area() {
        global $property;

        if ( $property->department == 'commercial' || ph_get_custom_department_based_on( $property->department ) == 'commercial' )
        {
            ph_get_template( 'single-property/floor-area.php' );
        }
    }
}

if ( ! function_exists( 'propertyhive_template_single_price' ) ) {

    /**
     * Output the property price.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_price() {
        
        global $property;

        $fees = '';
        if ( get_option('propertyhive_lettings_fees_display_single_property', '') == 'yes' )
        {
            if (
                $property->department == 'residential-lettings' && 
                get_option('propertyhive_lettings_fees', '') != ''
            )
            {
                $fees = nl2br(get_option('propertyhive_lettings_fees', ''));
            }
            if (
                $property->department == 'commercial' && 
                $property->to_rent == 'yes' && 
                get_option('propertyhive_lettings_fees_commercial', '') != ''
            )
            {
                $fees = nl2br(get_option('propertyhive_lettings_fees_commercial', ''));
            }
        }

        $price_qualifier = '';
        if (
            ( 
                $property->department == 'residential-sales' || 
                ph_get_custom_department_based_on($property->department) == 'residential-sales' ||
                $property->department == 'commercial' || 
                ph_get_custom_department_based_on($property->department) == 'commercial'
            ) &&
            $property->price_qualifier != ''
        )
        {
            $price_qualifier = $property->price_qualifier;
        }

        ph_get_template( 'single-property/price.php', array( 'price_qualifier' => $price_qualifier, 'fees' => $fees ) );
    }
}

if ( ! function_exists( 'propertyhive_template_single_meta' ) ) {

    /**
     * Output the product meta.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_meta() {

        global $post, $property;

        $meta = array();

        if ( $property->reference_number != '' )
        {
            $meta['reference-number'] = array(
                'label' => __('Ref', 'propertyhive'),
                'value' => $property->reference_number
            );
        }

        if ( $property->property_type != '' )
        {
            $meta['property-type'] = array(
                'label' =>  __('Type', 'propertyhive'),
                'value' => $property->property_type
            );
        }

        if ( $property->availability != '' )
        {
            $meta['availability'] = array(
                'label' => __('Availability', 'propertyhive'),
                'value' => $property->availability
            );
        }

        if ( $property->department != 'commercial' && ph_get_custom_department_based_on( $property->department ) != 'commercial' ) 
        {
            if ( $property->bedrooms > 0 )
            {
                $meta['bedrooms'] = array(
                    'label' => __('Bedrooms', 'propertyhive'),
                    'value' => $property->bedrooms
                );
            }

            if ( $property->bathrooms > 0 )
            {
                $meta['bathrooms'] = array(
                    'label' => __('Bathrooms', 'propertyhive'),
                    'value' => $property->bathrooms
                );
            }

            if ( $property->reception_rooms > 0 )
            {
                $meta['reception-rooms'] = array(
                    'label' => __('Reception Rooms', 'propertyhive'),
                    'value' => $property->reception_rooms
                );
            }

            if ( $property->parking != '' )
            {
                $meta['parking'] = array(
                    'label' => __('Parking', 'propertyhive'),
                    'value' => $property->parking
                );
            }

            if ( $property->outside_space != '' )
            {
                $meta['outside-space'] = array(
                    'label' => __('Outside Space', 'propertyhive'),
                    'value' => $property->outside_space
                );
            }

            if ( $property->council_tax_band != '' )
            {
                $meta['council-tax-band'] = array(
                    'label' => __('Council Tax Band', 'propertyhive'),
                    'value' => $property->council_tax_band
                );
            }
        }

        if ( $property->department == 'residential-sales' || ph_get_custom_department_based_on( $property->department ) == 'residential-sales' ) 
        {
            if ( $property->tenure != '' )
            {
                $meta['tenure'] = array(
                    'label' => __('Tenure', 'propertyhive'),
                    'value' => $property->tenure
                );

                if ( in_array( strtolower($property->tenure), apply_filters('propertyhive_leasehold_tenure_names', array( 'leasehold', 'share of freehold' ) ) ) )
                {
                    if ( $property->leasehold_years_remaining != '' )
                    {
                        $meta['leasehold-years-remaining'] = array(
                            'label' => __('Leasehold Remaining', 'propertyhive'),
                            'value' => $property->leasehold_years_remaining . ' ' . __( 'years', 'propertyhive' )
                        );
                    }

                    if ( $property->ground_rent != '' )
                    {
                        $meta['ground-rent'] = array(
                            'label' => __('Ground Rent', 'propertyhive'),
                            'value' => '&pound;' . ph_display_price_field($property->ground_rent, true)
                        );
                    }

                    if ( $property->ground_rent_review_years != '' )
                    {
                        $meta['ground-rent-review-years'] = array(
                            'label' => __('Ground Rent Review Period', 'propertyhive'),
                            'value' => $property->ground_rent_review_years . ' ' . __( 'years', 'propertyhive' )
                        );
                    }

                    if ( $property->service_charge != '' )
                    {
                        $meta['service-charge'] = array(
                            'label' => __('Service Charge', 'propertyhive'),
                            'value' => '&pound;' . ph_display_price_field($property->service_charge, true)
                        );
                    }

                    if ( $property->service_charge_review_years != '' )
                    {
                        $meta['service-charge-review-years'] = array(
                            'label' => __('Service Charge Review Period', 'propertyhive'),
                            'value' => $property->service_charge_review_years . ' ' . __( 'years', 'propertyhive' )
                        );
                    }

                    if ( $property->shared_ownership == 'yes' )
                    {
                        $meta['shared-ownership'] = array(
                            'label' => __('Shared Ownership', 'propertyhive'),
                            'value' => $property->shared_ownership . ( $property->shared_ownership_percentage != '' ? ' (' . $property->shared_ownership_percentage . '%)' : '' )
                        );
                    }
                }
            }
        }

        if ( $property->department == 'residential-lettings' || ph_get_custom_department_based_on( $property->department ) == 'residential-lettings' ) 
        {
            if ( $property->furnished != '' )
            {
                $meta['furnished'] = array(
                    'label' => __('Furnished', 'propertyhive'),
                    'value' => $property->furnished
                );
            }

            if ( $property->deposit > 0 )
            {
                $meta['deposit'] = array(
                    'label' => __('Deposit', 'propertyhive'),
                    'value' => $property->get_formatted_deposit()
                );
            }

            if ( $property->available_date != '' )
            {
                $meta['available-date'] = array(
                    'label' => __('Available', 'propertyhive'),
                    'value' => $property->get_available_date()
                );
            }
        }

        $meta = apply_filters( 'propertyhive_single_property_meta', $meta );

        ph_get_template( 'single-property/meta.php', array( 'meta' => $meta ) );
    }
}

if ( ! function_exists( 'propertyhive_template_single_sharing' ) ) {

    /**
     * Output the product sharing.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_sharing() {
        ph_get_template( 'single-property/share.php' );
    }
}

if ( ! function_exists( 'propertyhive_get_template_single_floorplans_action' ) ) {

    function propertyhive_get_template_single_floorplans_action() {

        global $post, $property;

        $actions = array();

        if ( get_option('propertyhive_floorplans_stored_as', '') == 'urls' )
        {
            $floorplan_urls = $property->_floorplan_urls;
            if ( is_array($floorplan_urls) && !empty( $floorplan_urls ) )
            {
                foreach ($floorplan_urls as $floorplan)
                {
                    $actions[] = array(
                        'href' => ( ( isset($floorplan['url']) ) ? $floorplan['url'] : '' ),
                        'label' => __( 'Floorplan', 'propertyhive' ),
                        'class' => 'action-floorplans',
                        'attributes' => array(
                            'data-fancybox' => 'floorplans'
                        )
                    );
                }
            }
        }
        else
        {
            $floorplan_ids = $property->get_floorplan_attachment_ids();
            if ( !empty( $floorplan_ids ) )
            {
                foreach ($floorplan_ids as $floorplan_id)
                {
                    $label = 'Floorplan';

                    $attachment_data = wp_prepare_attachment_for_js( $floorplan_id );
                    if ( isset( $attachment_data['caption'] ) && $attachment_data['caption'] != '' )
                    {
                        $label = $attachment_data['caption'];
                    }
                    

                    $actions[] = array(
                        'href' => wp_get_attachment_url( $floorplan_id ),
                        'label' => __( $label, 'propertyhive' ),
                        'class' => 'action-floorplans',
                        'attributes' => array(
                            'data-fancybox' => 'floorplans'
                        )
                    );
                }
            }
        }

        $actions = apply_filters( 'propertyhive_single_property_floorplans_actions', $actions );

        return $actions;
    }

}

if ( ! function_exists( 'propertyhive_get_template_single_brochures_action' ) ) {

    function propertyhive_get_template_single_brochures_action() {

        global $post, $property;

        $actions = array();

        if ( get_option('propertyhive_brochures_stored_as', '') == 'urls' )
        {
            $brochure_urls = $property->_brochure_urls;
            if ( is_array($brochure_urls) && !empty( $brochure_urls ) )
            {
                foreach ($brochure_urls as $brochure)
                {
                    $actions[] = array(
                        'href' => ( ( isset($brochure['url']) ) ? $brochure['url'] : '' ),
                        'label' => __( 'View Brochure', 'propertyhive' ),
                        'class' => 'action-brochure',
                        'attributes' => array(
                            'target' => '_blank'
                        )
                    );
                }
            }
        }
        else
        {
            $brochure_ids = $property->get_brochure_attachment_ids();
            if ( !empty( $brochure_ids ) )
            {
                foreach ($brochure_ids as $brochure_id)
                {
                    $actions[] = array(
                        'href' => wp_get_attachment_url( $brochure_id ),
                        'label' => __( 'View Brochure', 'propertyhive' ),
                        'class' => 'action-brochure',
                        'attributes' => array(
                            'target' => '_blank'
                        )
                    );
                }
            }
        }

        $actions = apply_filters( 'propertyhive_single_property_brochures_actions', $actions );

        return $actions;
    }

}

if ( ! function_exists( 'propertyhive_get_template_single_epcs_action' ) ) {

    function propertyhive_get_template_single_epcs_action() {

        global $post, $property;

        $actions = array();

        if ( get_option('propertyhive_epcs_stored_as', '') == 'urls' )
        {
            $epc_urls = $property->_epc_urls;
            if ( is_array($epc_urls) && !empty( $epc_urls ) )
            {
                foreach ($epc_urls as $epc)
                {
                    $actions[] = array(
                        'href' => ( ( isset($epc['url']) ) ? $epc['url'] : '' ),
                        'label' => __( 'View EPC', 'propertyhive' ),
                        'class' => 'action-epc',
                        'attributes' => array(
                            'target' => '_blank'
                        )
                    );
                }
            }
        }
        else
        {
            $epc_ids = $property->get_epc_attachment_ids();
            if ( !empty( $epc_ids ) )
            {
                foreach ($epc_ids as $epc_id)
                {
                    $attributes = array('target' => '_blank');
                    if ( wp_attachment_is_image($epc_id) )
                    {
                        $attributes = array('data-fancybox' => 'epcs');
                    }
                    $actions[] = array(
                        'href' => wp_get_attachment_url( $epc_id ),
                        'label' => __( 'View EPC', 'propertyhive' ),
                        'class' => 'action-epc',
                        'attributes' => $attributes
                    );
                }
            }
        }

        $actions = apply_filters( 'propertyhive_single_property_epcs_actions', $actions );

        return $actions;
    }

}

if ( ! function_exists( 'propertyhive_get_template_single_virtual_tours_action' ) ) {

    function propertyhive_get_template_single_virtual_tours_action() {

        global $post, $property;

        $actions = array();

        $virtual_tours = $property->get_virtual_tours();
        if ( !empty( $virtual_tours ) )
        {
            foreach ($virtual_tours as $virtual_tour)
            {
                $attributes = array('target' => '_blank');
                if ( strpos($virtual_tour['url'], 'yout') !== FALSE || strpos($virtual_tour['url'], 'vimeo') !== FALSE )
                {
                    $attributes['data-fancybox'] = '';
                }

                $actions[] = array(
                    'href' => $virtual_tour['url'],
                    'label' => __( $virtual_tour['label'], 'propertyhive' ),
                    'class' => 'action-virtual-tour',
                    'attributes' => $attributes,
                );
            }
        }

        $actions = apply_filters( 'propertyhive_single_property_virtual_tours_actions', $actions );

        return $actions;
    }

}

if ( ! function_exists( 'propertyhive_get_template_single_material_information_action' ) ) {

    function propertyhive_get_template_single_material_information_action() {

        global $post, $property;

        $actions = array();

        $material_information = $property->get_material_information();

        // Only show if at least one bit of material information has been completed
        if ( empty($material_information) )
        {
            return $actions;
        }

        $actions[] = array(
            'href' => 'javascript:;',
            'label' => __( 'Utilities & More', 'propertyhive' ),
            'class' => 'action-material-information',
            'attributes' => array( 
                'data-fancybox' => '', 
                'data-src' => '#ph_material_information' 
            ),
        );

        echo '<div id="ph_material_information" style="display:none;">';

            ph_get_template( 'single-property/material-information.php' );

        echo '</div>';

        $actions = apply_filters( 'propertyhive_single_property_material_information_actions', $actions );

        return $actions;
    }

}

if ( ! function_exists( 'propertyhive_get_template_single_actions' ) ) {

    function propertyhive_get_template_single_actions() {

        global $post, $property;

        $actions = array_merge(
            propertyhive_get_template_single_floorplans_action(),
            propertyhive_get_template_single_brochures_action(),
            propertyhive_get_template_single_epcs_action(),
            propertyhive_get_template_single_virtual_tours_action(),
            propertyhive_get_template_single_material_information_action(),
        );

        
        $actions = apply_filters( 'propertyhive_single_property_actions', $actions );

        return $actions;
    }

}

if ( ! function_exists( 'propertyhive_template_single_actions' ) ) {

    /**
     * Output the product actions (make enquiry etc)
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_actions() {

        global $post, $property;

        $actions = propertyhive_get_template_single_actions();
        
        ph_get_template( 'single-property/actions.php', array( 'actions' => $actions ) );
    }
}

if ( ! function_exists( 'propertyhive_template_single_features' ) ) {

    /**
     * Output the property features.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_features() {
        ph_get_template( 'single-property/features.php' );
    }
}

if ( ! function_exists( 'propertyhive_template_single_summary' ) ) {

    /**
     * Output the product summary description.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_summary() {
        ph_get_template( 'single-property/summary-description.php' );
    }
}


if ( ! function_exists( 'propertyhive_template_single_description' ) ) {
    
    /**
     * Output the product rooms or descriptions to form a full description
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_template_single_description() {
        ph_get_template( 'single-property/description.php' );
    }
}

if ( ! function_exists( 'propertyhive_make_enquiry_button' ) ) {

    /**
     * Output the make enquiry button and lightbox
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_make_enquiry_button() {
        ph_get_template( 'global/make-enquiry.php' );
    }
}

function propertyhive_my_account_pages()
{
    $user_id = get_current_user_id();

    $contact = new PH_Contact( '', $user_id );

    $pages = array(
        'dashboard' => array(
            'name' => __( 'Dashboard', 'propertyhive' )
        ),
        'details' => array(
            'name' => __( 'My Details', 'propertyhive' )
        ),
    ); 

    // Add 'requirements' tab if applicant

    if ( !empty($contact->contact_types) )
    {   
        $contact_types = $contact->contact_types;
        if ( !is_array($contact_types) )
        {
            $contact_types = array($contact_types);
        }
        if ( in_array('applicant', $contact_types) )
        {
            $applicant_profiles = $contact->applicant_profiles;

            $buy_rent = array();

            if ( $applicant_profiles && $applicant_profiles > 0 )
            {
                for ( $i = 0; $i < $applicant_profiles; ++$i )
                {
                    $pages['requirements'] = array(
                        'name' => __( 'Requirements', 'propertyhive' ),
                    );

                    $applicant_profile = $contact->{'applicant_profile_' . $i};
                    
                    if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-sales' )
                    {
                        $buy_rent[] = 'To Buy';
                    }
                    if ( isset($applicant_profile['department']) && $applicant_profile['department'] == 'residential-lettings' )
                    {
                        $buy_rent[] = 'To Rent';
                    }
                }
            }

            // Check viewing module is active and that viewings exist, either future or past
            if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
            {
                $args = array(
                    'post_type'   => 'viewing', 
                    'posts_per_page'    => 1,
                    'post_status'   => 'publish',
                    'fields' => 'ids',
                    'meta_query'  => array(
                        array(
                            'key' => '_applicant_contact_id',
                            'value' => $contact->id
                        )
                    )
                );
                $viewings_query = new WP_Query( $args );

                if ( $viewings_query->have_posts() )
                {
                    $pages['applicant_viewings'] = array(
                        'name' => __( 'Viewings', 'propertyhive' ) . ( !empty($buy_rent) ? ' ' . implode('/', $buy_rent) : '' ),
                    );
                }
                wp_reset_postdata();
            }
        }
        if ( in_array('owner', $contact_types) )
        {
            $sell_let = array();

            // Get properties belonging to this owner
            $args = array(
                'post_type'   => 'property', 
                'nopaging'    => true,
                'post_status'   => 'publish',
                'fields' => 'ids',
                'meta_query'  => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_owner_contact_id',
                        'value' => ':' . $contact->id . ';',
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_owner_contact_id',
                        'value' => ':"' . $contact->id . '";',
                        'compare' => 'LIKE'
                    )
                )
            );

            $properties_query = new WP_Query( $args );

            $property_ids = array();

            if ( $properties_query->have_posts() )
            {
                while ( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    $property_ids[] = get_the_ID();

                    if ( get_post_meta( get_the_ID(), '_department', TRUE ) == 'residential-sales' )
                    {
                        $sell_let[] = 'To Sell';
                    }
                    if ( get_post_meta( get_the_ID(), '_department', TRUE ) == 'residential-lettings' )
                    {
                        $sell_let[] = 'To Let';
                    }
                }

                $pages['owner_properties'] = array(
                    'name' => __( 'Properties', 'propertyhive' ),
                );
            }
            wp_reset_postdata();

            // Check viewing module is active and that viewings exist, either future or past
            if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
            {
                $past_viewings = array();
                $upcoming_viewings = array();

                if ( !empty($property_ids) )
                {
                    $args = array(
                        'post_type'   => 'viewing', 
                        'posts_per_page'    => 1,
                        'post_status'   => 'publish',
                        'fields' => 'ids',
                        'meta_query'  => array(
                            array(
                                'key' => '_property_id',
                                'value' => $property_ids,
                                'compare' => 'IN'
                            )
                        )
                    );
                    $viewings_query = new WP_Query( $args );

                    if ( $viewings_query->have_posts() )
                    {
                        $pages['owner_viewings'] = array(
                            'name' => __( 'Viewings', 'propertyhive' ) . ( !empty($sell_let) ? ' ' . implode('/', $sell_let) : '' ),
                        );
                    }
                    wp_reset_postdata();
                }
            }
        }
    }

    $pages = apply_filters( 'propertyhive_my_account_pages', $pages );

    /*$pages['delete'] = array(
        'name' => __( 'Delete Account', 'propertyhive' )
    );*/

    $pages['logout'] = array(
        'name' => __( 'Logout', 'propertyhive' ),
        'href' => home_url() . '?logout=1' // Logout URL
    );
    
    return $pages;
}

if ( ! function_exists( 'propertyhive_my_account_navigation' ) ) {

    /**
     * Output the navigation/tabs within a users 'My Account' page
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_navigation() {

        $pages = propertyhive_my_account_pages();

        ph_get_template( 'account/navigation.php', array( 'pages' => $pages ) );
    }
}

if ( ! function_exists( 'propertyhive_my_account_sections' ) ) {

    /**
     * Output the main sections within a users 'My Account' page that relate to the navigation tabs/links
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_sections() {

        $pages = propertyhive_my_account_pages();

        ph_get_template( 'account/sections.php', array( 'pages' => $pages ) );
    }
}

if ( ! function_exists( 'propertyhive_my_account_dashboard' ) ) {

    /**
     * Output the dashboard section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_dashboard() {

        ph_get_template( 'account/dashboard.php' );
    }
}

if ( ! function_exists( 'propertyhive_my_account_details' ) ) {

    /**
     * Output the details section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_details() {

        $form_controls = ph_get_user_details_form_fields();
    
        $form_controls = apply_filters( 'propertyhive_user_details_form_fields', $form_controls );

        // Make sure password fields aren't require
        foreach ( $form_controls as $i => $form_control )
        {
            if ( $form_control['type'] == 'password' )
            {
                $form_control['required'] = false;

                $form_controls[$i] = $form_control;
            }
        }

        ph_get_template( 'account/details.php', array( 'form_controls' => $form_controls ) );
    }
}

if ( ! function_exists( 'propertyhive_my_account_requirements' ) ) {

    /**
     * Output the requirements section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_requirements() {

        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        if ( is_array($contact->contact_types) && in_array('applicant', $contact->contact_types) )
        {
            $applicant_profiles = $contact->applicant_profiles;

            for ( $i = 0; $i < $applicant_profiles; ++$i )
            {
                $form_controls = ph_get_applicant_requirements_form_fields($contact->{'applicant_profile_' . $i});
            
                $form_controls = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls, $contact->{'applicant_profile_' . $i} );

                $form_controls['profile_id'] = array(
                    'type' => 'hidden',
                    'value' => $i,
                );

                // Remove office as this is only required on initial sign up (at the moment anyway)
                if ( isset($form_controls['office_id']) )
                {
                    unset($form_controls['office_id']);
                }

                ph_get_template( 'account/requirements.php', array( 'form_controls' => $form_controls ) );
            }
        }
    }
}

if ( ! function_exists( 'propertyhive_my_account_applicant_viewings' ) ) {

    /**
     * Output the applicant viewings section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_applicant_viewings() {

        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        $past_viewings = array();
        $upcoming_viewings = array();

        $args = array(
            'post_type'   => 'viewing', 
            'nopaging'    => 'true',
            'post_status'   => 'publish',
            'fields' => 'ids',
            'orderby'   => 'meta_value',
            'order'       => 'DESC',
            'post_status'   => 'publish',
            'meta_key'  => '_start_date_time',
            'meta_query'  => array(
                array(
                    'key' => '_applicant_contact_id',
                    'value' => $contact->id
                )
            )
        );

        // Do past viewings
        $args2 = $args;
        $args2['meta_query'][] = array(
            'key' => '_start_date_time',
            'value' => date("Y-m-d H:i:s"),
            'compare' => '<='
        );

        $viewings_query = new WP_Query( $args2 );

        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();

                $viewing = new PH_Viewing( get_the_ID() );

                $past_viewings[] = $viewing;
            }
        }
        wp_reset_postdata();

        // Do upcoming viewings
        $args2 = $args;
        $args2['meta_query'][] = array(
            'key' => '_start_date_time',
            'value' => date("Y-m-d H:i:s"),
            'compare' => '>='
        );

        $viewings_query = new WP_Query( $args2 );

        if ( $viewings_query->have_posts() )
        {
            while ( $viewings_query->have_posts() )
            {
                $viewings_query->the_post();

                $viewing = new PH_Viewing( get_the_ID() );

                $upcoming_viewings[] = $viewing;
            }
        }
        wp_reset_postdata();

        ph_get_template( 'account/applicant-viewings.php', array( 'past_viewings' => $past_viewings, 'upcoming_viewings' => $upcoming_viewings ) );
    }
}

if ( ! function_exists( 'propertyhive_my_account_owner_properties' ) ) {

    /**
     * Output the owner properties section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_owner_properties() {

        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        // Get properties belonging to this owner
        $args = array(
            'post_type'   => 'property', 
            'nopaging'    => true,
            'post_status'   => 'publish',
            'fields' => 'ids',
            'meta_query'  => array(
                'relation' => 'OR',
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':' . $contact->id . ';',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':"' . $contact->id . '";',
                    'compare' => 'LIKE'
                )
            )
        );

        $properties_query = new WP_Query( $args );

        $properties = array();

        if ( $properties_query->have_posts() )
        {
            while ( $properties_query->have_posts() )
            {
                $properties_query->the_post();

                $properties[] = new PH_Property( get_the_id() );
            }
        }
        wp_reset_postdata();
        ph_get_template( 'account/owner-properties.php', array( 'properties' => $properties ) );
    }
}

if ( ! function_exists( 'propertyhive_my_account_owner_viewings' ) ) {

    /**
     * Output the owner viewings section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_owner_viewings() {

        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        // Get properties belonging to this owner
        $args = array(
            'post_type'   => 'property', 
            'nopaging'    => true,
            'post_status'   => 'publish',
            'fields' => 'ids',
            'meta_query'  => array(
                'relation' => 'OR',
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':' . $contact->id . ';',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':"' . $contact->id . '";',
                    'compare' => 'LIKE'
                )
            )
        );

        $properties_query = new WP_Query( $args );

        $property_ids = array();

        if ( $properties_query->have_posts() )
        {
            while ( $properties_query->have_posts() )
            {
                $properties_query->the_post();

                $property_ids[] = get_the_id();
            }
        }
        wp_reset_postdata();

        $past_viewings = array();
        $upcoming_viewings = array();

        if ( !empty($property_ids) )
        {
            $args = array(
                'post_type'   => 'viewing', 
                'nopaging'    => true,
                'post_status'   => 'publish',
                'fields' => 'ids',
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'post_status'   => 'publish',
                'meta_key'  => '_start_date_time',
                'meta_query'  => array(
                    array(
                        'key' => '_property_id',
                        'value' => $property_ids,
                        'compare' => 'IN'
                    )
                )
            );

            // Do past viewings
            $args2 = $args;
            $args2['meta_query'][] = array(
                'key' => '_start_date_time',
                'value' => date("Y-m-d H:i:s"),
                'compare' => '<='
            );

            $viewings_query = new WP_Query( $args2 );

            if ( $viewings_query->have_posts() )
            {
                while ( $viewings_query->have_posts() )
                {
                    $viewings_query->the_post();

                    $viewing = new PH_Viewing( get_the_ID() );

                    $past_viewings[] = $viewing;
                }
            }
            wp_reset_postdata();

            // Do upcoming viewings
            $args2 = $args;
            $args2['meta_query'][] = array(
                'key' => '_start_date_time',
                'value' => date("Y-m-d H:i:s"),
                'compare' => '>='
            );

            $viewings_query = new WP_Query( $args2 );

            if ( $viewings_query->have_posts() )
            {
                while ( $viewings_query->have_posts() )
                {
                    $viewings_query->the_post();

                    $viewing = new PH_Viewing( get_the_ID() );

                    $upcoming_viewings[] = $viewing;
                }
            }
            wp_reset_postdata();
        }

        ph_get_template( 'account/owner-viewings.php', array( 'past_viewings' => $past_viewings, 'upcoming_viewings' => $upcoming_viewings ) );
    }
}

if ( ! function_exists( 'propertyhive_my_account_delete' ) ) {

    /**
     * Output the delete section within a users account
     *
     * @access public
     * @return void
     */
    function propertyhive_my_account_delete() {

        ph_get_template( 'account/delete.php' );
    }
}