<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$propertyhive_email_recipients = array();
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only email composition view; the matching controller verifies propertyhive-matching-applicants before including it, and send authorization remains in the controller.
$propertyhive_recipient_input = isset( $_POST['email_contact_applicant_profile_id'] ) && is_array( $_POST['email_contact_applicant_profile_id'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['email_contact_applicant_profile_id'] ) ) : array();
foreach ( $propertyhive_recipient_input as $propertyhive_recipient ) {
    if ( is_string( $propertyhive_recipient ) && preg_match( '/^\d+\|\d+$/', $propertyhive_recipient ) ) {
        $propertyhive_email_recipients[] = $propertyhive_recipient;
    }
}
?>
<h1>Emailing <?php echo esc_html($property->get_formatted_full_address()); ?> To <?php echo count( $propertyhive_email_recipients ); ?> Suitable Applicant<?php echo count( $propertyhive_email_recipients ) != 1 ? 's' : ''; ?></h1>

<table class="form-table">

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'To', 'propertyhive' )); ?></th>
        <td class="forminp">
        <?php
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
            foreach ( $propertyhive_email_recipients as $contact_applicant_profile_id )
            {
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $explode_contact_applicant_profile_id = explode("|", $contact_applicant_profile_id);

                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $contact_id = $explode_contact_applicant_profile_id[0];
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable in an admin view; PHPCS analyzes the view file standalone even though WordPress includes it inside a method/function scope.
                $applicant_profile_id = $explode_contact_applicant_profile_id[1];

                echo esc_html( get_the_title( $contact_id ) ) . ' (' . esc_html( get_post_meta( $contact_id, '_email_address', TRUE ) ) . ')<br>';
            }
        ?>
        <div style="margin-top:5px;"><a href="" class="show-cc">Show Cc</a> &nbsp;|&nbsp; <a href="" class="show-bcc">Show Bcc</a></div>
        </td>
    </tr>

    <tr valign="top" style="display:none" id="cc_email_address_row">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'Cc', 'propertyhive' )); ?></th>
        <td class="forminp">
            <input type="text" name="cc_email_address" value="" style="width:100%;">
        </td>
    </tr>

    <tr valign="top" style="display:none" id="bcc_email_address_row">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'Bcc', 'propertyhive' )); ?></th>
        <td class="forminp">
            <input type="text" name="bcc_email_address" value="" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'From Name', 'propertyhive' )); ?></th>
        <td class="forminp">
            <input type="text" name="from_name" value="<?php echo esc_attr(get_bloginfo('name')); ?>" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'From Email Address', 'propertyhive' )); ?></th>
        <td class="forminp">
            <input type="text" name="from_email_address" value="<?php echo esc_attr(trim($from_email_address)); ?>" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'Subject', 'propertyhive' )); ?></th>
        <td class="forminp">
            <input type="text" name="subject" value="<?php echo esc_attr($subject); ?>" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo esc_html(__( 'Email Body', 'propertyhive' )); ?></th>
        <td class="forminp">
            <textarea name="body" style="width:100%; height:300px;"><?php echo esc_textarea( $body ); ?></textarea>
        </td>
    </tr>

</table>

<script>

jQuery(document).ready(function()
{
    jQuery('a.show-cc').click(function(e)
    {
        e.preventDefault();

        jQuery('#cc_email_address_row').fadeIn('fast');
        jQuery('#cc_email_address_row input').focus();
    });

    jQuery('a.show-bcc').click(function(e)
    {
        e.preventDefault();

        jQuery('#bcc_email_address_row').fadeIn('fast');
        jQuery('#bcc_email_address_row input').focus();
    });
});

</script>