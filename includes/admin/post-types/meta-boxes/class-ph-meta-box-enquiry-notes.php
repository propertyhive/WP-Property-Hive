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

        echo '<ul class="record_notes" style="max-height:300px; overflow-y:auto">';

        if ( !empty($notes) ) 
        {
            $datetime_format = get_option('date_format')." \a\\t ".get_option('time_format');

            foreach( $notes as $note ) 
            {
                $note_classes = array( 'note' );

                $comment_content = unserialize($note->comment_content);

                $note_classes[] = 'note-type-' . $comment_content['note_type'];

                $note_body = 'Unknown note type';
                switch ( $comment_content['note_type'] )
                {
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
                                    echo date( $datetime_format, strtotime( $note->comment_date_gmt ) );
                                } else {
                                    printf( __( '%s ago', 'propertyhive' ), human_time_diff( strtotime( $note->comment_date_gmt ), current_time( 'timestamp', 1 ) ) );
                                }
                            ?>
                        </abbr>
                        <?php if ( $note->comment_author !== __( 'Property Hive', 'propertyhive' ) ) printf( ' ' . __( 'by %s', 'propertyhive' ), $note->comment_author ); ?>
                        <?php if ($comment_content['note_type'] == 'note') { ?><a href="#" class="delete_note"><?php _e( 'Delete', 'propertyhive' ); ?></a><?php } ?>
                        <?php
                            if ( $post->ID != $note->comment_post_ID )
                            {
                        ?>
                        <br>
                        <?php echo __( 'Note originally entered on', 'properthive' ); ?> <a href="<?php echo get_edit_post_link($note->comment_post_ID); ?>" style="color:inherit;"><?php echo __( ucfirst(get_post_type($note->comment_post_ID)), 'propertyhive' ); ?></a>
                        <?php
                            }
                        ?>
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