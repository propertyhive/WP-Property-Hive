<table width="100%" cellpadding="5" cellspacing="0">
	<tr>
		<td width="20%" valign="top"><?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local variable; the template is included through a helper/function scope, so PrefixAllGlobals misclassifies the file when checked standalone.
				$image = $property->get_main_photo_src();
				if ( $image !== false )
				{
					echo '<a href="' . esc_url( get_permalink() ) . '"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( get_the_title() ) . '" style="max-width:100%"></a>';
				}
		?></td>
		<td valign="top" class="text">
			<p style="margin-bottom:8px !important;"><strong><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></strong></p>
			<p style="margin-bottom:8px !important; font-size:14px;"><strong><?php echo wp_kses_post( $property->get_formatted_price() ); ?></strong>
			<?php
				if ( $property->price_qualifier != '' )
		        {
                    echo ' <span class="price-qualifier">' . esc_html( $property->price_qualifier ) . '</span>';
		       	}
		    ?>
			</p>
			<p style="margin-bottom:8px !important; font-size:14px;">
			<?php
				if ( $property->department != 'commercial' && ph_get_custom_department_based_on( $property->department ) != 'commercial' )
				{
					echo esc_html( $property->bedrooms ) . ' bed ';
				}
				else
				{
					echo wp_kses_post( $property->get_formatted_floor_area() ) . ' | ';
				}
				echo esc_html( $property->property_type ) . ' ' . esc_html( $property->availability );
			?>
			</p>
			<?php
				if ( wp_strip_all_tags($property->post_excerpt) != '' )
				{
					echo '<p style="margin-bottom:0 !important; font-size:14px;">' . esc_html( substr(wp_strip_all_tags($property->post_excerpt), 0, 300) );
					if ( strlen(wp_strip_all_tags($property->post_excerpt)) > 300 ) { echo '...'; }
					echo '</p>';
				}
			?>
		</td>
	</tr>
</table><br>