<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-property-embedded-virtual-tours-preview-template">

	<#
		var titleHTML = '';

		if ( params.show_title && params.show_title == 'yes' )
		{
			titleHTML = '<h4>Virtual Tours</h4>';
		}
	#>

	<div class="fusion-module-property-embedded-virtual-tours-preview">
		{{{titleHTML}}}
		<iframe src="https://www.youtube.com/embed/CbOQqvQDrVQ?si=8z8timInJgyT305L" height="315" width="560" allowfullscreen frameborder="0" allow="fullscreen"></iframe>
	</div>

</script>
