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
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy public global class PH_Meta_Box_Viewing_Notes; preserving the existing PH_* class name is required for plugin and extension compatibility.
class PH_Meta_Box_Viewing_Notes {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $wpdb, $propertyhive, $post;

        $section = 'viewing';

        echo '<ul class="subsubsub notes-filter" style="float:none; padding-left:10px;">';
            
            $notes_filters = array(
                '' =>  esc_html__( 'All', 'propertyhive' ),
                'note' =>  esc_html__( 'Note', 'propertyhive' ),
                'action' =>  esc_html__( 'System Change', 'propertyhive' ),
            );

            $notes_filters = apply_filters( 'propertyhive_notes_filters', $notes_filters, $post );
            $notes_filters = apply_filters( 'propertyhive_viewing_notes_filters', $notes_filters, $post );

            $i = 0;
            foreach ( $notes_filters as $class => $label )
            {
                echo '<li><a href="" data-section="' . esc_attr($section) . '" data-filter-class="' . ( $class == '' ? '*' : 'note-type-' . esc_attr($class) ) . '"' . ( $class == '' ? ' class="current"' : '' ) . '>';
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built-in labels are escaped before the trusted PHP propertyhive_notes_filters and propertyhive_viewing_notes_filters hooks, which intentionally permit extension HTML.
                echo $label;
                echo '</a>';
                if ( $i < count($notes_filters) - 1 ) { echo ' |&nbsp; '; }
                echo '</li>';
                ++$i;
            }

        echo '</ul>';

        echo '<div class="propertyhive-notes-container" id="propertyhive_' . esc_attr($section) . '_notes_container">' . esc_html(__( 'Loading', 'propertyhive' )) . '...</div>';
    }
}