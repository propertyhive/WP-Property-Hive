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

        echo '<link rel="alternate" type="application/rss+xml"  title="' . __( 'Latest Properties', 'propertyhive' ) . '" href="' . esc_attr( $feed ) . '" />';

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
    $classes = (array) $classes;

    if ( is_propertyhive() ) {
        $classes[] = 'propertyhive';
        $classes[] = 'propertyhive-page';
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
        echo propertyhive_get_property_thumbnail();
    }
}

if ( ! function_exists( 'propertyhive_get_property_thumbnail' ) ) {

    /**
     * Get the property thumbnail, or the placeholder if not set.
     *
     * @access public
     * @subpackage  Loop
     * @param string $size (default: 'shop_catalog')
     * @param int $placeholder_width (default: 0)
     * @param int $placeholder_height (default: 0)
     * @return string
     */
    function propertyhive_get_property_thumbnail( $size = 'medium', $class = '', $placeholder_width = 0, $placeholder_height = 0  ) {
        global $post, $property;

        $photo_url = $property->get_main_photo_src( $size);

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
        ph_get_template( 'search/floor-area.php' );
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
        ph_get_template( 'search/price.php' );
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
    function propertyhive_result_count() {
        ph_get_template( 'search/result-count.php' );
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
    function propertyhive_catalog_ordering() {
        $orderby = isset( $_GET['orderby'] ) ? ph_clean( $_GET['orderby'] ) : apply_filters( 'propertyhive_default_search_results_orderby', get_option( 'propertyhive_default_search_results_orderby' ) );

        ph_get_template( 'search/orderby.php', array( 'orderby' => $orderby ) );
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
    function propertyhive_pagination() {
        ph_get_template( 'search/pagination.php' );
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

if ( ! function_exists( 'propertyhive_show_property_images' ) ) {

    /**
     * Output the property image before the single property summary.
     *
     * @access public
     * @subpackage  Property
     * @return void
     */
    function propertyhive_show_property_images() {
        ph_get_template( 'single-property/property-images.php' );
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
        ph_get_template( 'single-property/property-thumbnails.php' );
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
        ph_get_template( 'single-property/floor-area.php' );
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
        ph_get_template( 'single-property/price.php' );
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
        ph_get_template( 'single-property/meta.php' );
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

        $actions = array();

        $floorplan_ids = $property->get_floorplan_attachment_ids();
        if ( !empty( $floorplan_ids ) )
        {
            $floorplans_urls = array();
            foreach ($floorplan_ids as $floorplan_id)
            {
                $floorplans_urls[] = wp_get_attachment_url( $floorplan_id );
            }
            $actions[] = array(
                'href' => '',
                'label' => __( 'Floorplans', 'propertyhive' ),
                'class' => 'action-floorplans',
                'attributes' => array(
                    'data-floorplan-urls' => implode("|", $floorplans_urls)
                )
            );
        }

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

        $epc_ids = $property->get_epc_attachment_ids();
        if ( !empty( $epc_ids ) )
        {
            foreach ($epc_ids as $epc_id)
            {
                $actions[] = array(
                    'href' => wp_get_attachment_url( $epc_id ),
                    'label' => __( 'View EPC', 'propertyhive' ),
                    'class' => 'action-epc',
                    'attributes' => array(
                        'target' => '_blank'
                    )
                );
            }
        }

        $virtual_tour_urls = $property->get_virtual_tour_urls();
        if ( !empty( $virtual_tour_urls ) )
        {
            foreach ($virtual_tour_urls as $virtual_tour_url)
            {
                $actions[] = array(
                    'href' => $virtual_tour_url,
                    'label' => __( 'Virtual Tour', 'propertyhive' ),
                    'class' => 'action-virtual-tour',
                    'attributes' => array(
                        'target' => '_blank'
                    )
                );
            }
        }

        /*$actions[] = array(
            'href' => '',
            'label' => __( 'View on Map', 'propertyhive' ),
            'class' => 'action-map'
        );

        $actions[] = array(
            'href' => '',
            'label' => __( 'Street View', 'propertyhive' ),
            'class' => 'action-street-view'
        );*/

        $actions = apply_filters( 'propertyhive_single_property_actions', $actions );
        
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

            if ( $applicant_profiles && $applicant_profiles > 0 )
            {
                for ( $i = 0; $i < $applicant_profiles; ++$i )
                {
                    $pages['requirements'] = array(
                        'name' => __( 'Requirements', 'propertyhive' ),
                    );

                    break;  // At the moment we'll only allow them to manage the first set of requirements
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
                        'name' => __( 'Viewings', 'propertyhive' ),
                    );
                }
                wp_reset_postdata();
            }
        }
        if ( in_array('owner', $contact_types) )
        {
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
                            'name' => __( 'Viewings', 'propertyhive' ),
                        );
                    }
                    wp_reset_postdata();
                }
            }
        }
    }

    $pages = apply_filters( 'propertyhive_my_account_pages', $pages );

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

        $form_controls = ph_get_applicant_requirements_form_fields();
    
        $form_controls = apply_filters( 'propertyhive_applicant_requirements_form_fields', $form_controls );

        // Remove office as this is only required on initial sign up (at the moment anyway)
        if ( isset($form_controls['office_id']) )
        {
            unset($form_controls['office_id']);
        }

        ph_get_template( 'account/requirements.php', array( 'form_controls' => $form_controls ) );
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