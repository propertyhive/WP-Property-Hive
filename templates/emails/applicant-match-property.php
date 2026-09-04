<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>

<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="margin-bottom:10px;">
	<tr>
		<td width="25%"><?php 
			$image = $property->get_main_photo_src();
			if ($image !== FALSE)
			{
		?>
		<a href="<?php echo esc_url(get_the_permalink( $property->id )); ?>"><img src="<?php echo esc_url($image); ?>" style="max-width:100%" alt="<?php echo esc_html(get_the_title( $property->id )); ?>"></a>
		<?php
			}
		?></td>
		<td>
			<h2><a href="<?php echo esc_url(get_the_permalink( $property->id )); ?>"><?php echo esc_html(get_the_title( $property->id )); ?></a></h2>
			<p>
				<strong><?php echo wp_kses_post($property->get_formatted_price()); ?></strong><?php
					if ( $property->price_qualifier != '' )
			        {
			        	echo ' <span class="price-qualifier">' . esc_html($property->price_qualifier) . '</span>';
			       	}
				?> | <?php 
					if ( $property->department != 'commercial' && ph_get_custom_department_based_on( $property->department ) != 'commercial' )
					{
						echo esc_html($property->bedrooms) . ' bed ';
					}
					else
					{
						echo esc_html($property->get_formatted_floor_area()) . ' | ';
					}
					echo esc_html($property->get_property_type()); ?> | <?php echo esc_html($property->get_availability()); ?></p>
			<p><?php echo wp_kses_post($property->post_excerpt); ?></p>
		</td>
	</tr>
</table>