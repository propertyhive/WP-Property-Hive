/* global propertyhive_admin */

/**
 * Property Hive Admin JS
 */
jQuery( function ( $ ) {

	$( document.body )

		.on( 'init_tooltips', function() {
			var tiptip_args = {
				'attribute': 'data-tip',
				'fadeIn': 50,
				'fadeOut': 50,
				'delay': 200
			};

			$( '.help_tip' ).tipTip( tiptip_args );
		});

	// Tooltips
	$( document.body ).trigger( 'init_tooltips' );

});