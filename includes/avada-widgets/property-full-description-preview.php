<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-property-full-description-preview-template">

	<#
		var text_color        = params.text_color,
			text_transform    = '' !== params.text_transform ? params.text_transform : '',
			styleTag          = '',
			titleHTML 		  = '';

		if ( params.show_title && params.show_title == 'yes' )
		{
			titleHTML = '<h4>Full Description</h4>';
		}

		if ( 'none' !== text_transform ) {
			styleTag += 'text-transform: ' + text_transform + ';';
		}

		if ( text_color && ( -1 !== text_color.indexOf( 'var(--' ) ) ) {
			text_color = getComputedStyle( document.documentElement ).getPropertyValue( text_color.replace( 'var(', '' ).replace( ')', '' ) );
		}

		if ( text_color && ( -1 !== text_color.replace( /\s/g, '' ).indexOf( 'rgba(255,255,255' ) || '#ffffff' === text_color ) ) {
			text_color = '#dddddd';
		}

		if ( params.content_align && '' !== params.content_align ) {
			styleTag += 'text-align: ' + params.content_align + ';';
		}

		if ( params.sep_color && '' !== params.sep_color ) {
			styleTag += 'border-color: ' + params.sep_color + ';';
		}

		if ( text_color ) {
			styleTag += 'color: ' + text_color + ';';
		}
	#>

	<div class="fusion-module-property-full-description-preview">
		{{{titleHTML}}}
		<p class="room">Nestled in a quiet, family-friendly neighborhood, this beautifully updated 3-bedroom, 2-bathroom home blends charm with modern convenience. With a spacious backyard, natural light throughout, and thoughtful design touches, it's move-in ready and waiting for you.</p>
		<p class="room"><strong>Living Room (15' x 18')</strong><br>The bright and airy living room features large windows, hardwood floors, and a cozy fireplace—perfect for relaxing evenings or entertaining guests.</p>
		<p class="room"><strong>Kitchen (12' x 14')</strong><br>The updated kitchen boasts sleek countertops, stainless steel appliances, and ample cabinet space, making it as functional as it is stylish.</p>
	</div>

</script>
