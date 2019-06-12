<?php
$current_user = wp_get_current_user();
?>
<h1>Emailing <?php echo $property->get_formatted_full_address(); ?> To <?php echo count($_POST['email_contact_applicant_profile_id']); ?> Suitable Applicant<?php echo count($_POST['email_contact_applicant_profile_id']) != 1 ? 's' : ''; ?></h1>

<table class="form-table">

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo __( 'To', 'propertyhive' ); ?></th>
        <td class="forminp">
        <?php
            foreach ( $_POST['email_contact_applicant_profile_id'] as $contact_applicant_profile_id )
            {
                $explode_contact_applicant_profile_id = explode("|", $contact_applicant_profile_id);

                $contact_id = $explode_contact_applicant_profile_id[0];
                $applicant_profile_id = $explode_contact_applicant_profile_id[1];

                echo get_the_title($contact_id) . ' (' . get_post_meta( $contact_id, '_email_address', TRUE ) . ')<br>';
            }
        ?>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo __( 'From Name', 'propertyhive' ); ?></th>
        <td class="forminp">
            <input type="text" name="from_name" value="<?php

                echo get_bloginfo('name');

            ?>" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo __( 'From Email Address', 'propertyhive' ); ?></th>
        <td class="forminp">
            <input type="text" name="from_email_address" value="<?php

                if ( trim($current_user->user_email) != '' )
                {
                    echo $current_user->user_email;
                }

            ?>" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo __( 'Subject', 'propertyhive' ); ?></th>
        <td class="forminp">
            <input type="text" name="subject" value="<?php echo $subject; ?>" style="width:100%;">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo __( 'Email Body', 'propertyhive' ); ?></th>
        <td class="forminp">
            <textarea name="body" style="width:100%; height:300px;"><?php echo $body; ?></textarea>
        </td>
    </tr>

</table>