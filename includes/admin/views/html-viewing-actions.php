<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$status = get_post_meta( $post_id, '_status', TRUE );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$feedback_status = get_post_meta( $post_id, '_feedback_status', TRUE );

echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_viewing_actions_meta_box">

<div class="options_group" style="padding-top:8px;">';

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$show_cancelled_meta_boxes = false;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$show_feedback_meta_boxes = false;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$show_customise_confirmation_meta_boxes = false;
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$show_customise_cancellation_notification_meta_boxes = false;

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$actions = array();

if ( $status == 'pending' )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $property_id = get_post_meta( $post_id, '_property_id', TRUE );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $applicant_email_addresses = array();
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    foreach ($applicant_contact_ids as $applicant_contact_id)
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $applicant_email_addresses[] = get_post_meta( $applicant_contact_id, '_email_address', TRUE );
    }

    if ( in_array((int)$property_id, array(0, '')) || !is_array($applicant_contact_ids) || count($applicant_contact_ids) == 0 || count($applicant_email_addresses) == 0 )
    {

    }
    else
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $applicant_booking_confirmation_sent_at = get_post_meta( $post_id, '_applicant_booking_confirmation_sent_at', TRUE );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $owner_booking_confirmation_sent_at = get_post_meta( $post_id, '_owner_booking_confirmation_sent_at', TRUE );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $attending_negotiator_booking_confirmation_sent_at = get_post_meta( $post_id, '_attending_negotiator_booking_confirmation_sent_at', TRUE );
        
        // Applicant
        if ( apply_filters( 'propertyhive_show_viewing_email_applicant_booking_confirmation', true ) === true )
        {
            if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_booking_confirmation_customise" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? esc_html__('Email Applicant Booking Confirmation', 'propertyhive') : esc_html__('Re-Email Applicant Booking Confirmation', 'propertyhive') ) . '</a>';

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $show_customise_confirmation_meta_boxes = true;
            }
            else
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_booking_confirmation" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? esc_html__('Email Applicant Booking Confirmation', 'propertyhive') : esc_html__('Re-Email Applicant Booking Confirmation', 'propertyhive') ) . '</a>';
            }

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $actions[] = '<div id="viewing_applicant_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $applicant_booking_confirmation_sent_at != '' ) ? 'Previously sent to applicant on <span title="' . esc_attr( $applicant_booking_confirmation_sent_at ) . '">' . esc_html( gmdate("jS F", strtotime($applicant_booking_confirmation_sent_at)) ) : '' ) . '</span></div>';
        }

        // Owner/Landlord
        if ( apply_filters( 'propertyhive_show_viewing_email_owner_booking_confirmation', true ) === true )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $property_department = get_post_meta( $property_id, '_department', TRUE );
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $owner_or_landlord = ( $property_department == 'residential-lettings' ? 'Landlord' : 'Owner' );

            if ( is_array($owner_contact_ids) && count($owner_contact_ids) > 0) {

                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_booking_confirmation_customise" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Email Landlord Booking Confirmation', 'propertyhive' ) : esc_html__( 'Email Owner Booking Confirmation', 'propertyhive' ) ) : ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Re-Email Landlord Booking Confirmation', 'propertyhive' ) : esc_html__( 'Re-Email Owner Booking Confirmation', 'propertyhive' ) ) ) . '</a>';

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $show_customise_confirmation_meta_boxes = true;
                }
                else
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_booking_confirmation" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Email Landlord Booking Confirmation', 'propertyhive' ) : esc_html__( 'Email Owner Booking Confirmation', 'propertyhive' ) ) : ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Re-Email Landlord Booking Confirmation', 'propertyhive' ) : esc_html__( 'Re-Email Owner Booking Confirmation', 'propertyhive' ) ) ) . '</a>';
                }
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<div id="viewing_owner_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $owner_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $owner_booking_confirmation_sent_at != '' ) ? 'Previously sent to ' . esc_html( strtolower($owner_or_landlord) ) . ' on <span title="' . esc_attr( $owner_booking_confirmation_sent_at ) . '">' . esc_html( gmdate("jS F", strtotime($owner_booking_confirmation_sent_at)) ) : '' ) . '</span></div>';
            }
        }

        // Attending Negotiators
        if ( apply_filters( 'propertyhive_show_viewing_email_attending_negotiator_booking_confirmation', true ) === true )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $attending_negotiators = get_post_meta( $property_id, '_negotiator_id' );
            if ( !empty($attending_negotiators) )
            {
                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_attending_negotiator_booking_confirmation_customise" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $attending_negotiator_booking_confirmation_sent_at == '' ) ? esc_html__('Email Negotiator Booking Confirmation', 'propertyhive') : esc_html__('Re-Email Negotiator Booking Confirmation', 'propertyhive') ) . '</a>';

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $show_customise_confirmation_meta_boxes = true;
                }
                else
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_attending_negotiator_booking_confirmation" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $attending_negotiator_booking_confirmation_sent_at == '' ) ? esc_html__('Email Negotiator Booking Confirmation', 'propertyhive') : esc_html__('Re-Email Negotiator Booking Confirmation', 'propertyhive') ) . '</a>';
                }

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<div id="viewing_attending_negotiator_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $attending_negotiator_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $attending_negotiator_booking_confirmation_sent_at != '' ) ? 'Previously sent to attending negotiators on <span title="' . esc_attr( $attending_negotiator_booking_confirmation_sent_at ) . '">' . esc_html( gmdate("jS F", strtotime($attending_negotiator_booking_confirmation_sent_at)) ) : '' ) . '</span></div>';
            }
        }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<hr>';
    }

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $actions[] = '<a 
            href="#action_panel_viewing_carried_out" 
            class="button button-success viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . esc_html__('Viewing Carried Out', 'propertyhive') . '</a>';
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $actions[] = '<a 
            href="#action_panel_viewing_cancelled" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . esc_html__('Viewing Cancelled', 'propertyhive') . '</a>';
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $actions[] = '<a
            href="#action_panel_viewing_no_show"
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center"
        >' . esc_html__('Applicant No Show', 'propertyhive') . '</a>';

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $show_cancelled_meta_boxes = true;
}

