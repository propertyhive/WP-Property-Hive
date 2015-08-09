<?php
/**
 * Single Property Meta
 *
 * @author 		BIOSTALL
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="property_meta">

	<?php do_action( 'propertyhive_property_meta_start' ); ?>
    
    <ul>
    
    	<?php if ( $property->reference_number != '' ) : ?><li class="ref"><?php _e( 'Ref:', 'propertyhive' ); echo ' ' . $property->reference_number; ?></li><?php endif; ?>
        
        <li class="type"><?php _e( 'Type:', 'propertyhive' ); echo ' ' . $property->get_property_type(); ?></li>
        
        <?php if ($property->bedrooms > 0) { ?><li class="bedrooms"><?php _e( 'Bedrooms:', 'propertyhive' ); echo ' ' . $property->bedrooms; ?></li><?php } ?>
        
        <?php if ($property->bathrooms > 0) { ?><li class="bathrooms"><?php _e( 'Bathrooms:', 'propertyhive' ); echo ' ' . $property->bathrooms; ?></li><?php } ?>
        
        <?php if ($property->reception_rooms > 0) { ?><li class="receptions"><?php _e( 'Reception Rooms:', 'propertyhive' ); echo ' ' . $property->reception_rooms; ?></li><?php } ?>
        
        <?php
            switch ($property->department)
            {
                case "residential-sales": {
        ?>
        
        <?php
                    break;
                }
                case "residential-lettings": {
        ?>
        
        <?php if ($property->deposit > 0) { ?><li class="deposit"><?php _e( 'Deposit:', 'propertyhive' ); echo ' ' . $property->get_formatted_deposit(); ?></li><?php } ?>
        
        <?php if ($property->available_date > 0) { ?><li class="available"><?php _e( 'Available:', 'propertyhive' ); echo ' ' . $property->get_available_date(); ?></li><?php } ?>
        
        
        <?php
                    break;
                }
            }
        ?>

    </ul>

	<?php do_action( 'propertyhive_property_meta_end' ); ?>
    
</div>