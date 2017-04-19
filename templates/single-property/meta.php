<?php
/**
 * Single Property Meta
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="property_meta">

	<?php do_action( 'propertyhive_property_meta_start' ); ?>
    
    <ul>

        <?php do_action( 'propertyhive_property_meta_list_start' ); ?>
    
    	<?php if ( $property->reference_number != '' ) : ?><li class="ref"><?php _e( 'Ref', 'propertyhive' ); echo ': ' . $property->reference_number; ?></li><?php endif; ?>
        
        <?php if ( $property->property_type != '' ) { ?><li class="type"><?php _e( 'Type', 'propertyhive' ); echo ': ' . $property->property_type; ?></li><?php } ?>

        <?php if ( $property->availability != '' ) { ?><li class="availability"><?php _e( 'Availability', 'propertyhive' ); echo ': ' . $property->availability; ?></li><?php } ?>
        
        <?php if ( $property->department != 'commercial' ) { ?>

        <?php if ( $property->bedrooms > 0 ) { ?><li class="bedrooms"><?php _e( 'Bedrooms', 'propertyhive' ); echo ': ' . $property->bedrooms; ?></li><?php } ?>
        
        <?php if ( $property->bathrooms > 0 ) { ?><li class="bathrooms"><?php _e( 'Bathrooms', 'propertyhive' ); echo ': ' . $property->bathrooms; ?></li><?php } ?>
        
        <?php if ( $property->reception_rooms > 0 ) { ?><li class="receptions"><?php _e( 'Reception Rooms', 'propertyhive' ); echo ': ' . $property->reception_rooms; ?></li><?php } ?>
        
        <?php
            switch ( $property->department )
            {
                case "residential-sales": 
                {
        ?>
        
        <?php if ( $property->tenure != '' ) { ?><li class="tenure"><?php _e( 'Tenure', 'propertyhive' ); echo ': ' . $property->tenure; ?></li><?php } ?>

        <?php
                    break;
                }
                case "residential-lettings": 
                {
        ?>

        <?php if ( $property->furnished != '' ) { ?><li class="furnished"><?php _e( 'Furnished', 'propertyhive' ); echo ': ' . $property->furnished; ?></li><?php } ?>
        
        <?php if ( $property->deposit > 0 ) { ?><li class="deposit"><?php _e( 'Deposit', 'propertyhive' ); echo ': ' . $property->get_formatted_deposit(); ?></li><?php } ?>
        
        <?php if ( $property->available_date != '') { ?><li class="available"><?php _e( 'Available', 'propertyhive' ); echo ': ' . $property->get_available_date(); ?></li><?php } ?>
        
        
        <?php
                    break;
                }
            }
        ?>

        <?php } // end if residential ?>

        <?php do_action( 'propertyhive_property_meta_list_end' ); ?>

    </ul>

	<?php do_action( 'propertyhive_property_meta_end' ); ?>
    
</div>