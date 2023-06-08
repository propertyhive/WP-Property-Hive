<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$status = get_post_meta( $post_id, '_status', TRUE );
$feedback_status = get_post_meta( $post_id, '_feedback_status', TRUE );

echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_viewing_actions_meta_box">

<div class="options_group" style="padding-top:8px;">';

$show_cancelled_meta_boxes = false;
$show_feedback_meta_boxes = false;
$show_customise_confirmation_meta_boxes = false;

$actions = array();

if ( $status == 'pending' )
{
    $property_id = get_post_meta( $post_id, '_property_id', TRUE );
    $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
    $applicant_email_addresses = array();
    foreach ($applicant_contact_ids as $applicant_contact_id)
    {
        $applicant_email_addresses[] = get_post_meta( $applicant_contact_id, '_email_address', TRUE );
    }

    if ( in_array((int)$property_id, array(0, '')) || !is_array($applicant_contact_ids) || count($applicant_contact_ids) == 0 || count($applicant_email_addresses) == 0 )
    {

    }
    else
    {
        $applicant_booking_confirmation_sent_at = get_post_meta( $post_id, '_applicant_booking_confirmation_sent_at', TRUE );
        $owner_booking_confirmation_sent_at = get_post_meta( $post_id, '_owner_booking_confirmation_sent_at', TRUE );
        $attending_negotiator_booking_confirmation_sent_at = get_post_meta( $post_id, '_attending_negotiator_booking_confirmation_sent_at', TRUE );
        
        //Applicant
        if ( apply_filters( 'propertyhive_show_viewing_email_applicant_booking_confirmation', true ) === true )
        {
            if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
            {
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_booking_confirmation_customise" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? __('Email Applicant Booking Confirmation', 'propertyhive') : __('Re-Email Applicant Booking Confirmation', 'propertyhive') ) . '</a>';

                $show_customise_confirmation_meta_boxes = true;
            }
            else
            {
                $actions[] = '<a 
                        href="#action_panel_viewing_email_applicant_booking_confirmation" 
                        class="button viewing-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? __('Email Applicant Booking Confirmation', 'propertyhive') : __('Re-Email Applicant Booking Confirmation', 'propertyhive') ) . '</a>';
            }

            $actions[] = '<div id="viewing_applicant_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $applicant_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $applicant_booking_confirmation_sent_at != '' ) ? 'Previously sent to applicant on <span title="' . $applicant_booking_confirmation_sent_at . '">' . date("jS F", strtotime($applicant_booking_confirmation_sent_at)) : '' ) . '</span></div>';
        }

        // Owner/Landlord
        if ( apply_filters( 'propertyhive_show_viewing_email_owner_booking_confirmation', true ) === true )
        {
            $property_department = get_post_meta( $property_id, '_department', TRUE );
            $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
            $owner_or_landlord = ( $property_department == 'residential-lettings' ? 'Landlord' : 'Owner' );

            if ( is_array($owner_contact_ids) && count($owner_contact_ids) > 0) {

                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_booking_confirmation_customise" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? __('Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') : __('Re-Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') ) . '</a>';

                    $show_customise_confirmation_meta_boxes = true;
                }
                else
                {
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_owner_booking_confirmation" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $owner_booking_confirmation_sent_at == '' ) ? __('Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') : __('Re-Email ' . $owner_or_landlord . ' Booking Confirmation', 'propertyhive') ) . '</a>';
                }
                $actions[] = '<div id="viewing_owner_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $owner_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $owner_booking_confirmation_sent_at != '' ) ? 'Previously sent to ' . strtolower($owner_or_landlord) . ' on <span title="' . $owner_booking_confirmation_sent_at . '">' . date("jS F", strtotime($owner_booking_confirmation_sent_at)) : '' ) . '</span></div>';
            }
        }

        // Attending Negotiators
        if ( apply_filters( 'propertyhive_show_viewing_email_attending_negotiator_booking_confirmation', true ) === true )
        {
            $attending_negotiators = get_post_meta( $property_id, '_negotiator_id' );
            if ( !empty($attending_negotiators) )
            {
                if ( get_option( 'propertyhive_customise_confirmation_emails', '' ) == 'yes' )
                {
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_attending_negotiator_booking_confirmation_customise" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $attending_negotiator_booking_confirmation_sent_at == '' ) ? __('Email Negotiator Booking Confirmation', 'propertyhive') : __('Re-Email Negotiator Booking Confirmation', 'propertyhive') ) . '</a>';

                    $show_customise_confirmation_meta_boxes = true;
                }
                else
                {
                    $actions[] = '<a 
                            href="#action_panel_viewing_email_attending_negotiator_booking_confirmation" 
                            class="button viewing-action"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . ( ( $attending_negotiator_booking_confirmation_sent_at == '' ) ? __('Email Negotiator Booking Confirmation', 'propertyhive') : __('Re-Email Negotiator Booking Confirmation', 'propertyhive') ) . '</a>';
                }

                $actions[] = '<div id="viewing_attending_negotiator_confirmation_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . ( ( $attending_negotiator_booking_confirmation_sent_at == '' ) ? 'display:none' : '' ) . '">' . ( ( $attending_negotiator_booking_confirmation_sent_at != '' ) ? 'Previously sent to attending negotiators on <span title="' . $attending_negotiator_booking_confirmation_sent_at . '">' . date("jS F", strtotime($attending_negotiator_booking_confirmation_sent_at)) : '' ) . '</span></div>';
            }
        }

        $actions[] = '<hr>';
    }

    $actions[] = '<a 
            href="#action_panel_viewing_carried_out" 
            class="button button-success viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . __('Viewing Carried Out', 'propertyhive') . '</a>';
    $actions[] = '<a 
            href="#action_panel_viewing_cancelled" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . __('Viewing Cancelled', 'propertyhive') . '</a>';
    $actions[] = '<a
            href="#action_panel_viewing_no_show"
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center"
        >' . __('Applicant No Show', 'propertyhive') . '</a>';

    $show_cancelled_meta_boxes = true;
}

