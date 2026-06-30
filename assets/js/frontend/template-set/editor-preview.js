(function () {
	'use strict';

	var modules = window.phTemplateSetModules = window.phTemplateSetModules || {};

	function removePrefixedClass(element, prefix) {
		Array.prototype.slice.call(element.classList).forEach(function (className) {
			if (className.indexOf(prefix) === 0) {
				element.classList.remove(className);
			}
		});
	}

	function setBodyOption(prefix, value) {
		removePrefixedClass(document.body, prefix);
		document.body.classList.add(prefix + value);
	}

	function setSearchOption(prefix, value) {
		setBodyOption(prefix, value);

		document.querySelectorAll('.ph-template-search').forEach(function (search) {
			removePrefixedClass(search, prefix);
			search.classList.add(prefix + value);
		});
	}

	function setBodyToggle(showClass, hideClass, enabled) {
		document.body.classList.toggle(showClass, enabled);
		document.body.classList.toggle(hideClass, !enabled);
	}

	function applyRecommendedCount(value) {
		var limit = parseInt(value, 10) || 3;

		document.querySelectorAll('[data-ph-recommended-properties]').forEach(function (section) {
			Array.prototype.slice.call(section.querySelectorAll('[data-ph-recommended-card]')).forEach(function (card, index) {
				card.hidden = index >= limit;
			});
		});
	}

	function resetGalleryPanel(panelName) {
		if (modules.gallery && typeof modules.gallery.resetPanel === 'function') {
			modules.gallery.resetPanel(panelName);
		}
	}

	function getSelectedOption(control) {
		if (!control || typeof control.selectedIndex !== 'number' || control.selectedIndex < 0) {
			return null;
		}

		return control.options[control.selectedIndex] || null;
	}

	function maybeNavigateTemplatePreview(control) {
		var selectedOption;
		var previewUrl;

		if (control.name !== 'template_set_detail_template' && control.name !== 'template_set_search_template') {
			return false;
		}

		selectedOption = getSelectedOption(control);
		previewUrl = selectedOption ? selectedOption.getAttribute('data-ph-template-preview-url') : '';

		if (!previewUrl || previewUrl === window.location.href) {
			return false;
		}

		window.location.href = previewUrl;
		return true;
	}

	function updateSegmentedControl(input) {
		var group = input.closest('.ph-template-editor-segmented');

		if (!group) {
			return;
		}

		group.querySelectorAll('label').forEach(function (label) {
			var labelInput = label.querySelector('input');
			label.classList.toggle('is-active', !!labelInput && labelInput.checked);
		});
	}


	var editorControlHandlers = {
		template_set_gallery_layout: function (value, control) {
			if (modules.gallery && typeof modules.gallery.setVariant === 'function') {
				modules.gallery.setVariant(value, false);
			}
			updateSegmentedControl(control);
		},
		template_set_button_style: function (value) {
			setBodyOption('ph-template-buttons-', value);
		},
		template_set_search_layout: function (value) {
			setSearchOption('ph-search-view-', value);
		},
		template_set_search_card_size: function (value) {
			setSearchOption('ph-search-card-size-', value);
		},
		template_set_search_grid_columns: function (value) {
			setSearchOption('ph-search-grid-columns-', value);
		},
		template_set_image_style: function (value) {
			setBodyOption('ph-template-images-', value);
		},
		template_set_contact_card_style: function (value) {
			setBodyOption('ph-template-contact-card-', value);
		},
		template_set_show_branch: function (value) {
			setBodyToggle('ph-template-show-branch', 'ph-template-hide-branch', value === 'yes');
		},
		template_set_show_badges: function (value) {
			setBodyToggle('ph-template-show-badges', 'ph-template-hide-badges', value === 'yes');
		},
		template_set_show_mobile_cta: function (value) {
			setBodyToggle('ph-template-show-mobile-cta', 'ph-template-hide-mobile-cta', value === 'yes');
		},
		template_set_show_floorplans: function (value) {
			setBodyToggle('ph-template-show-floorplans', 'ph-template-hide-floorplans', value === 'yes');

			if (value !== 'yes') {
				resetGalleryPanel('floorplan');
			}
		},
		template_set_show_virtual_tours: function (value) {
			setBodyToggle('ph-template-show-virtual-tours', 'ph-template-hide-virtual-tours', value === 'yes');

			if (value !== 'yes') {
				resetGalleryPanel('virtual-tour');
			}
		},
		template_set_show_recommended: function (value) {
			setBodyToggle('ph-template-show-recommended', 'ph-template-hide-recommended', value === 'yes');
		},
		template_set_recommended_count: function (value) {
			setBodyOption('ph-template-recommended-count-', value);
			applyRecommendedCount(value);
		},
		template_set_recommended_layout: function (value) {
			setBodyOption('ph-template-recommended-layout-', value);
		},
		template_set_recommended_image_size: function (value) {
			setBodyOption('ph-template-recommended-images-', value);
		}
	};

	function applyEditorControl(control) {
		var handler;
		var value;

		if (!control || !control.name) {
			return;
		}

		if (control.type === 'radio' && !control.checked) {
			return;
		}

		handler = editorControlHandlers[control.name];
		if (!handler) {
			return;
		}

		value = control.type === 'checkbox' ? (control.checked ? 'yes' : '') : control.value;
		handler(value, control);
	}

	modules.editorPreview = {
		applyControl: applyEditorControl,
		maybeNavigateTemplatePreview: maybeNavigateTemplatePreview
	};
}());
