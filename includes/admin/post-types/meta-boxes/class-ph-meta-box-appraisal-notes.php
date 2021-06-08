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

        $section = 'appraisal';

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
                echo '<li><a href="" data-section="' . $section . '" data-filter-class="' . ( $class == '' ? '*' : 'note-type-' . $class ) . '"' . ( $class == '' ? ' class="current"' : '' ) . '>' . $label . '</a>';
                if ( $i < count($notes_filters) - 1 ) { echo ' |&nbsp; '; }
                echo '</li>';
                ++$i;
            }

        echo '</ul>';

        echo '<div class="propertyhive-notes-container" id="propertyhive_' . $section . '_notes_container">' . __( 'Loading', 'propertyhive' ) . '...</div>';
    }
}