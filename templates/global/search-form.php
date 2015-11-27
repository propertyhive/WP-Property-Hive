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

<form name="ph_property_search" class="property-search-form clear" action="<?php echo get_post_type_archive_link( 'property' ); ?>" method="get" role="form">

    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <input type="submit" value="<?php _e( 'Search', 'propertyhive' ); ?>">

</form>