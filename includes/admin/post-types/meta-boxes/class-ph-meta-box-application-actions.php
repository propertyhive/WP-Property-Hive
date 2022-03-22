<?php
/**
 * Application Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Application_Actions
 */
class PH_Meta_Box_Application_Actions {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $wpdb, $thepostid;

        echo '<div id="propertyhive_application_actions_meta_box_container">

            Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
    $('#propertyhive_application_actions_meta_box_container').on('click', 'a.application-action', function(e)
    {
        e.preventDefault();

        var this_href = $(this).attr('href');

        $(this).attr('disabled', 'disabled');

        if ( this_href == '#action_panel_application_accepted' )
        {
            var data = {
                action:         'propertyhive_application_accepted',
                application_id:	<?php echo $post->ID; ?>,
                security:       '<?php echo wp_create_nonce( 'application-actions' ); ?>',
            };
            jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
            {
                redraw_application_actions();

                $('#application_details_status_span').text('Offer Accepted');
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_application_declined' )
        {
            var data = {
                action:         'propertyhive_application_declined',
                application_id: <?php echo $post->ID; ?>,
                security:       '<?php echo wp_create_nonce( 'application-actions' ); ?>',
            };
            jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
            {
                redraw_application_actions();

                $('#application_details_status_span').text('Offer Declined');
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_application_withdrawn' )
        {
            var data = {
                action:         'propertyhive_application_withdrawn',
                application_id:    	<?php echo $post->ID; ?>,
                security:       '<?php echo wp_create_nonce( 'application-actions' ); ?>',
            };
            jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
            {
                redraw_application_actions();

                $('#application_details_status_span').text('Withdrawn');
            }, 'json');
            return;
        }

        if ( this_href == '#action_panel_application_revert_pending' )
        {
            var data = {
                action:         'propertyhive_application_revert_pending',
                application_id: <?php echo $post->ID; ?>,
                security:       '<?php echo wp_create_nonce( 'application-actions' ); ?>',
            };
            jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
            {
                redraw_application_actions();

                $('#application_details_status_span').text('Offer Pending');
            }, 'json');
            return;
        }

        $('#propertyhive_application_actions_meta_box').stop().fadeOut(300, function()
        {
            $(this_href).stop().fadeIn(300, function()
            {
                
            });
        });
    });

    $('#propertyhive_application_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
    {
        e.preventDefault();

        redraw_application_actions();
    });
});

jQuery(window).on('load', function($)
{
    redraw_application_actions();
});

function redraw_application_actions()
{
    jQuery('#propertyhive_application_actions_meta_box_container').html('Loading...');

    var data = {
        action:         'propertyhive_get_application_actions',
        application_id: <?php echo $post->ID; ?>,
        security:       '<?php echo wp_create_nonce( 'application-actions' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
    {
        jQuery('#propertyhive_application_actions_meta_box_container').html(response);
    }, 'html');
}

</script>
<?php
    }
}