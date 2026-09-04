<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<script type="text/template" id="fusion-builder-block-module-property-floorplans-preview-template">

	<#
		var titleHTML = '';

		if ( params.show_title && params.show_title == 'yes' )
		{
			titleHTML = '<h4>Floorplans</h4>';
		}
	#>

	<div class="fusion-module-property-floorplans-preview">
		{{{titleHTML}}}
		Floorplans here
	</div>

</script>