if ( $status == 'carried_out' )
{
    if ( $feedback_status == '' )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<a 
            href="#action_panel_viewing_interested" 
            class="button button-success viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Applicant Interested', 'propertyhive') ) . '</a>';

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<a 
            href="#action_panel_viewing_not_interested" 
            class="button button-danger viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Applicant Not Interested', 'propertyhive') ) . '</a>';

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<a 
            href="#action_panel_viewing_feedback_not_required" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Feedback Not Required', 'propertyhive') ) . '</a>';

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $show_feedback_meta_boxes = true;
    }

    if ( $feedback_status == 'interested' )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<a 
            href="' . esc_url( add_query_arg( array( 'post_type' => 'viewing', 'applicant_contact_id' => implode( '|', array_map( 'absint', get_post_meta( $post_id, '_applicant_contact_id' ) ) ), 'property_id' => absint( get_post_meta( $post_id, '_property_id', TRUE ) ), 'viewing_id' => absint( $post_id ) ), admin_url( 'post-new.php' ) ) ) .'"
            class="button button-success"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Book Second Viewing', 'propertyhive') ) . '</a>';

        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $property_id = get_post_meta( $post_id, '_property_id', TRUE );
            if ( get_post_meta( $property_id, '_department', TRUE ) == 'residential-sales' )
            {
                // See if an offer has this viewing id associated with it
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $offer_id = get_post_meta( $post_id, '_offer_id', TRUE );
                if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $offer_id = '';
                }

                if ( $offer_id != '' )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="' . esc_url( get_edit_post_link( $offer_id, '' ) ) . '"
                            class="button"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . wp_kses_post( __('View Offer', 'propertyhive') ) . '</a>';
                }
                else
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="' . esc_url( wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), 'propertyhive-create_offer-' . $post_id, 'create_offer' ) ) . '"
                            class="button button-success"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . wp_kses_post( __('Record Offer', 'propertyhive') ) . '</a>';
                }
            }
        }
    }

    if ( get_post_meta( $post_id, '_feedback_passed_on', TRUE ) != 'yes' && ( $feedback_status == 'interested' || $feedback_status == 'not_interested' ) )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<a 
            href="#action_panel_viewing_revert_feedback_passed_on" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Feedback Passed On To Owner', 'propertyhive') ) . '</a>';
    }

    if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' || $feedback_status == 'not_required' )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<a 
            href="#action_panel_viewing_revert_feedback_pending" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Revert To Feedback Pending', 'propertyhive') ) . '</a>';
    }
}

