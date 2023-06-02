<table width="100%" cellpadding="5" cellspacing="0">
	<tr>
		<td width="20%" valign="top"><?php
				$image = $property->get_main_photo_src();
				if ( $image !== false )
				{
					echo '<a href="' . get_permalink() . '"><img src="' . $image . '" alt="' . get_the_title() . '"></a>';
				}
		?></td>
		<td valign="top" class="text">
			<p style="margin-bottom:8px !important;"><strong><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></strong></p>
			<p style="margin-bottom:8px !important; font-size:14px;"><strong><?php echo $property->get_formatted_price(); ?></strong>
			<?php
				if ( $property->price_qualifier != '' )
		        {
		        	echo ' <span class="price-qualifier">' . $property->price_qualifier . '</span>';
		       	}
		    ?>
			</p>
			<p style="margin-bottom:8px !important; font-size:14px;">
			<?php
				if ( $property->department != 'commercial' && ph_get_custom_department_based_on( $property->department ) != 'commercial' )
				{
					echo $property->bedrooms . ' bed ';
				}
				else
				{
					echo $property->get_formatted_floor_area() . ' | ';
				}
				echo $property->property_type . ' ' . $property->availability;
			?>
			</p>
			<?php
				if ( strip_tags($property->post_excerpt) != '' )
				{
					echo '<p style="margin-bottom:0 !important; font-size:14px;">' . substr(strip_tags($property->post_excerpt), 0, 300);
					if ( strlen(strip_tags($property->post_excerpt)) > 300 ) { echo '...'; } 
					echo '</p>';
				}
			?>
		</td>
	</tr>
</table><br>