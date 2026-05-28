/* global propertyhive_onboarding */

/**
 * Property Hive onboarding wizard.
 */
jQuery( function( $ ) {
	'use strict';

	var config = window.propertyhive_onboarding || {};
	var steps = [ 'intro', 'departments', 'country', 'office', 'usage', 'demo-data', 'complete' ];
	var $wizard = $( '.ph-onboarding' );
	var currentStep = $wizard.data( 'current-step' ) || 'intro';
	var demoImported = $wizard.data( 'demo-imported' ) === 'yes';
	var demoImporting = false;

	function getStepIndex( step ) {
		var index = $.inArray( step, steps );
		return index < 0 ? 0 : index;
	}

	function selectedValues( name ) {
		return $( 'input[name="' + name + '[]"]:checked' ).map( function() {
			return $( this ).val();
		} ).get();
	}

	function setMessage( message, type ) {
		var $message = $( '[data-message]' );
		$message.removeClass( 'is-error is-success' );

		if ( ! message ) {
			$message.text( '' ).hide();
			return;
		}

		$message.addClass( type === 'error' ? 'is-error' : 'is-success' ).text( message ).show();
	}

	function track( eventName, step ) {
		if ( ! config.ajax_url || ! config.nonce ) {
			return;
		}

		$.post( config.ajax_url, {
			action: 'propertyhive_onboarding_track',
			security: config.nonce,
			event: eventName,
			step: step || currentStep
		} );
	}

	function updateUsageLinks() {
		var usage = selectedValues( 'usage' );
		$( '[data-usage-link]' ).each( function() {
			var $link = $( this );
			$link.toggle( $.inArray( $link.data( 'usage-link' ), usage ) >= 0 );
		} );
	}

	function showStep( step ) {
		currentStep = step;
		var index = getStepIndex( step );
		var percent = ( index / ( steps.length - 1 ) ) * 100;

		$wizard.attr( 'data-current-step', step ).data( 'current-step', step );
		$wizard.toggleClass( 'is-intro-step', step === 'intro' );
		$( '.ph-onboarding__panel' ).removeClass( 'is-active' );
		$( '.ph-onboarding__panel[data-step="' + step + '"]' ).addClass( 'is-active' );

		$( '[data-progress-bar]' ).css( 'width', percent + '%' );
		$( '[data-back]' ).toggle( index > 0 );
		$( '[data-next]' ).text( step === 'complete' ? config.i18n.finish : config.i18n.continue );
		setMessage( '' );
		updateUsageLinks();
		track( 'step_viewed', step );
	}

	function setSaving( saving ) {
		$( '[data-next]' ).prop( 'disabled', saving ).toggleClass( 'is-busy', saving );
		if ( saving ) {
			$( '[data-next]' ).text( config.i18n.saving );
		} else {
			$( '[data-next]' ).text( currentStep === 'complete' ? config.i18n.finish : config.i18n.continue );
		}
	}

	function stepPayload( step ) {
		var payload = {
			action: 'propertyhive_onboarding_save_step',
			security: config.nonce,
			step: step
		};

		if ( step === 'departments' ) {
			payload.departments = selectedValues( 'departments' );
		}

		if ( step === 'intro' ) {
			payload.usage_tracking = $( '[data-usage-tracking]' ).is( ':checked' ) ? 'yes' : 'no';
		}

		if ( step === 'country' ) {
			payload.country = $( '[data-country-select]' ).val();
		}

		if ( step === 'office' ) {
			$( '[data-office-field]' ).each( function() {
				var key = $( this ).attr( 'data-office-field' );
				if ( key ) {
					payload[ key ] = $( this ).val();
				}
			} );
		}

		if ( step === 'usage' ) {
			payload.usage = selectedValues( 'usage' );
		}

		if ( step === 'demo-data' ) {
			payload.demo_data_imported = demoImported ? 'yes' : 'no';
		}

		return payload;
	}

	function validateStep( step ) {
		if ( step === 'departments' && ! selectedValues( 'departments' ).length ) {
			setMessage( config.i18n.chooseDepartment, 'error' );
			return false;
		}

		return true;
	}

	function saveStep( step ) {
		if ( ! validateStep( step ) ) {
			return $.Deferred().reject().promise();
		}

		setSaving( true );

		return $.post( config.ajax_url, stepPayload( step ) )
			.done( function( response ) {
				if ( ! response || ! response.success ) {
					var message = response && response.data && response.data.message ? response.data.message : 'Setup could not be saved.';
					setMessage( message, 'error' );
				}
			} )
			.fail( function( xhr ) {
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Setup could not be saved.';
				setMessage( message, 'error' );
			} )
			.always( function() {
				setSaving( false );
			} );
	}

	function demoSections() {
		var usage = selectedValues( 'usage' );
		var usesCrm = $.inArray( 'crm', usage ) >= 0;

		if ( ! usesCrm ) {
			return {
				base: [ 'property' ],
				sub: []
			};
		}

		return {
			base: config.demo_base_sections || [ 'property', 'contact' ],
			sub: config.demo_crm_sub_sections || []
		};
	}

	function updateDemoProgress( done, total, text ) {
		var percent = total > 0 ? ( done / total ) * 100 : 0;
		$( '[data-demo-progress-bar]' ).css( 'width', percent + '%' );
		$( '[data-demo-status]' ).text( text );
	}

	function refreshDemoState() {
		if ( demoImported ) {
			$( 'input[name="demo_data_choice"][value="yes"]' ).prop( 'checked', true );
			updateDemoProgress( 1, 1, config.i18n.importComplete );
		}

		updateDemoChoice();
	}

	function appendDemoResult( text ) {
		$( '[data-demo-results]' ).append( $( '<div />' ).text( text ) );
	}

	function demoResponseKey( section ) {
		return section === 'applicant' || section === 'property_owner' ? 'contact' : section;
	}

	function getSectionDemoData( section ) {
		return $.ajax( {
			url: config.ajax_url,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'propertyhive_get_section_demo_data',
				section: section
			}
		} );
	}

	function createDemoRecords( dataItems ) {
		return $.ajax( {
			url: config.ajax_url,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'propertyhive_create_demo_data_records',
				data_items: dataItems
			}
		} );
	}

	function importDemoData() {
		if ( demoImporting ) {
			return $.Deferred().reject().promise();
		}

		if ( config.demo_data_active !== 'yes' ) {
			return $.Deferred().reject().promise();
		}

		var sections = demoSections();
		var total = sections.base.length + sections.sub.length;
		var done = 0;

		demoImporting = true;
		demoImported = false;
		$( '[data-next]' ).prop( 'disabled', true ).addClass( 'is-busy' ).text( config.i18n.importing );
		$( '[data-demo-results]' ).empty();
		updateDemoProgress( done, total, config.i18n.importing );
		track( 'demo_data_started', 'demo-data' );

		return getSectionDemoData( sections.base )
			.then( function( dataItems ) {
				return createDemoRecords( dataItems );
			} )
			.then( function( response ) {
				$.each( sections.base, function( index, section ) {
					done++;
					appendDemoResult( section + ': ' + ( response[ demoResponseKey( section ) ] || 0 ) + ' records created' );
				} );
				updateDemoProgress( done, total, config.i18n.importing );

				var chain = $.Deferred().resolve().promise();
				$.each( sections.sub, function( index, section ) {
					chain = chain.then( function() {
						return getSectionDemoData( section )
							.then( function( dataItems ) {
								return createDemoRecords( dataItems );
							} )
							.then( function( subResponse ) {
								done++;
								appendDemoResult( section + ': ' + ( subResponse[ section ] || 0 ) + ' records created' );
								updateDemoProgress( done, total, config.i18n.importing );
							} );
					} );
				} );

				return chain;
			} )
			.done( function() {
				demoImported = true;
				demoImporting = false;
				updateDemoProgress( total, total, config.i18n.importComplete );
			} )
			.fail( function() {
				demoImporting = false;
				updateDemoProgress( done, total, config.i18n.importFailed );
				setMessage( config.i18n.importFailed, 'error' );
				track( 'demo_data_failed', 'demo-data' );
			} )
			.always( function() {
				setSaving( false );
			} );
	}

	function updateDemoChoice() {
		var wantsDemo = $( 'input[name="demo_data_choice"]:checked' ).val() === 'yes';
		$( '[data-demo-progress-box]' ).toggle( wantsDemo );
	}

	function maybeImportDemoData() {
		if ( currentStep !== 'demo-data' || $( 'input[name="demo_data_choice"]:checked' ).val() !== 'yes' || demoImported ) {
			return $.Deferred().resolve().promise();
		}

		if ( config.demo_data_active !== 'yes' ) {
			setMessage( config.i18n.demoDataInactive, 'error' );
			return $.Deferred().reject().promise();
		}

		return importDemoData();
	}

	$( document ).on( 'change', 'input[name="usage[]"]', updateUsageLinks );
	$( document ).on( 'change', 'input[name="demo_data_choice"]', updateDemoChoice );

	$( '[data-next]' ).on( 'click', function() {
		var index = getStepIndex( currentStep );

		if ( demoImporting ) {
			return;
		}

		maybeImportDemoData().done( function() {
			saveStep( currentStep ).done( function( response ) {
				if ( response && response.success === false ) {
					return;
				}

				if ( currentStep === 'complete' ) {
					window.location.href = config.complete_redirect_url || config.settings_url;
					return;
				}

				showStep( steps[ index + 1 ] );
			} );
		} );
	} );

	$( '[data-back]' ).on( 'click', function() {
		var index = getStepIndex( currentStep );
		if ( index > 0 ) {
			showStep( steps[ index - 1 ] );
		}
	} );

	$( '[data-ph-onboarding-skip]' ).on( 'click', function() {
		if ( ! window.confirm( config.i18n.skipConfirm ) ) {
			return;
		}

		$.post( config.ajax_url, {
			action: 'propertyhive_onboarding_skip',
			security: config.nonce,
			step: currentStep
		} ).done( function( response ) {
			window.location.href = response && response.data && response.data.redirect_url ? response.data.redirect_url : config.settings_url;
		} );
	} );

	$( document ).on( 'change', '.ph-onboarding__choice input', function() {
		if ( $( this ).attr( 'type' ) === 'radio' ) {
			if ( $( this ).is( ':checked' ) ) {
				$( 'input[name="' + $( this ).attr( 'name' ) + '"]' ).closest( '.ph-onboarding__choice' ).removeClass( 'is-selected' );
				$( this ).closest( '.ph-onboarding__choice' ).addClass( 'is-selected' );
			} else {
				$( this ).closest( '.ph-onboarding__choice' ).removeClass( 'is-selected' );
			}
			return;
		}

		$( this ).closest( '.ph-onboarding__choice' ).toggleClass( 'is-selected', $( this ).is( ':checked' ) );
	} );

	$( '.ph-onboarding__choice input' ).trigger( 'change' );
	showStep( currentStep );
	refreshDemoState();
} );