if ( $status == 'offer_made' )
{
    if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $offer_id = get_post_meta( $post_id, '_offer_id', TRUE );
        if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $offer_id = '';
        }

        if ( $offer_id != '' )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $actions[] = '<a 
                    href="' . esc_url( get_edit_post_link( $offer_id, '' ) ) . '"
                    class="button"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('View Offer', 'propertyhive') ) . '</a>';
        }
    }
}

if ( $status == 'cancelled' )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $property_id = get_post_meta( $post_id, '_property_id', TRUE );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $applicant_email_addresses = array();
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    foreach ($applicant_contact_ids as $applicant_contact_id)
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $applicant_email_addresses[] = get_post_meta( $applicant_contact_id, '_email_address', TRUE );
    }

    if ( in_array((int)$property_id, array(0, '')) || !is_array($applicant_contact_ids) || count($applicant_contact_ids) == 0 || count($applicant_email_addresses) == 0 )
    {

    }
    else
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $applicant_cancellation_notification_sent_at = get_post_meta( $post_id, '_applicant_cancellation_notification_sent_at', TRUE );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $owner_cancellation_notification_sent_at = get_post_meta( $post_id, '_owner_cancellation_notification_sent_at', TRUE );
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $attending_negotiator_cancellation_notification_sent_at = get_post_meta( $post_id, '_attending_negotiator_cancellation_notification_sent_at', TRUE );
        
        // Applicant
        if ( apply_filters( 'propertyhive_show_viewing_email_applicant_cancellation_notification', true ) === true )
        {
            if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_cancellation_notification_customise" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_cancellation_notification_sent_at == '' ) ? esc_html__('Email Applicant Cancellation Notification', 'propertyhive') : esc_html__('Re-Email Applicant Cancellation Notification', 'propertyhive') ) . '</a>';

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $show_customise_cancellation_notification_meta_boxes = true;
            }
            else
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_cancellation_notification" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_cancellation_notification_sent_at == '' ) ? esc_html__('Email Applicant Cancellation Notification', 'propertyhive') : esc_html__('Re-Email Applicant Cancellation Notification', 'propertyhive') ) . '</a>';
            }

            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $actions[] = '<div id="viewing_applicant_cancellation_notification_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $applicant_cancellation_notification_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $applicant_cancellation_notification_sent_at != '' ) ? 'Previously sent to applicant on <span title="' . esc_attr( $applicant_cancellation_notification_sent_at ) . '">' . esc_html( gmdate("jS F", strtotime($applicant_cancellation_notification_sent_at)) ) : '' ) . '</span></div>';
        }

        // Owner/Landlord
        if ( apply_filters( 'propertyhive_show_viewing_email_owner_cancellation_notification', true ) === true )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $property_department = get_post_meta( $property_id, '_department', TRUE );
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $owner_or_landlord = ( $property_department == 'residential-lettings' ? 'Landlord' : 'Owner' );

            if ( is_array($owner_contact_ids) && count($owner_contact_ids) > 0) {

                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_cancellation_notification_customise" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_cancellation_notification_sent_at == '' ) ? ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Email Landlord Cancellation Notification', 'propertyhive' ) : esc_html__( 'Email Owner Cancellation Notification', 'propertyhive' ) ) : ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Re-Email Landlord Cancellation Notification', 'propertyhive' ) : esc_html__( 'Re-Email Owner Cancellation Notification', 'propertyhive' ) ) ) . '</a>';

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $show_customise_cancellation_notification_meta_boxes = true;
                }
                else
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_cancellation_notification" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_cancellation_notification_sent_at == '' ) ? ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Email Landlord Cancellation Notification', 'propertyhive' ) : esc_html__( 'Email Owner Cancellation Notification', 'propertyhive' ) ) : ( $owner_or_landlord === 'Landlord' ? esc_html__( 'Re-Email Landlord Cancellation Notification', 'propertyhive' ) : esc_html__( 'Re-Email Owner Cancellation Notification', 'propertyhive' ) ) ) . '</a>';
                }
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<div id="viewing_owner_cancellation_notification_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $owner_cancellation_notification_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $owner_cancellation_notification_sent_at != '' ) ? 'Previously sent to ' . esc_html( strtolower($owner_or_landlord) ) . ' on <span title="' . esc_attr( $owner_cancellation_notification_sent_at ) . '">' . esc_html( gmdate("jS F", strtotime($owner_cancellation_notification_sent_at)) ) : '' ) . '</span></div>';
            }
        }

        // Attending Negotiators
        if ( apply_filters( 'propertyhive_show_viewing_email_attending_negotiator_cancellation_notification', true ) === true )
        {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            $attending_negotiators = get_post_meta( $property_id, '_negotiator_id' );
            if ( !empty($attending_negotiators) )
            {
                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_attending_negotiator_cancellation_notification_customise" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $attending_negotiator_cancellation_notification_sent_at == '' ) ? esc_html__('Email Negotiator Cancellation Notification', 'propertyhive') : esc_html__('Re-Email Negotiator Cancellation Notification', 'propertyhive') ) . '</a>';

                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $show_customise_cancellation_notification_meta_boxes = true;
                }
                else
                {
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_attending_negotiator_cancellation_notification" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $attending_negotiator_cancellation_notification_sent_at == '' ) ? esc_html__('Email Negotiator Cancellation Notification', 'propertyhive') : esc_html__('Re-Email Negotiator Cancellation Notification', 'propertyhive') ) . '</a>';
                }

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $actions[] = '<div id="viewing_attending_negotiator_cancellation_notification_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $attending_negotiator_cancellation_notification_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $attending_negotiator_cancellation_notification_sent_at != '' ) ? 'Previously sent to attending negotiators on <span title="' . esc_attr( $attending_negotiator_cancellation_notification_sent_at ) . '">' . esc_html( gmdate("jS F", strtotime($attending_negotiator_cancellation_notification_sent_at)) ) : '' ) . '</span></div>';
            }
        }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
        $actions[] = '<hr>';
    }
}

