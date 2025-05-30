<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-property-epcs-preview-template">

	<#
		var titleHTML = '';

		if ( params.show_title && params.show_title == 'yes' )
		{
			titleHTML = '<h4>EPCs</h4>';
		}
	#>

	<div class="fusion-module-property-epcs-preview">
		{{{titleHTML}}}
		EPCs here
	</div>

</script>
