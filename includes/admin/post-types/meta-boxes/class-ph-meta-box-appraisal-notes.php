<?php
/**
 * Appraisal Notes
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appraisal_Notes
 */
class PH_Meta_Box_Appraisal_Notes {

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
            $notes_filters = apply_filters( 'propertyhive_appraisal_notes_filters', $notes_filters, $post );

            $i = 0;
            foreach ( $notes_filters as $class => $label )
            {
                echo '<li><a href="" data-filter-class="' . ( $class == '' ? '*' : 'note-type-' . $class ) . '">' . $label . '</a>';
                if ( $i < count($notes_filters) - 1 ) { echo ' |&nbsp; '; }
                echo '</li>';
                ++$i;
            }

        echo '</ul>';

        echo '<ul class="record_notes" style="max-height:300px; overflow-y:auto">';

        $note_output = array();

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

                $note_output[] = array(
                    'id' => $note->comment_ID,
                    'post_id' => $note->comment_post_ID,
                    'type' => $comment_content['note_type'],
                    'author' => $note->comment_author,
                    'body' => $note_body,
                    'timestamp' => strtotime($note->comment_date),
                    'internal' => true
                );
            }
        }

        $note_output = apply_filters( 'propertyhive_notes', $note_output, $post );
        $note_output = apply_filters( 'propertyhive_appraisal_notes', $note_output, $post );

        if ( !empty($note_output) )
        {
            // order by date desc. Older PHP versions don't support array_column so just can't order for them
            if ( function_exists('array_column') )
            {
                $timestamp = array_column($note_output, 'timestamp');

                array_multisort($timestamp, SORT_DESC, $note_output);
            }

            foreach ( $note_output as $note )
            {
                $note_classes = array( 'note' );

                $note_classes[] = 'note-type-' . $note['type'];
?>
                <li rel="<?php echo absint( $note['id'] ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
                    <div class="note_content">
                        <?php echo wp_kses_post( $note['body'] ); ?>
                    </div>
                    <p class="meta">
                        <abbr class="exact-date" title="<?php echo date("Y-m-d H:i:s", $note['timestamp']); ?>">
                            <?php 
                                
                                $time_diff =  current_time( 'timestamp', 1 ) - $note['timestamp'];

                                if ($time_diff > 86400) {
                                    echo date( $datetime_format, $note['timestamp'] );
                                } else {
                                    printf( __( '%s ago', 'propertyhive' ), human_time_diff( $note['timestamp'], current_time( 'timestamp', 1 ) ) );
                                }
                            ?>
                        </abbr>
                        <?php if ( $note['author'] !== __( 'Property Hive', 'propertyhive' ) && $note['author'] != '' ) printf( ' ' . __( 'by %s', 'propertyhive' ), $note['author'] ); ?>
                        <?php if ( $note['type'] == 'note' ) { ?><a href="#" class="delete_note"><?php _e( 'Delete', 'propertyhive' ); ?></a><?php } ?>
                        <?php
                            if ( $post->ID != $note['post_id'] )
                            {
                        ?>
                        <br>
                        <?php echo __( 'Note originally entered on', 'propertyhive' ); ?> <a href="<?php echo get_edit_post_link($note['post_id']); ?>" style="color:inherit;"><?php echo __( ucfirst(get_post_type($note['post_id'])), 'propertyhive' ); ?></a>
                        <?php
                            }
                        ?>
                    </p>
                </li>
<?php
            }
        }

        echo '<li id="no_notes" style="text-align:center;' . ( (!empty($note_output)) ? 'display:none;' : '' ) . '">' . __( 'There are no notes to display', 'propertyhive' ) . '</li>';

        echo '</ul>';
        
        include( PH()->plugin_path() . '/includes/admin/views/html-add-note.php' );
    }
}