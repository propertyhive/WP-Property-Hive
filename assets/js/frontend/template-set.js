(function () {
	'use strict';

	var modules = window.phTemplateSetModules = window.phTemplateSetModules || {};
	var config = window.phTemplateSet || {};

	// Keep the existing CSS and public JavaScript contract stable while feature code lives in focused modules.
	function setEditorStatus(editor, message, state) {
		var status = editor.querySelector('[data-ph-template-editor-status]');

		if (status) {
			status.textContent = message;
		}

		editor.setAttribute('data-ph-template-editor-state', state || 'ready');
	}

	function buildEditorFormData(form) {
		var data = new window.FormData();

		data.append('action', 'propertyhive_template_set_save');
		data.append('security', config.security || '');

		Array.prototype.slice.call(form.elements).forEach(function (field) {
			if (!field.name || field.disabled) {
				return;
			}

			if (field.type === 'checkbox') {
				data.append(field.name, field.checked ? field.value : '');
				return;
			}

			if (field.type === 'radio') {
				if (field.checked) {
					data.append(field.name, field.value);
				}
				return;
			}

			data.append(field.name, field.value);
		});

		return data;
	}

	function hasUnsavedEditorChanges(editor, searchFormBuilder) {
		return editor.classList.contains('is-dirty') || !!(searchFormBuilder && typeof searchFormBuilder.isDirty === 'function' && searchFormBuilder.isDirty());
	}

	function getControlValue(control) {
		if (control.type === 'checkbox') {
			return control.checked ? control.value : '';
		}

		if (control.type === 'radio') {
			return control.checked ? control.value : '';
		}

		return control.value;
	}

	function restoreControlValue(control, value) {
		if (control.type === 'checkbox') {
			control.checked = value === control.value;
			return;
		}

		if (control.type === 'radio') {
			control.checked = value === control.value;
			return;
		}

		control.value = value;
	}

	function confirmTemplateNavigation(labels) {
		return window.confirm(labels.unsavedNavigation || 'You have unsaved changes. Leave this page without saving?');
	}

	function initTemplateEditor() {
		var editor = document.querySelector('[data-ph-template-editor]');
		var form;
		var labels;
		var searchFormBuilder;

		if (!editor || !config.editorActive) {
			return;
		}

		form = editor.querySelector('[data-ph-template-editor-form]');
		labels = config.labels || {};

		if (!form) {
			return;
		}

		if (modules.searchFormBuilder && typeof modules.searchFormBuilder.init === 'function') {
			searchFormBuilder = modules.searchFormBuilder.init(config, editor, form, labels, {
				setEditorStatus: setEditorStatus,
				refreshEditorGroupBody: function (targetForm) {
					if (modules.editorSidebar && typeof modules.editorSidebar.refreshActiveGroupBodyWithoutTransition === 'function') {
						modules.editorSidebar.refreshActiveGroupBodyWithoutTransition(targetForm);
					}
				}
			});
		}

		if (modules.editorSidebar && typeof modules.editorSidebar.init === 'function') {
			modules.editorSidebar.init(editor, form, config.editorSidebarLayout || null);
		}

		form.querySelectorAll('[data-ph-template-editor-control]').forEach(function (control) {
			control.setAttribute('data-ph-template-editor-previous-value', getControlValue(control));

			control.addEventListener('change', function () {
				var previousValue = control.getAttribute('data-ph-template-editor-previous-value') || '';
				var previewUrl = modules.editorPreview && typeof modules.editorPreview.getTemplatePreviewUrl === 'function' ? modules.editorPreview.getTemplatePreviewUrl(control) : '';

				if (previewUrl && previewUrl !== window.location.href) {
					if (hasUnsavedEditorChanges(editor, searchFormBuilder) && !confirmTemplateNavigation(labels)) {
						restoreControlValue(control, previousValue);
						return;
					}

					setEditorStatus(editor, labels.loading || 'Loading...', 'saving');
					window.location.href = previewUrl;
					return;
				}

				if (modules.editorPreview && typeof modules.editorPreview.applyControl === 'function') {
					modules.editorPreview.applyControl(control);
				}
				control.setAttribute('data-ph-template-editor-previous-value', getControlValue(control));
				editor.classList.add('is-dirty');
				setEditorStatus(editor, labels.changed || 'Unsaved changes', 'changed');
			});

			if (control.type === 'color') {
				control.addEventListener('input', function () {
					if (modules.editorPreview && typeof modules.editorPreview.applyControl === 'function') {
						modules.editorPreview.applyControl(control);
					}
					control.setAttribute('data-ph-template-editor-previous-value', getControlValue(control));
					editor.classList.add('is-dirty');
					setEditorStatus(editor, labels.changed || 'Unsaved changes', 'changed');
				});
			}
		});

		form.addEventListener('submit', function (event) {
			var saveButton = form.querySelector('[data-ph-template-editor-save]');

			event.preventDefault();

			if (!window.fetch || !window.FormData) {
				setEditorStatus(editor, labels.error || 'Could not save', 'error');
				return;
			}

			setEditorStatus(editor, labels.saving || 'Saving...', 'saving');
			if (saveButton) {
				saveButton.disabled = true;
			}

			window.fetch(config.ajaxUrl || '', {
				method: 'POST',
				credentials: 'same-origin',
				body: buildEditorFormData(form)
			}).then(function (response) {
				return response.json();
			}).then(function (payload) {
				if (!payload || !payload.success) {
					throw new Error(payload && payload.data && payload.data.message ? payload.data.message : (labels.error || 'Could not save'));
				}

				config.settings = payload.data && payload.data.settings ? payload.data.settings : config.settings;

				if (searchFormBuilder && searchFormBuilder.isDirty()) {
					return searchFormBuilder.save();
				}
			}).then(function () {
				editor.classList.remove('is-dirty');
				form.querySelectorAll('[data-ph-template-editor-control]').forEach(function (control) {
					control.setAttribute('data-ph-template-editor-previous-value', getControlValue(control));
				});
				setEditorStatus(editor, labels.saved || 'Saved', 'saved');
			}).catch(function (error) {
				setEditorStatus(editor, error && error.message ? error.message : (labels.error || 'Could not save'), 'error');
			}).finally(function () {
				if (saveButton) {
					saveButton.disabled = false;
				}
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		if (modules.gallery && typeof modules.gallery.init === 'function') {
			modules.gallery.init(config);
		}

		initTemplateEditor();
	});

	window.phTemplateSetGallery = window.phTemplateSetGallery || {};
	window.phTemplateSetGallery.setVariant = function (variant, persist) {
		if (modules.gallery && typeof modules.gallery.setVariant === 'function') {
			modules.gallery.setVariant(variant, persist);
		}
	};
}());