if ( $status == 'carried_out' )
{
    if ( $feedback_status == '' )
    {
        $actions[] = '<a 
            href="#action_panel_viewing_interested" 
            class="button button-success viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Applicant Interested', 'propertyhive') ) . '</a>';

        $actions[] = '<a 
            href="#action_panel_viewing_not_interested" 
            class="button button-danger viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Applicant Not Interested', 'propertyhive') ) . '</a>';

        $actions[] = '<a 
            href="#action_panel_viewing_feedback_not_required" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Feedback Not Required', 'propertyhive') ) . '</a>';

        $show_feedback_meta_boxes = true;
    }

    if ( $feedback_status == 'interested' )
    {
        $actions[] = '<a 
            href="' . trim(admin_url(), '/') . '/post-new.php?post_type=viewing&applicant_contact_id=' . implode('|', get_post_meta( $post_id, '_applicant_contact_id' )) . '&property_id=' . get_post_meta( $post_id, '_property_id', TRUE ) . '&viewing_id=' . $post_id .'" 
            class="button button-success"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Book Second Viewing', 'propertyhive') ) . '</a>';

        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
        {
            $property_id = get_post_meta( $post_id, '_property_id', TRUE );
            if ( get_post_meta( $property_id, '_department', TRUE ) == 'residential-sales' )
            {
                // See if an offer has this viewing id associated with it
                $offer_id = get_post_meta( $post_id, '_offer_id', TRUE );
                if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
                {
                    $offer_id = '';
                }

                if ( $offer_id != '' )
                {
                    $actions[] = '<a 
                            href="' . get_edit_post_link( $offer_id, '' ) . '" 
                            class="button"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . wp_kses_post( __('View Offer', 'propertyhive') ) . '</a>';
                }
                else
                {
                    $actions[] = '<a 
                            href="' . wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ), '1', 'create_offer' ) . '" 
                            class="button button-success"
                            style="width:100%; margin-bottom:7px; text-align:center" 
                        >' . wp_kses_post( __('Record Offer', 'propertyhive') ) . '</a>';
                }
            }
        }
    }

    if ( get_post_meta( $post_id, '_feedback_passed_on', TRUE ) != 'yes' && ( $feedback_status == 'interested' || $feedback_status == 'not_interested' ) )
    {
        $actions[] = '<a 
            href="#action_panel_viewing_revert_feedback_passed_on" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Feedback Passed On To Owner', 'propertyhive') ) . '</a>';
    }

    if ( $feedback_status == 'interested' || $feedback_status == 'not_interested' || $feedback_status == 'not_required' )
    {
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
        $offer_id = get_post_meta( $post_id, '_offer_id', TRUE );
        if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
        {
            $offer_id = '';
        }

        if ( $offer_id != '' )
        {
            $actions[] = '<a 
                    href="' . get_edit_post_link( $offer_id, '' ) . '" 
                    class="button"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                >' . wp_kses_post( __('View Offer', 'propertyhive') ) . '</a>';
        }
    }
}

if ( ( $status == 'carried_out' && $feedback_status == '' ) || in_array( $status, array('cancelled', 'no_show') ) )
{
    $actions[] = '<a 
            href="#action_panel_viewing_revert_pending" 
            class="button viewing-action"
            style="width:100%; margin-bottom:7px; text-align:center" 
        >' . wp_kses_post( __('Revert To Pending', 'propertyhive') ) . '</a>';
}

$actions = apply_filters( 'propertyhive_admin_viewing_actions', $actions, $post_id );
$actions = apply_filters( 'propertyhive_admin_post_actions', $actions, $post_id );

