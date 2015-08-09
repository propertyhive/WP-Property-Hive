<?php
/**
 * Property Notes
 *
 * @author      BIOSTALL
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
        global $propertyhive, $post;

        $args = array(
            'approve'   => 'approve',
            'type'      => 'propertyhive_note'
        );
        
        $meta_query = array(
            'key' => '_related_to',
            'value' => 'property|' . $post->ID,
            'compare' => '='
        );

        //remove_filter( 'comments_clauses', array( 'PH_Comments', 'exclude_order_comments' ), 10, 1 );

        $notes = get_comments( $args );

        //add_filter( 'comments_clauses', array( 'PH_Comments', 'exclude_order_comments' ), 10, 1 );

        echo '<ul class="record_notes">';

        if ( $notes ) {
            foreach( $notes as $note ) {
                $note_classes = array( 'note' );

                ?>
                <li rel="<?php echo absint( $note->comment_ID ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
                    <div class="note_content">
                        <?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
                    </div>
                    <p class="meta">
                        <abbr class="exact-date" title="<?php echo $note->comment_date_gmt; ?> GMT"><?php printf( __( 'added %s ago', 'propertyhive' ), human_time_diff( strtotime( $note->comment_date_gmt ), current_time( 'timestamp', 1 ) ) ); ?></abbr>
                        <?php if ( $note->comment_author !== __( 'PropertyHive', 'propertyhive' ) ) printf( ' ' . __( 'by %s', 'propertyhive' ), $note->comment_author ); ?>
                        <a href="#" class="delete_note"><?php _e( 'Delete note', 'propertyhive' ); ?></a>
                    </p>
                </li>
                <?php
            }
        } else {
            echo '<li style="text-align:center;">' . __( 'There are no notes for this property yet.', 'propertyhive' ) . '</li>';
        }

        echo '</ul>';
        
        ?>
        <div class="add_note">
            <h4><?php _e( 'Add Note', 'propertyhive' ); ?></h4>
            <p>
                <textarea type="text" name="property_note" id="add_property_note" class="input-text" cols="20" rows="5"></textarea>
            </p>
            <p>
                <a href="#" class="add_note button"><?php _e( 'Add', 'propertyhive' ); ?></a>
            </p>
        </div>
        <?php 
    }
}