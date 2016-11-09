<?php
$current_user = wp_get_current_user();
?>
<div class="wrap propertyhive">

	<h1>Emailing <?php echo count($_POST['email_property_id']); ?> Suitable Properties To <?php echo get_the_title($contact_id); ?></h1>

    <div id="poststuff">

    <?php /*<div style="float:left; width:50%;">*/ ?>

    	<form method="post" id="mainform" action="" enctype="multipart/form-data">

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

            <p class="submit">

            	<input name="save" class="button-primary" type="submit" value="<?php echo __( 'Send Email', 'propertyhive' ); ?>" />
                <input name="preview" id="preview_email" class="button" type="button" value="<?php echo __( 'Preview Email', 'propertyhive' ); ?>" />

            	<input type="hidden" name="step" value="two" />
                <input type="hidden" name="email_property_id" value="<?php echo implode(",", $_POST['email_property_id']); ?>" />
            	<?php wp_nonce_field( 'propertyhive-matching-properties' ); ?>

            </p>

            <p>
            <?php echo __( 'When sending out lots of emails we recommend using <a href="https://en-gb.wordpress.org/plugins/tags/smtp" target="_blank">a plugin</a> to send them out using SMTP. Your web developer or hosting company should be able to advise on this.', 'propertyhive' );
            ?>
            </p>

    	</form>

    <?php /*</div>

    <div style="float:right; width:50%;">

        <iframe name="previewFrame" src="" height="500" width="100%" style="border:1px solid #CCC;" frameborder="0" scrolling="auto">Your browser does not support iFrames</iframe>

    </div>*/ ?>

    </div>

</div>

<script>

    jQuery(document).ready(function()
    {
        jQuery('#preview_email').click(function(e)
        {
            e.preventDefault();

            showPreview();
        });
    });

    function showPreview()
    {
        jQuery('#mainform').attr('target', '_blank');
        jQuery('#mainform').attr('action', '<?php echo admin_url( '?preview_propertyhive_email=true&contact_id=' . $_GET['contact_id'] . '&applicant_profile=' . $_GET['applicant_profile'] ); ?>');

        jQuery('#mainform').submit();
        jQuery('#mainform').attr('target', '_self');
        jQuery('#mainform').attr('action', '');
    }

</script>