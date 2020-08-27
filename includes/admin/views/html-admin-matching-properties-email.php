<h1>Emailing <?php echo count($_POST['email_property_id']); ?> Suitable Properties To <?php echo get_the_title($contact_id); ?></h1>

<table class="form-table">

    <tr valign="top">
        <th scope="row" class="titledesc"><?php echo __( 'To', 'propertyhive' ); ?></th>
        <td class="forminp">
            <input type="text" name="to_email_address" value="<?php echo get_post_meta( $contact_id, '_email_address', TRUE ); ?>" style="width:100%;">
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
            <input type="text" name="from_email_address" value="<?php echo trim($from_email_address); ?>" style="width:100%;">
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