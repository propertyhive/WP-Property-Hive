<?php
/**
 * Viewing Notes
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Viewing_Notes
 */
class PH_Meta_Box_Viewing_Notes {

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
                'note' =>  __( 'Note', 'propertyhive' ),
                'action' =>  __( 'System Change', 'propertyhive' ),
            );

            $notes_filters = apply_filters( 'propertyhive_notes_filters', $notes_filters, $post );
            $notes_filters = apply_filters( 'propertyhive_viewing_notes_filters', $notes_filters, $post );

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
                    case "note":
                    {
                        $note_body = $comment_content['note'];
                        break;
                    }
                    case "action":
                    {
                        $note_body = $comment_content['action'];
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
        $note_output = apply_filters( 'propertyhive_viewing_notes', $note_output, $post );
        
        include( PH()->plugin_path() . '/includes/admin/views/html-display-notes.php' );
    }
}