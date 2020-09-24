<?php
/**
 * Enquiry Notes
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Enquiryy_Notes
 */
class PH_Meta_Box_Enquiry_Notes {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $propertyhive, $post;

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

        include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );
    }
}