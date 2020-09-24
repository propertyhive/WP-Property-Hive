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
            'type'      => 'propertyhive_note',
            'meta_query' => array(
                array(
                    'key' => 'related_to',
                    'value' => '"' . $post->ID . '"',
                    'compare' => 'LIKE',
                ),
            )
        );

        $notes = get_comments( $args );

        echo '<ul class="subsubsub notes-filter" style="float:none; padding-left:10px;">';
            
            $notes_filters = array(
                '' =>  __( 'All', 'propertyhive' ),
                'mailout' =>  __( 'Mailout', 'propertyhive' ),
                'note' =>  __( 'Note', 'propertyhive' ),
                'action' =>  __( 'System Change', 'propertyhive' ),
            );

            $notes_filters = apply_filters( 'propertyhive_notes_filters', $notes_filters, $post );
            $notes_filters = apply_filters( 'propertyhive_property_notes_filters', $notes_filters, $post );

            $i = 0;
            foreach ( $notes_filters as $class => $label )
            {
                echo '<li><a href="" data-filter-class="' . ( $class == '' ? '*' : 'note-type-' . $class ) . '">' . $label . '</a>';
                if ( $i < count($notes_filters) - 1 ) { echo ' |&nbsp; '; }
                echo '</li>';
                ++$i;
            }

        echo '</ul>';

        $pinned_notes = array();
        $unpinned_notes = array();

        if ( !empty($notes) ) 
        {
            $datetime_format = get_option('date_format')." \a\\t ".get_option('time_format');

            foreach( $notes as $note ) 
            {
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
                                $note_body = 'Included in email mailout to ' . get_the_title($email_log->contact_id) . '.';
                                $note_body .= ' <a href="' . wp_nonce_url( admin_url('?view_propertyhive_email=' . $comment_content['email_log_id'] . '&email_id=' . $comment_content['email_log_id'] ), 'view-email' ) . '" target="_blank">View Email Sent</a>';
                            }                                
                        }
                        break;
                    }
                    case "action":
                    {
                        $note_body = $comment_content['action'];

                        switch ( $comment_content['action'] )
                        {
                            case "property_price_change":
                            {
                                $note_body .= '<br>From: ' . $comment_content['original_value'] . '<br>To: ' . $comment_content['new_value'];
                                break;
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
                $note_content = array(
                    'id' => $note->comment_ID,
                    'post_id' => $note->comment_post_ID,
                    'type' => $comment_content['note_type'],
                    'author' => $note->comment_author,
                    'body' => $note_body,
                    'timestamp' => strtotime($note->comment_date),
                    'internal' => true,
                    'pinned' => ( isset($comment_content['pinned']) && $comment_content['pinned'] == '1' ) ? '1' : '0',
                );

                if ( $note_content['pinned'] == '1' )
                {
                    $pinned_notes[] = $note_content;
                }
                else
                {
                    $unpinned_notes[] = $note_content;
                }
            }
        }

        $note_output = array_merge($pinned_notes, $unpinned_notes);

        $note_output = apply_filters( 'propertyhive_notes', $note_output, $post );
	    $note_output = apply_filters( 'propertyhive_property_notes', $note_output, $post );
        
        include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );
    }
}