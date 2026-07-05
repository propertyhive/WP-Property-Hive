/* global propertyhive_onboarding */

/**
 * Property Hive onboarding wizard.
 */
jQuery( function( $ ) {
	'use strict';

	var config = window.propertyhive_onboarding || {};
	var fallbackSteps = [ 'intro', 'departments', 'country', 'office', 'usage', 'license', 'demo-data', 'complete' ];
	var steps = $.isArray( config.steps ) && config.steps.length ? config.steps : fallbackSteps;
	var $wizard = $( '.ph-onboarding' );
	var currentStep = $wizard.data( 'current-step' ) || 'intro';
	var demoImported = $wizard.data( 'demo-imported' ) === 'yes';
	var demoImporting = false;
	var licenseActivating = false;
	var addressLookupTimer = null;
	var addressLookupXhr = null;
	var addressGetXhr = null;
	var fieldControls = {
		departments: 'input[name="departments[]"]',
		country: '[data-country-select]',
		office_name: '[data-office-field="office_name"]',
		office_address_1: '[data-office-field="office_address_1"]',
		office_address_2: '[data-office-field="office_address_2"]',
		office_address_3: '[data-office-field="office_address_3"]',
		office_address_4: '[data-office-field="office_address_4"]',
		office_postcode: '[data-office-field="office_postcode"]',
		office_telephone_number: '[data-office-field="office_telephone_number"]',
		office_email_address: '[data-office-field="office_email_address"]',
		usage: 'input[name="usage[]"]',
		license_key: 'input[name="license_key"]',
		demo_data_choice: 'input[name="demo_data_choice"]'
	};
	var officeRules = [
		{ field: 'office_name', maxLength: 120, maxMessage: 'officeNameTooLong' },
		{ field: 'office_address_1', maxLength: 120, maxMessage: 'officeAddressTooLong' },
		{ field: 'office_address_2', required: false, maxLength: 120, maxMessage: 'officeAddress2TooLong' },
		{ field: 'office_address_3', required: false, maxLength: 120, maxMessage: 'officeAddress3TooLong' },
		{ field: 'office_address_4', required: false, maxLength: 120, maxMessage: 'officeAddress4TooLong' },
		{ field: 'office_postcode', maxLength: 20, maxMessage: 'officePostcodeTooLong' },
		{ field: 'office_telephone_number', maxLength: 30, maxMessage: 'officePhoneTooLong' },
		{ field: 'office_email_address', maxLength: 100, maxMessage: 'officeEmailTooLong' }
	];

	function getStepIndex( step ) {
		var index = $.inArray( step, steps );
		return index < 0 ? 0 : index;
	}

	function selectedValues( name ) {
		return $( 'input[name="' + name + '[]"]:checked' ).map( function() {
			return $( this ).val();
		} ).get();
	}

	function text( key, fallback ) {
		return config.i18n && config.i18n[ key ] ? config.i18n[ key ] : fallback;
	}

	function fieldSelector( field ) {
		return '[data-validation-field="' + field + '"]';
	}

	function errorSelector( field ) {
		return '[data-field-error="' + field + '"]';
	}

	function clearFieldError( field ) {
		$( fieldSelector( field ) ).removeClass( 'has-error' );
		$( errorSelector( field ) ).text( '' ).hide();

		if ( fieldControls[ field ] ) {
			$( fieldControls[ field ] ).removeAttr( 'aria-invalid' );
		}
	}

	function setFieldError( field, message ) {
		var $field = $( fieldSelector( field ) );
		var $error = $( errorSelector( field ) );

		if ( ! $field.length || ! $error.length ) {
			return;
		}

		$field.addClass( 'has-error' );
		$error.text( message ).css( 'display', 'block' );

		if ( fieldControls[ field ] ) {
			$( fieldControls[ field ] ).attr( 'aria-invalid', 'true' );
		}
	}

	function clearValidation( step ) {
		var $scope = step ? $( '.ph-onboarding__panel[data-step="' + step + '"]' ) : $wizard;

		$scope.find( '[data-validation-field]' ).removeClass( 'has-error' );
		$scope.find( '[data-field-error]' ).text( '' ).hide();
		$scope.find( '[aria-invalid="true"]' ).removeAttr( 'aria-invalid' );
	}

	function applyFieldErrors( errors ) {
		var firstMessage = '';
		var firstField = '';

		if ( ! errors ) {
			return '';
		}

		$.each( errors, function( field, message ) {
			if ( ! firstMessage ) {
				firstMessage = message;
				firstField = field;
			}
			setFieldError( field, message );
		} );

		if ( firstField && fieldControls[ firstField ] ) {
			$( fieldControls[ firstField ] ).first().trigger( 'focus' );
		}

		return firstMessage;
	}

	function fieldValue( field ) {
		return $.trim( $( fieldControls[ field ] ).val() || '' );
	}

	function isValidEmail( value ) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value );
	}

	function isValidPhone( value ) {
		var digits = value.replace( /\D+/g, '' );
		return digits.length >= 7 && /^[0-9+\-\s().]+$/.test( value );
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

	function setAddressStatus( message, type ) {
		var $status = $( '[data-address-lookup-status]' );
		$status.removeClass( 'is-error is-success' );

		if ( ! message ) {
			$status.text( '' ).hide();
			return;
		}

		$status.addClass( type === 'error' ? 'is-error' : 'is-success' ).text( message ).show();
	}

	function clearAddressResults() {
		$( '[data-address-lookup-results]' ).empty().prop( 'hidden', true );
	}

	function renderAddressSuggestions( suggestions ) {
		var $results = $( '[data-address-lookup-results]' );
		$results.empty();

		if ( ! suggestions || ! suggestions.length ) {
			clearAddressResults();
			setAddressStatus( text( 'addressLookupNoResults', 'No matching addresses found.' ), 'error' );
			return;
		}

		$.each( suggestions, function( index, suggestion ) {
			if ( ! suggestion.id || ! suggestion.address ) {
				return;
			}

			$( '<button />', {
				type: 'button',
				class: 'ph-onboarding__address-result',
				text: suggestion.address
			} ).attr( 'data-address-id', suggestion.id ).appendTo( $results );
		} );

		$results.prop( 'hidden', false );
		setAddressStatus( '' );
	}

	function setOfficeField( field, value ) {
		$( '[data-office-field="' + field + '"]' ).val( value || '' ).trigger( 'input' ).trigger( 'change' );
	}

	function fillOfficeAddress( address ) {
		address = address || {};

		setOfficeField( 'office_address_1', address.line_1 );
		setOfficeField( 'office_address_2', address.line_2 );
		setOfficeField( 'office_address_3', address.town_or_city );
		setOfficeField( 'office_address_4', address.county );
		setOfficeField( 'office_postcode', address.postcode );

		clearAddressResults();
		$( '[data-address-lookup-input]' ).val( '' );
		setAddressStatus( text( 'addressLookupSelected', 'Address selected.' ), 'success' );
	}

	function fetchAddress( id ) {
		if ( ! id || config.address_lookup_enabled !== 'yes' ) {
			return;
		}

		if ( addressGetXhr ) {
			addressGetXhr.abort();
		}

		setAddressStatus( text( 'addressLookupSearching', 'Searching addresses...' ) );

		addressGetXhr = $.post( config.ajax_url, {
			action: 'propertyhive_getaddress_get',
			security: config.address_lookup_nonce,
			id: id
		} ).done( function( response ) {
			if ( response && response.success && response.data && response.data.address ) {
				fillOfficeAddress( response.data.address );
				return;
			}

			setAddressStatus( text( 'addressLookupFailed', 'Address lookup could not be completed. Please enter the address manually.' ), 'error' );
		} ).fail( function( xhr ) {
			if ( xhr.statusText === 'abort' ) {
				return;
			}

			setAddressStatus( text( 'addressLookupFailed', 'Address lookup could not be completed. Please enter the address manually.' ), 'error' );
		} );
	}

	function searchAddresses( term ) {
		if ( config.address_lookup_enabled !== 'yes' ) {
			return;
		}

		term = $.trim( term || '' );

		if ( term.length < 3 ) {
			if ( addressLookupXhr ) {
				addressLookupXhr.abort();
			}
			clearAddressResults();
			setAddressStatus( '' );
			return;
		}

		if ( addressLookupXhr ) {
			addressLookupXhr.abort();
		}

		setAddressStatus( text( 'addressLookupSearching', 'Searching addresses...' ) );

		addressLookupXhr = $.post( config.ajax_url, {
			action: 'propertyhive_getaddress_autocomplete',
			security: config.address_lookup_nonce,
			term: term
		} ).done( function( response ) {
			if ( response && response.success && response.data ) {
				renderAddressSuggestions( response.data.suggestions || [] );
				return;
			}

			clearAddressResults();
			setAddressStatus( text( 'addressLookupFailed', 'Address lookup could not be completed. Please enter the address manually.' ), 'error' );
		} ).fail( function( xhr ) {
			if ( xhr.statusText === 'abort' ) {
				return;
			}

			clearAddressResults();
			setAddressStatus( text( 'addressLookupFailed', 'Address lookup could not be completed. Please enter the address manually.' ), 'error' );
		} );
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
		clearValidation( step );
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

		if ( step === 'license' ) {
			payload.has_license_key = $( 'input[name="has_license_key"]:checked' ).val() || $( 'input[name="has_license_key"][type="hidden"]' ).val() || 'no';
			payload.license_key_type = $( 'input[name="license_key_type"]:checked' ).val() || $( 'input[name="license_key_type"][type="hidden"]' ).val() || 'pro';
		}

		if ( step === 'demo-data' ) {
			payload.demo_data_choice = $( 'input[name="demo_data_choice"]:checked' ).val() || '';
			payload.demo_data_imported = demoImported ? 'yes' : 'no';
		}

		return payload;
	}

	function validateStep( step ) {
		var errors = {};

		clearValidation( step );

		if ( step === 'departments' && ! selectedValues( 'departments' ).length ) {
			errors.departments = text( 'chooseDepartment', 'Please choose at least one property sector.' );
		}

		if ( step === 'country' && ! $( '[data-country-select]' ).val() ) {
			errors.country = text( 'chooseCountry', 'Please choose a valid country.' );
		}

		if ( step === 'office' ) {
			$.each( officeRules, function( index, rule ) {
				var value = fieldValue( rule.field );

				if ( rule.required && ! value ) {
					errors[ rule.field ] = text( rule.requiredMessage, 'This field is required.' );
					return;
				}

				if ( value && rule.maxLength && value.length > rule.maxLength ) {
					errors[ rule.field ] = text( rule.maxMessage, 'This field is too long.' );
					return;
				}

				if ( value && rule.field === 'office_telephone_number' && ! isValidPhone( value ) ) {
					errors.office_telephone_number = text( 'officePhoneInvalid', 'Please enter a valid phone number.' );
				}

				if ( value && rule.field === 'office_email_address' && ! isValidEmail( value ) ) {
					errors.office_email_address = text( 'officeEmailInvalid', 'Please enter a valid email address.' );
				}
			} );
		}

		if ( step === 'usage' && ! selectedValues( 'usage' ).length ) {
			errors.usage = text( 'chooseUsage', 'Please choose how you will use Property Hive.' );
		}

		if ( step === 'demo-data' ) {
			if ( ! $( 'input[name="demo_data_choice"]:checked' ).length ) {
				errors.demo_data_choice = text( 'chooseDemoData', 'Please choose whether to import demo data.' );
			} else if ( $( 'input[name="demo_data_choice"]:checked' ).val() === 'yes' && config.demo_data_active !== 'yes' ) {
				errors.demo_data_choice = text( 'demoDataInactive', 'The Demo Data feature is not active on this site yet.' );
			}
		}

		if ( ! $.isEmptyObject( errors ) ) {
			setMessage( applyFieldErrors( errors ), 'error' );
			return false;
		}

		return true;
	}

	function saveStep( step, skipClientValidation ) {
		if ( ! skipClientValidation && ! validateStep( step ) ) {
			return $.Deferred().reject().promise();
		}

		setSaving( true );

		return $.post( config.ajax_url, stepPayload( step ) )
			.done( function( response ) {
				if ( ! response || ! response.success ) {
					var message = response && response.data && response.data.message ? response.data.message : 'Setup could not be saved.';
					clearValidation( step );
					if ( response && response.data && response.data.errors ) {
						message = applyFieldErrors( response.data.errors ) || message;
					}
					setMessage( message, 'error' );
				}
			} )
			.fail( function( xhr ) {
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Setup could not be saved.';
				clearValidation( step );
				if ( xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.errors ) {
					message = applyFieldErrors( xhr.responseJSON.data.errors ) || message;
				}
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

	function updateLicenseChoice() {
		var choice = $( 'input[name="has_license_key"]:checked' ).val() || 'no';
		$( '[data-license-panel]' ).removeClass( 'is-open' );
		$( '[data-license-panel="' + choice + '"]' ).addClass( 'is-open' );
	}

	function updateLicenseType() {
		var type = $( 'input[name="license_key_type"]:checked' ).val() || 'pro';
		$( '[data-license-old-note]' ).toggle( type === 'old' );
	}

	function setLicenseState( type, content ) {
		var $state = $( '[data-license-state]' );

		$state.removeClass( 'is-busy is-ok is-error' );
		if ( ! content ) {
			$state.empty().hide();
			return;
		}

		$state.addClass( type ? 'is-' + type : '' ).empty().append( content ).show();
	}

	function renderLicenseBusy() {
		setLicenseState( 'busy', $( '<span />' ).text( text( 'licenseChecking', 'Checking your key... Contacting wp-property-hive.com - a couple of seconds.' ) ) );
	}

	function renderLicenseSuccess( licenseType, summary ) {
		var $content = $( '<div />' );
		var message = licenseType === 'old' ? text( 'licenseOldSuccess', 'License valid - updates are enabled for your purchased add-ons.' ) : text( 'licenseProSuccess', 'Pro activated. The features in your plan are ready to use.' );

		if ( licenseType === 'old' && summary ) {
			message += ' Expires ' + summary + '.';
		}

		$( '<strong />' ).text( '\u2713 ' + message ).appendTo( $content );

		if ( licenseType === 'old' ) {
			$( '<a />', {
				href: config.license_trial_url || config.import_features_url || '#',
				target: '_blank',
				rel: 'noopener noreferrer',
				text: text( 'licenseOldTrial', 'Want the Pro features too? Start a free 7-day trial.' )
			} ).appendTo( $( '<p />', { class: 'ph-onboarding__license-trial-link' } ).appendTo( $content ) );
		}

		setLicenseState( 'ok', $content.contents() );
	}

	function renderLicenseError( message, renewUrl ) {
		var $content = $( '<div />' );

		$( '<strong />' ).text( message || text( 'licenseError', "That key wasn't recognised. Check for typos, or recover your key. You can keep going and add it later - nothing is lost." ) ).appendTo( $content );

		if ( renewUrl ) {
			$content.append( ' ' );
			$( '<a />', {
				href: renewUrl,
				target: '_blank',
				rel: 'noopener noreferrer',
				text: text( 'renewLicense', 'Renew License' )
			} ).appendTo( $content );
		}

		setLicenseState( 'error', $content.contents() );
	}

	function lockLicenseSuccess() {
		$( 'input[name="has_license_key"][value="yes"]' ).prop( 'checked', true ).trigger( 'change' );
		$( 'input[name="has_license_key"], input[name="license_key_type"]' ).prop( 'disabled', true ).closest( '.ph-onboarding__choice' ).addClass( 'is-disabled' );
		$( '[data-license-entry]' ).hide();
		$( '[data-license-old-note]' ).hide();
	}

	function revealImportSetupLink() {
		$( '[data-import-setup-link]' )
			.attr( 'href', config.import_setup_url || 'admin.php?page=propertyhive_import_properties' )
			.removeAttr( 'hidden' );
	}

	function appendImportEnabledLine() {
		var $state = $( '[data-license-state]' );

		if ( $state.find( '[data-license-import-enabled]' ).length ) {
			return;
		}

		$( '<p />', {
			'data-license-import-enabled': 'yes',
			text: text( 'importFeatureEnabled', 'Property Import feature enabled' ) + ' \u2713'
		} ).appendTo( $state );
	}

	function importFeatureSucceeded() {
		appendImportEnabledLine();
		revealImportSetupLink();
	}

	function maybeEnableImportFeature() {
		if ( $.inArray( 'import_properties', selectedValues( 'usage' ) ) < 0 ) {
			return;
		}

		if ( config.import_feature_active === 'yes' ) {
			importFeatureSucceeded();
			return;
		}

		if ( config.can_install_plugins !== 'yes' || ! config.import_feature_slug || ! config.updates_nonce ) {
			return;
		}

		$.ajax( {
			url: config.ajax_url,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'propertyhive_activate_pro_feature',
				slug: config.import_feature_slug,
				_ajax_nonce: config.updates_nonce
			}
		} ).done( function( response ) {
			if ( response && response.success === true ) {
				if ( response.data && response.data.activateUrl ) {
					$.get( response.data.activateUrl ).done( importFeatureSucceeded );
					return;
				}

				importFeatureSucceeded();
				return;
			}

			if ( response && response.data && response.data.errorMessage === 'Plugin already active' ) {
				importFeatureSucceeded();
			}
		} ).fail( function() {
			if ( window.console && window.console.log ) {
				window.console.log( 'Property Import auto-enable failed.' );
			}
		} );
	}

	function activateLicense() {
		if ( licenseActivating ) {
			return;
		}

		var licenseKey = $.trim( $( 'input[name="license_key"]' ).val() || '' );
		var licenseType = $( 'input[name="license_key_type"]:checked' ).val() || 'pro';

		clearFieldError( 'license_key' );
		setMessage( '' );

		if ( ! licenseKey ) {
			setFieldError( 'license_key', text( 'licenseKeyRequired', 'Please enter your license key.' ) );
			return;
		}

		licenseActivating = true;
		$( '[data-license-activate]' ).prop( 'disabled', true ).addClass( 'is-busy' );
		renderLicenseBusy();

		$.post( config.ajax_url, {
			action: 'propertyhive_onboarding_activate_license',
			security: config.nonce,
			license_key_type: licenseType,
			license_key: licenseKey
		} ).done( function( response ) {
			var data = response && response.data ? response.data : {};

			if ( response && response.success && data.activated ) {
				renderLicenseSuccess( data.license_type, data.summary );
				lockLicenseSuccess();

				if ( data.license_type === 'pro' ) {
					maybeEnableImportFeature();
				}

				return;
			}

			renderLicenseError( data.message, data.renew_url );
			$( '[data-license-activate]' ).prop( 'disabled', false ).removeClass( 'is-busy' );
		} ).fail( function( xhr ) {
			var data = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : {};
			renderLicenseError( data.message, data.renew_url );
			$( '[data-license-activate]' ).prop( 'disabled', false ).removeClass( 'is-busy' );
		} ).always( function() {
			licenseActivating = false;
		} );
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

	$( document ).on( 'change', 'input[name="demo_data_choice"]', updateDemoChoice );
	$( document ).on( 'change', 'input[name="has_license_key"]', updateLicenseChoice );
	$( document ).on( 'change', 'input[name="license_key_type"]', updateLicenseType );
	$( document ).on( 'click', '[data-license-activate]', activateLicense );
	$( document ).on( 'click', '[data-import-setup-link]', function() {
		track( 'setup_import_clicked', 'complete' );
	} );
	$( document ).on( 'input', '[data-address-lookup-input]', function() {
		var value = $( this ).val();

		window.clearTimeout( addressLookupTimer );
		addressLookupTimer = window.setTimeout( function() {
			searchAddresses( value );
		}, 300 );
	} );
	$( document ).on( 'click', '[data-address-id]', function() {
		fetchAddress( $( this ).attr( 'data-address-id' ) );
	} );

	$( document ).on( 'input change', '[data-office-field], [data-country-select]', function() {
		var field = $( this ).attr( 'data-office-field' ) || 'country';
		clearFieldError( field );
		setMessage( '' );
	} );

	$( document ).on( 'input change', 'input[name="license_key"]', function() {
		clearFieldError( 'license_key' );
	} );

	$( '[data-next]' ).on( 'click', function() {
		var index = getStepIndex( currentStep );

		if ( demoImporting ) {
			return;
		}

		if ( ! validateStep( currentStep ) ) {
			return;
		}

		maybeImportDemoData().done( function() {
			saveStep( currentStep, true ).done( function( response ) {
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
			step: currentStep,
			usage_tracking: $( '[data-usage-tracking]' ).is( ':checked' ) ? 'yes' : 'no'
		} ).done( function( response ) {
			window.location.href = response && response.data && response.data.redirect_url ? response.data.redirect_url : config.settings_url;
		} );
	} );

	$( document ).on( 'change', '.ph-onboarding__choice input', function() {
		var name = $( this ).attr( 'name' );

		if ( name === 'departments[]' ) {
			clearFieldError( 'departments' );
			setMessage( '' );
		}

		if ( name === 'usage[]' ) {
			clearFieldError( 'usage' );
			setMessage( '' );
		}

		if ( name === 'demo_data_choice' ) {
			clearFieldError( 'demo_data_choice' );
			setMessage( '' );
		}

		if ( name === 'has_license_key' ) {
			setMessage( '' );
		}

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
	updateLicenseChoice();
	updateLicenseType();
} );