if ( !empty($actions) )
{
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

        <a class="button action-cancel" style="width:100%;" href="#">' . __( 'Back To Actions', 'propertyhive' ) . '</a>

    </div>

</div>';

do_action( 'propertyhive_admin_viewing_action_options', $post_id );
do_action( 'propertyhive_admin_post_action_options', $post_id );

if ( $show_customise_confirmation_meta_boxes )
{
    $subject = get_option( 'propertyhive_viewing_owner_booking_confirmation_email_subject', '' );
    $body = get_option( 'propertyhive_viewing_owner_booking_confirmation_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_owner_booking_confirmation_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_owner_confirmation_email_subject">' . __( 'Subject', 'propertyhive' ) . '</label>
                
                <input id="_owner_confirmation_email_subject" name="_owner_confirmation_email_subject" style="width:100%;" value="' . $subject . '">

            </div>

            <div class="form-field">

                <label for="_owner_confirmation_email_body">' . __( 'Body', 'propertyhive' ) . '</label>
                
                <textarea id="_owner_confirmation_email_body" name="_owner_confirmation_email_body" style="width:100%; height:100px;">' . $body . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
            <a class="button button-primary owner-booking-confirmation-action-submit" href="#">' . __( 'Send', 'propertyhive' ) . '</a>

        </div>

    </div>';

    $subject = get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_subject', '' );
    $body = get_option( 'propertyhive_viewing_applicant_booking_confirmation_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_applicant_booking_confirmation_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_applicant_confirmation_email_subject">' . __( 'Subject', 'propertyhive' ) . '</label>
                
                <input id="_applicant_confirmation_email_subject" name="_applicant_confirmation_email_subject" style="width:100%;" value="' . $subject . '">

            </div>

            <div class="form-field">

                <label for="_applicant_confirmation_email_body">' . __( 'Body', 'propertyhive' ) . '</label>
                
                <textarea id="_applicant_confirmation_email_body" name="_applicant_confirmation_email_body" style="width:100%; height:100px;">' . $body . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
            <a class="button button-primary applicant-booking-confirmation-action-submit" href="#">' . __( 'Send', 'propertyhive' ) . '</a>

        </div>

    </div>';

    $subject = get_option( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_subject', '' );
    $body = get_option( 'propertyhive_viewing_attending_negotiator_booking_confirmation_email_body', '' );

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_email_attending_negotiator_booking_confirmation_customise" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_attending_negotiator_confirmation_email_subject">' . __( 'Subject', 'propertyhive' ) . '</label>
                
                <input id="_attending_negotiator_confirmation_email_subject" name="_attending_negotiator_confirmation_email_subject" style="width:100%;" value="' . $subject . '">

            </div>

            <div class="form-field">

                <label for="_attending_negotiator_confirmation_email_body">' . __( 'Body', 'propertyhive' ) . '</label>
                
                <textarea id="_attending_negotiator_confirmation_email_body" name="_attending_negotiator_confirmation_email_body" style="width:100%; height:100px;">' . $body . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
            <a class="button button-primary attending-negotiator-booking-confirmation-action-submit" href="#">' . __( 'Send', 'propertyhive' ) . '</a>

        </div>

    </div>';
}

if ( $show_cancelled_meta_boxes )
{
    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_cancelled" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_viewing_cancelled_reason">' . __( 'Reason Cancelled', 'propertyhive' ) . '</label>
                
                <textarea id="_cancelled_reason" name="_cancelled_reason" style="width:100%;">' . get_post_meta( $post_id, '_cancelled_reason', TRUE ) . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
            <a class="button button-primary cancelled-reason-action-submit" href="#">' . __( 'Save', 'propertyhive' ) . '</a>

        </div>

    </div>';
}

if ( $show_feedback_meta_boxes )
{
    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_interested" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_viewing_interested_feedback">' . __( 'Applicant Feedback', 'propertyhive' ) . '</label>
                
                <textarea id="_interested_feedback" name="_interested_feedback" style="width:100%;">' . get_post_meta( $post_id, '_feedback', TRUE ) . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
            <a class="button button-primary interested-feedback-action-submit" href="#">' . wp_kses_post( __( 'Save Feedback', 'propertyhive' ) ) . '</a>

        </div>

    </div>';

    echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_viewing_not_interested" style="display:none;">

        <div class="options_group" style="padding-top:8px;">

            <div class="form-field">

                <label for="_viewing_not_interested_feedback">' . __( 'Applicant Feedback', 'propertyhive' ) . '</label>
                
                <textarea id="_not_interested_feedback" name="_not_interested_feedback" style="width:100%;">' . get_post_meta( $post_id, '_feedback', TRUE ) . '</textarea>

            </div>

            <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
            <a class="button button-primary not-interested-feedback-action-submit" href="#">' . wp_kses_post( __( 'Save Feedback', 'propertyhive' ) ) . '</a>

        </div>

    </div>';
}