<?php
/**
 * Property search form
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_property_search" class="property-search-form property-search-form-<?php echo esc_attr($id); ?> clear" action="<?php echo apply_filters( 'propertyhive_search_form_action', get_post_type_archive_link( 'property' ) ); ?>" method="get" role="form">

    <?php do_action( 'propertyhive_before_search_form_controls', $id, $form_controls ); ?>

    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <?php do_action( 'propertyhive_after_search_form_controls', $id, $form_controls ); ?>

    <input type="submit" value="<?php echo esc_attr( 'Search', 'propertyhive' ); ?>">

    <?php do_action( 'propertyhive_after_search_form_submit', $id, $form_controls ); ?>

</form>