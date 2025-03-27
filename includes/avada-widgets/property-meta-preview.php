<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-property-meta-preview-template">

	<#
		var text_color        = params.text_color,
			text_transform    = '' !== params.text_transform ? params.text_transform : '',
			styleTag          = '';

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

	<div class="fusion-module-property-meta-preview">
		<ul style="{{{ styleTag }}}">
			<li><span>Reference:</span> 001-ABC</li>
			<li><span>Availability:</span> For Sale</li>
			<li><span>Property Type:</span> Detached House</li>
			<li><span>Bedrooms:</span> 3</li>
			<li><span>Bathrooms:</span> 1</li>
		</ul>
	</div>

</script>