if ( ( $status == 'carried_out' && $feedback_status == '' ) || in_array( $status, array('cancelled', 'no_show') ) )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $actions[] = '<a 
            href="#action_panel_viewing_revert_pending" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Revert To Pending', 'propertyhive') ) . '</a>';
}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$actions = apply_filters( 'propertyhive_admin_viewing_actions', $actions, $post_id );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
$actions = apply_filters( 'propertyhive_admin_post_actions', $actions, $post_id );

if ( !empty($actions) )
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core action URLs, attributes and labels are escaped when assembled above; PHP extension filters intentionally supply action HTML.
    echo implode("", $actions);
}
else
{
    echo '<div style="text-align:center">' . wp_kses_post( __( 'No actions to display', 'propertyhive' ) ) . '</div>';
}

echo '</div>

</div>';

// Success action panel
echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
             
    <div class="options_group" style="padding-top:8px;">

        <div id="success_actions"></div>

        <a class="button action-cancel" style="width:100%;" href="#">' . esc_html(__( 'Back To Actions', 'propertyhive' )) . '</a>

    </div>

</div>';

do_action( 'propertyhive_admin_viewing_action_options', $post_id );
do_action( 'propertyhive_admin_post_action_options', $post_id );

if ( $show_customise_confirmation_meta_boxes )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $subject = get_option( 'propertyhive_viewing_owner_booking_confirmation_email_subject', '' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $body = get_option( 'propertyhive_viewing_owner_booking_confirmation_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_owner_booking_confirmation_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_owner_confirmation_email_subject">' . esc_html__( 'Subject', 'propertyhive' ) . '</label>
                
                <input id="_owner_confirmation_email_subject" name="_owner_confirmation_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

            </div>

            <div class="form-field">

                <label for="_owner_confirmation_email_body">' . esc_html__( 'Body', 'propertyhive' ) . '</label>
                
                <textarea id="_owner_confirmation_email_body" name="_owner_confirmation_email_body" style="width:100%; height:100px;">' . esc_textarea( $body ) . '</textarea>

            </div>

            <div class="form-field">

                <label for="_owner_confirmation_email_attachment">' . esc_html__( 'Attach File(s)', 'propertyhive' ) . '</label>
                
                <input type="file" id="_owner_confirmation_email_attachment" name="_owner_confirmation_email_attachment" style="width:100%;" multiple>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary owner-booking-confirmation-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

        </div>

    </div>';

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $subject = get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_subject', '' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $body = get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_applicant_booking_confirmation_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_applicant_confirmation_email_subject">' . esc_html(__( 'Subject', 'propertyhive' )) . '</label>
                
                <input id="_applicant_confirmation_email_subject" name="_applicant_confirmation_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

            </div>

            <div class="form-field">

                <label for="_applicant_confirmation_email_body">' . esc_html(__( 'Body', 'propertyhive' )) . '</label>
                
                <textarea id="_applicant_confirmation_email_body" name="_applicant_confirmation_email_body" style="width:100%; height:100px;">' . esc_textarea( $body ) . '</textarea>

            </div>

            <div class="form-field">

                <label for="_applicant_confirmation_email_attachment">' . esc_html(__( 'Attach File(s)', 'propertyhive' )) . '</label>
                
                <input type="file" id="_applicant_confirmation_email_attachment" name="_applicant_confirmation_email_attachment" style="width:100%;" multiple>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary applicant-booking-confirmation-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

        </div>

    </div>';

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $subject = get_option( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_subject', '' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $body = get_option( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_attending_negotiator_booking_confirmation_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_attending_negotiator_confirmation_email_subject">' . esc_html(__( 'Subject', 'propertyhive' )) . '</label>
                
                <input id="_attending_negotiator_confirmation_email_subject" name="_attending_negotiator_confirmation_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

            </div>

            <div class="form-field">

                <label for="_attending_negotiator_confirmation_email_body">' . esc_html(__( 'Body', 'propertyhive' )) . '</label>
                
                <textarea id="_attending_negotiator_confirmation_email_body" name="_attending_negotiator_confirmation_email_body" style="width:100%; height:100px;">' . esc_textarea( $body ) . '</textarea>

            </div>

            <div class="form-field">

                <label for="_attending_negotiator_confirmation_email_attachment">' . esc_html(__( 'Attach File(s)', 'propertyhive' )) . '</label>
                
                <input type="file" id="_attending_negotiator_confirmation_email_attachment" name="_attending_negotiator_confirmation_email_attachment" style="width:100%;" multiple>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary attending-negotiator-booking-confirmation-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

        </div>

    </div>';
}

if ( $show_customise_cancellation_notification_meta_boxes )
{
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $subject = get_option( 'propertyhive_viewing_owner_cancellation_notification_email_subject', '' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $body = get_option( 'propertyhive_viewing_owner_cancellation_notification_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_owner_cancellation_notification_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_owner_cancellation_notification_email_subject">' . esc_html__( 'Subject', 'propertyhive' ) . '</label>
                
                <input id="_owner_cancellation_notification_email_subject" name="_owner_cancellation_notification_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

            </div>

            <div class="form-field">

                <label for="_owner_cancellation_notification_email_body">' . esc_html__( 'Body', 'propertyhive' ) . '</label>
                
                <textarea id="_owner_cancellation_notification_email_body" name="_owner_cancellation_notification_email_body" style="width:100%; height:100px;">' . esc_textarea( $body ) . '</textarea>

            </div>

            <div class="form-field">

                <label for="_owner_cancellation_notification_email_attachment">' . esc_html__( 'Attach File(s)', 'propertyhive' ) . '</label>
                
                <input type="file" id="_owner_cancellation_notification_email_attachment" name="_owner_cancellation_notification_email_attachment" style="width:100%;" multiple>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary owner-cancellation-notification-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

        </div>

    </div>';

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $subject = get_option( 'propertyhive_viewing_applicant_cancellation_notification_email_subject', '' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $body = get_option( 'propertyhive_viewing_applicant_cancellation_notification_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_applicant_cancellation_notification_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_applicant_cancellation_notification_email_subject">' . esc_html(__( 'Subject', 'propertyhive' )) . '</label>
                
                <input id="_applicant_cancellation_notification_email_subject" name="_applicant_cancellation_notification_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

            </div>

            <div class="form-field">

                <label for="_applicant_cancellation_notification_email_body">' . esc_html(__( 'Body', 'propertyhive' )) . '</label>
                
                <textarea id="_applicant_cancellation_notification_email_body" name="_applicant_cancellation_notification_email_body" style="width:100%; height:100px;">' . esc_textarea( $body ) . '</textarea>

            </div>

            <div class="form-field">

                <label for="_applicant_cancellation_notification_email_attachment">' . esc_html(__( 'Attach File(s)', 'propertyhive' )) . '</label>
                
                <input type="file" id="_applicant_cancellation_notification_email_attachment" name="_applicant_cancellation_notification_email_attachment" style="width:100%;" multiple>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary applicant-cancellation-notification-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

        </div>

    </div>';

    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $subject = get_option( 'propertyhive_viewing_attending_negotiator_cancellation_notification_email_subject', '' );
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
    $body = get_option( 'propertyhive_viewing_attending_negotiator_cancellation_notification_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_attending_negotiator_cancellation_notification_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_attending_negotiator_cancellation_notification_email_subject">' . esc_html(__( 'Subject', 'propertyhive' )) . '</label>
                
                <input id="_attending_negotiator_cancellation_notification_email_subject" name="_attending_negotiator_cancellation_notification_email_subject" style="width:100%;" value="' . esc_attr($subject) . '">

            </div>

            <div class="form-field">

                <label for="_attending_negotiator_cancellation_notification_email_body">' . esc_html(__( 'Body', 'propertyhive' )) . '</label>
                
                <textarea id="_attending_negotiator_cancellation_notification_email_body" name="_attending_negotiator_cancellation_notification_email_body" style="width:100%; height:100px;">' . esc_textarea( $body ) . '</textarea>

            </div>

            <div class="form-field">

                <label for="_attending_negotiator_cancellation_notification_email_attachment">' . esc_html(__( 'Attach File(s)', 'propertyhive' )) . '</label>
                
                <input type="file" id="_attending_negotiator_cancellation_notification_email_attachment" name="_attending_negotiator_cancellation_notification_email_attachment" style="width:100%;" multiple>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary attending-negotiator-cancellation-notification-action-submit" href="#">' . esc_html(__( 'Send', 'propertyhive' )) . '</a>

        </div>

    </div>';
}

if ( $show_cancelled_meta_boxes )
{
    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_cancelled" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_cancelled_reason">' . esc_html(__( 'Reason Cancelled', 'propertyhive' )) . '</label>
                
                <textarea id="_cancelled_reason" name="_cancelled_reason" style="width:100%;">' . esc_textarea( get_post_meta( $post_id, '_cancelled_reason', TRUE ) ) . '</textarea>

            </div>

            <div class="form-field">

                <label><input type="checkbox" id="_cancelled_reason_public" name="_cancelled_reason_public" value="yes"> ' . esc_html(__( 'Make Reason Public?', 'propertyhive' )) . '</label>
                <em><small>' . esc_html( __( 'If checked, the reason for cancellation entered will be visible to applicants and owners/landlords in their login area and any cancellation emails sent.', 'propertyhive' ) ) . '</small></em>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary cancelled-reason-action-submit" href="#">' . esc_html(__( 'Save', 'propertyhive' )) . '</a>

        </div>

    </div>';
}

if ( $show_feedback_meta_boxes )
{
    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_interested" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_viewing_interested_feedback">' . esc_html(__( 'Applicant Feedback', 'propertyhive' )) . '</label>
                
                <textarea id="_interested_feedback" name="_interested_feedback" style="width:100%;">' . esc_textarea( get_post_meta( $post_id, '_feedback', TRUE ) ) . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary interested-feedback-action-submit" href="#">' . wp_kses_post( __( 'Save Feedback', 'propertyhive' ) ) . '</a>

        </div>

    </div>';

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_not_interested" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_viewing_not_interested_feedback">' . esc_html(__( 'Applicant Feedback', 'propertyhive' )) . '</label>
                
                <textarea id="_not_interested_feedback" name="_not_interested_feedback" style="width:100%;">' . esc_textarea( get_post_meta( $post_id, '_feedback', TRUE ) ) . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . esc_html(__( 'Cancel', 'propertyhive' )) . '</a>
            <a class="button button-primary not-interested-feedback-action-submit" href="#">' . wp_kses_post( __( 'Save Feedback', 'propertyhive' ) ) . '</a>

        </div>

    </div>';
}