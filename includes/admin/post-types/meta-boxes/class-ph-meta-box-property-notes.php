<?php
/**
 * Property Notes
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Notes
 */
class PH_Meta_Box_Property_Notes {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $wpdb, $propertyhive, $post;

        $args = array(
            'post_id'   => $post->ID,
            'type'      => 'propertyhive_note'
        );

        $notes = get_comments( $args );

        // Get notes for all viewings
        
        // Get notes for all offers

        // Get notes for all sales

        echo '<ul class="record_notes" style="max-height:300px; overflow-y:auto">';

        if ( !empty($notes) ) 
        {

            $datetime_format = get_option('date_format')." \a\\t ".get_option('time_format');
            foreach( $notes as $note )
            {
                $note_classes = array( 'note' );

                $comment_content = unserialize($note->comment_content);

                $note_body = 'Unknown note type';
                switch ( $comment_content['note_type'] )
                {
                    case "mailout": 
                    { 
                        if ( isset($comment_content['method']) && $comment_content['method'] == 'email' && isset($comment_content['email_log_id']) )
                        {
                            $email_log = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "ph_email_log WHERE email_id = '" . $comment_content['email_log_id'] . "'" );
                            if ( null !== $email_log ) 
                            {
                                $property_ids = unserialize($email_log->property_ids);
                                $note_body = 'Included in email mailout to ' . get_the_title($email_log->contact_id) . '.';
                                $note_body .= ' <a href="' . wp_nonce_url( admin_url('?view_propertyhive_email=' . $comment_content['email_log_id'] . '&email_id=' . $comment_content['email_log_id'] ), 'view-email' ) . '" target="_blank">View Email Sent</a>';
                            }                                
                        }
                        break;
                    }
                    case "note":
                    {
                        $note_body = $comment_content['note'];
                        break;
                    }
                }
                ?>
                <li rel="<?php echo absint( $note->comment_ID ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
                    <div class="note_content">
                        <?php echo wpautop( wptexturize( wp_kses_post( $note_body ) ) ); ?>
                    </div>
                    <p class="meta">
                        <abbr class="exact-date" title="<?php echo $note->comment_date_gmt; ?> GMT">
                            <?php 
                                
                                $time_diff =  current_time( 'timestamp', 1 ) - strtotime( $note->comment_date_gmt );

                                if ($time_diff > 86400) {
                                    echo date($datetime_format, strtotime( $note->comment_date_gmt ) );
                                } else {
                                    printf( __( '%s ago', 'propertyhive' ), human_time_diff( strtotime( $note->comment_date_gmt ), current_time( 'timestamp', 1 ) ) );
                                }
                            ?>
                        </abbr>
                        <?php if ( $note->comment_author !== __( 'Property Hive', 'propertyhive' ) ) printf( ' ' . __( 'by %s', 'propertyhive' ), $note->comment_author ); ?>
                        <a href="#" class="delete_note"><?php _e( 'Delete', 'propertyhive' ); ?></a>
                    </p>
                </li>
                <?php
            }
        }

         echo '<li id="no_notes" style="text-align:center;' . ( (!empty($notes)) ? 'display:none;' : '' ) . '">' . __( 'There are no notes to display', 'propertyhive' ) . '</li>';

        echo '</ul>';
        
        ?>
        <div class="add_note">
            <h4><?php _e( 'Add Note', 'propertyhive' ); ?></h4>
            <p>
                <textarea type="text" name="note" id="add_note" class="input-text" cols="20" rows="6"></textarea>
            </p>
            <p>
                <a href="#" class="add_note button"><?php _e( 'Add', 'propertyhive' ); ?></a>
            </p>
        </div>
        <?php 
    }
}