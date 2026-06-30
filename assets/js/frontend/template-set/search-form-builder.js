(function () {
	'use strict';

	var modules = window.phTemplateSetModules = window.phTemplateSetModules || {};
	var activeConfig = {};
	var activeHelpers = {};
	function cloneSearchFormField(field) {
		return JSON.parse(JSON.stringify(field || {}));
	}

	function getSearchFormFieldById(state, fieldId) {
		var pools = [state.active, state.inactive];
		var found = null;

		pools.forEach(function (pool) {
			pool.some(function (field) {
				if (field.id === fieldId) {
					found = field;
					return true;
				}
				return false;
			});
		});

		return found;
	}

	function buildSearchFormPayload(state) {
		function prepareList(list) {
			return list.map(function (field) {
				return {
					id: field.id,
					settings: field.settings || {}
				};
			});
		}

		return {
			active_fields: prepareList(state.active),
			inactive_fields: prepareList(state.inactive)
		};
	}

	function searchFormRequest(action, searchFormConfig, payload, extra) {
		var data = new window.FormData();

		data.append('action', action);
		data.append('security', searchFormConfig.security || '');
		data.append('payload', JSON.stringify(payload || {}));

		Object.keys(extra || {}).forEach(function (key) {
			data.append(key, extra[key]);
		});

		return window.fetch(activeConfig.ajaxUrl || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		}).then(function (response) {
			return response.json();
		}).then(function (responsePayload) {
			if (!responsePayload || !responsePayload.success) {
				throw new Error(responsePayload && responsePayload.data && responsePayload.data.message ? responsePayload.data.message : 'Request failed');
			}

			return responsePayload.data || {};
		});
	}

	function replaceSearchFormPreview(html, searchFormConfig) {
		var selector = searchFormConfig.previewSelector || '.property-search-form';
		var currentForm = document.querySelector(selector);
		var container = document.createElement('div');
		var nextForm;

		if (!currentForm || !html) {
			return;
		}

		container.innerHTML = html;
		nextForm = container.querySelector(selector) || container.querySelector('form.property-search-form');

		if (!nextForm) {
			return;
		}

		currentForm.parentNode.replaceChild(nextForm, currentForm);

		nextForm.querySelectorAll('script').forEach(function (script) {
			var executable = document.createElement('script');
			executable.text = script.text || script.textContent || '';
			document.body.appendChild(executable);
			document.body.removeChild(executable);
		});

		if (typeof window.toggleDepartmentFields === 'function') {
			window.toggleDepartmentFields();
		}

		if (window.jQuery && window.jQuery.fn && window.jQuery.fn.multiselect) {
			window.jQuery(nextForm).find('select.ph-form-multiselect').each(function () {
				var select = window.jQuery(this);

				if (select.data('ph-template-editor-multiselect')) {
					return;
				}

				select.multiselect({
					texts: {
						placeholder: select.data('blank-option')
					}
				});
				select.data('ph-template-editor-multiselect', true);
			});
		}
	}

	function initSearchFormBuilder(editor, form, labels) {
		var searchFormConfig = activeConfig.searchFormEditor || {};
		var root = form.querySelector('[data-ph-search-form-builder]');
		var state;
		var previewTimer = null;
		var previewSequence = 0;
		var draggedFieldId = '';
		var pendingHandleFocusId = '';
		var openingFieldId = '';
		var openingTimer = null;
		var closingFieldId = '';
		var closingTimer = null;
		var builderResizeObserver = null;
		var builderResizeFrame = null;

		if (!root || !searchFormConfig.enabled || !window.fetch || !window.FormData) {
			return null;
		}

		state = {
			active: (searchFormConfig.active || []).map(cloneSearchFormField),
			inactive: (searchFormConfig.inactive || []).map(cloneSearchFormField),
			categories: searchFormConfig.categories || {},
			visibilityContexts: searchFormConfig.visibilityContexts || {
				residential_sales: 'Residential sales',
				residential_lettings: 'Residential lettings',
				commercial_sales: 'Commercial sale',
				commercial_lettings: 'Commercial rent'
			},
			visibilityChoices: searchFormConfig.visibilityChoices || [],
			selectedId: '',
			dirty: false,
			previewing: false,
			baseHash: searchFormConfig.baseHash || ''
		};

		function setBuilderStatus(message, stateName) {
			var status = root.querySelector('[data-ph-search-form-builder-status]');

			if (status) {
				status.textContent = message || '';
			}

			root.setAttribute('data-ph-search-form-builder-state', stateName || 'ready');
		}

		function refreshBuilderGroupBody() {
			if (activeHelpers && typeof activeHelpers.refreshEditorGroupBody === 'function') {
				activeHelpers.refreshEditorGroupBody(form);
			}
		}

		function scheduleBuilderGroupBodyRefresh() {
			if (builderResizeFrame && window.cancelAnimationFrame) {
				window.cancelAnimationFrame(builderResizeFrame);
			}

			if (window.requestAnimationFrame) {
				builderResizeFrame = window.requestAnimationFrame(function () {
					builderResizeFrame = null;
					refreshBuilderGroupBody();
				});
			} else {
				refreshBuilderGroupBody();
			}
		}

		function getSearchFormPreview() {
			return document.querySelector(searchFormConfig.previewSelector || '.property-search-form');
		}

		function setSearchFormPreviewValue(previewForm, name, value) {
			var controls = previewForm ? Array.prototype.slice.call(previewForm.querySelectorAll('[name="' + name + '"]')) : [];
			var matched = false;
			var changed = false;

			if (!controls.length || !value) {
				return false;
			}

			controls.forEach(function (control) {
				if (control.type === 'radio' || control.type === 'checkbox') {
					if (control.value === value) {
						matched = true;
					}
					return;
				}

				if (control.tagName && control.tagName.toLowerCase() === 'select') {
					Array.prototype.slice.call(control.options).forEach(function (option) {
						if (option.value === value) {
							matched = true;
						}
					});
					return;
				}

				matched = true;
			});

			if (!matched) {
				return false;
			}

			controls.forEach(function (control) {
				if (control.type === 'radio') {
					if (control.checked !== (control.value === value)) {
						changed = true;
					}
					control.checked = control.value === value;
					return;
				}

				if (control.type === 'checkbox') {
					if (control.value === value && !control.checked) {
						changed = true;
						control.checked = true;
					}
					return;
				}

				if (control.value !== value) {
					changed = true;
				}
				control.value = value;
			});

			return changed;
		}

		function syncSearchFormPreviewSelection() {
			var field = getSearchFormFieldById(state, state.selectedId);
			var visibility = field && field.visibility ? field.visibility : {};
			var previewForm = getSearchFormPreview();
			var changed = false;

			if (!previewForm || !visibility.preview_department) {
				return;
			}

			changed = setSearchFormPreviewValue(previewForm, 'department', visibility.preview_department) || changed;

			if (visibility.commercial_availability) {
				changed = setSearchFormPreviewValue(previewForm, 'commercial_for_sale_to_rent', visibility.commercial_availability) || changed;
			}

			if (changed && typeof window.toggleDepartmentFields === 'function') {
				window.toggleDepartmentFields();
			}
		}

		function getVisibilityContextKeys() {
			return Object.keys(state.visibilityContexts || {});
		}

		function cleanDisplayContexts(contexts) {
			var allowed = getVisibilityContextKeys();
			var clean = [];

			if (!Array.isArray(contexts)) {
				contexts = [];
			}

			contexts.forEach(function (context) {
				if (allowed.indexOf(context) !== -1 && clean.indexOf(context) === -1) {
					clean.push(context);
				}
			});

			return clean;
		}

		function normalizeDisplayContexts(contexts) {
			var allowed = getVisibilityContextKeys();
			var clean = cleanDisplayContexts(contexts);

			return clean.length ? clean : allowed.slice();
		}

		function getDefaultVisibilityChoices() {
			return getVisibilityContextKeys().map(function (context) {
				return {
					id: context,
					label: state.visibilityContexts[context],
					contexts: [context]
				};
			});
		}

		function normalizeVisibilityChoices(choices) {
			var normalized = [];

			if (!Array.isArray(choices)) {
				return getDefaultVisibilityChoices();
			}

			choices.forEach(function (choice) {
				var contexts = cleanDisplayContexts(choice && choice.contexts ? choice.contexts : [choice && choice.id]);

				if (!choice || !choice.id || !contexts.length) {
					return;
				}

				normalized.push({
					id: choice.id,
					label: choice.label || contexts.map(function (context) {
						return state.visibilityContexts[context];
					}).join(', '),
					contexts: contexts
				});
			});

			return normalized.length ? normalized : getDefaultVisibilityChoices();
		}

		function getVisibilityChoices() {
			state.visibilityChoices = normalizeVisibilityChoices(state.visibilityChoices);

			return state.visibilityChoices;
		}

		function getSelectableVisibilityContextKeys() {
			var selectable = [];

			getVisibilityChoices().forEach(function (choice) {
				choice.contexts.forEach(function (context) {
					if (selectable.indexOf(context) === -1) {
						selectable.push(context);
					}
				});
			});

			return selectable;
		}

		function hasSameContexts(first, second) {
			first = normalizeDisplayContexts(first);
			second = normalizeDisplayContexts(second);

			return first.length === second.length && first.every(function (context) {
				return second.indexOf(context) !== -1;
			});
		}

		function getDisplayContextLabel(contexts) {
			var allContexts = getVisibilityContextKeys();
			var labels = [];

			contexts = normalizeDisplayContexts(contexts);

			if (hasSameContexts(contexts, allContexts)) {
				return '';
			}

			if (hasSameContexts(contexts, ['residential_sales', 'residential_lettings'])) {
				return 'Residential';
			}

			if (hasSameContexts(contexts, ['commercial_sales', 'commercial_lettings'])) {
				return 'Commercial';
			}

			contexts.forEach(function (context) {
				if (state.visibilityContexts[context]) {
					labels.push(state.visibilityContexts[context]);
				}
			});

			return labels.join(', ');
		}

		function getVisibilityForContexts(contexts) {
			var allContexts = getVisibilityContextKeys();
			var visibility = {
				scope: 'all',
				label: '',
				contexts: normalizeDisplayContexts(contexts)
			};

			if (hasSameContexts(visibility.contexts, allContexts)) {
				return visibility;
			}

			visibility.scope = 'custom';
			visibility.label = getDisplayContextLabel(visibility.contexts);

			if (visibility.contexts.indexOf('residential_sales') !== -1) {
				visibility.preview_department = 'residential-sales';
				return visibility;
			}

			if (visibility.contexts.indexOf('residential_lettings') !== -1) {
				visibility.preview_department = 'residential-lettings';
				return visibility;
			}

			if (visibility.contexts.indexOf('commercial_sales') !== -1) {
				visibility.preview_department = 'commercial';
				visibility.commercial_availability = 'for_sale';
				return visibility;
			}

			if (visibility.contexts.indexOf('commercial_lettings') !== -1) {
				visibility.preview_department = 'commercial';
				visibility.commercial_availability = 'to_rent';
			}

			return visibility;
		}

		function syncFieldVisibilityState(field) {
			var contexts;

			if (!field) {
				return;
			}

			field.settings = field.settings || {};
			contexts = normalizeDisplayContexts(field.settings.display_contexts || (field.visibility && field.visibility.contexts));
			field.settings.display_contexts = contexts;
			field.visibility = getVisibilityForContexts(contexts);
		}

		function markDirty() {
			state.dirty = true;
			editor.classList.add('is-dirty');
			if (activeHelpers && typeof activeHelpers.setEditorStatus === 'function') {
				activeHelpers.setEditorStatus(editor, labels.changed || 'Unsaved changes', 'changed');
			}
			queuePreview();
		}

		function queueOpeningField(fieldId) {
			if (openingTimer) {
				window.clearTimeout(openingTimer);
			}

			openingFieldId = fieldId || '';

			if (!openingFieldId) {
				openingTimer = null;
				return;
			}

			openingTimer = window.setTimeout(function () {
				if (openingFieldId === fieldId) {
					var item = getActiveFieldItem(fieldId);
					var panel = item ? item.querySelector('.ph-search-form-builder-settings.is-opening') : null;

					if (panel) {
						panel.classList.remove('is-opening');
					}
					openingFieldId = '';
					scheduleBuilderGroupBodyRefresh();
				}
				openingTimer = null;
			}, 210);
		}

		function queueClosingField(fieldId) {
			if (closingTimer) {
				window.clearTimeout(closingTimer);
			}

			closingFieldId = fieldId || '';

			if (!closingFieldId) {
				closingTimer = null;
				return;
			}

			closingTimer = window.setTimeout(function () {
				if (closingFieldId === fieldId) {
					closingFieldId = '';
					render();
				}
				closingTimer = null;
			}, 210);
		}

		function selectField(fieldId) {
			var previousFieldId = state.selectedId;

			if (previousFieldId === fieldId) {
				state.selectedId = '';
				queueOpeningField('');
				queueClosingField(fieldId);
				render();
				return;
			}

			state.selectedId = fieldId;

			if (closingFieldId === fieldId) {
				queueClosingField('');
			}

			if (previousFieldId) {
				queueClosingField(previousFieldId);
			}

			queueOpeningField(fieldId);
			render();
		}

		function getActiveFieldItem(fieldId) {
			var items = root.querySelectorAll('[data-ph-search-form-field]');
			var i;

			for (i = 0; i < items.length; i++) {
				if (items[i].getAttribute('data-ph-search-form-field') === fieldId) {
					return items[i];
				}
			}

			return null;
		}

		function focusFieldHandle(fieldId) {
			var item = getActiveFieldItem(fieldId);
			var handle = item ? item.querySelector('[data-ph-search-form-drag-handle]') : null;

			if (handle) {
				handle.focus();
			}
		}

		function moveField(fieldId, direction, shouldFocusHandle) {
			var index = state.active.findIndex(function (field) { return field.id === fieldId; });
			var target = index + direction;
			var field;

			if (index < 0 || target < 0 || target >= state.active.length) {
				return;
			}

			field = state.active.splice(index, 1)[0];
			state.active.splice(target, 0, field);
			state.selectedId = fieldId;
			if (shouldFocusHandle) {
				pendingHandleFocusId = fieldId;
			}
			markDirty();
			render();
		}

		function reorderField(fieldId, targetFieldId, placement) {
			var originalOrder = state.active.map(function (field) { return field.id; }).join('|');
			var sourceIndex = state.active.findIndex(function (field) { return field.id === fieldId; });
			var targetIndex = state.active.findIndex(function (field) { return field.id === targetFieldId; });
			var field;
			var nextOrder;

			if (sourceIndex < 0 || targetIndex < 0 || fieldId === targetFieldId) {
				return;
			}

			field = state.active.splice(sourceIndex, 1)[0];
			if (sourceIndex < targetIndex) {
				targetIndex--;
			}
			if (placement === 'after') {
				targetIndex++;
			}
			targetIndex = Math.max(0, Math.min(targetIndex, state.active.length));
			state.active.splice(targetIndex, 0, field);

			nextOrder = state.active.map(function (activeField) { return activeField.id; }).join('|');
			if (nextOrder === originalOrder) {
				return;
			}

			state.selectedId = fieldId;
			markDirty();
			render();
		}

		function getDropPlacement(event, item) {
			var rect = item.getBoundingClientRect();

			return event.clientY > rect.top + (rect.height / 2) ? 'after' : 'before';
		}

		function clearDropTargets(includeDragging) {
			root.querySelectorAll('.is-drop-before, .is-drop-after, .is-dragging').forEach(function (item) {
				item.classList.remove('is-drop-before', 'is-drop-after');
				if (includeDragging) {
					item.classList.remove('is-dragging');
				}
			});
		}

		function removeField(fieldId) {
			var index = state.active.findIndex(function (field) { return field.id === fieldId; });
			var field;

			if (index < 0) {
				return;
			}

			field = state.active.splice(index, 1)[0];
			state.inactive.push(field);
			if (state.selectedId === fieldId) {
				state.selectedId = state.active.length ? state.active[Math.max(0, index - 1)].id : '';
			}
			if (openingFieldId === fieldId) {
				queueOpeningField('');
			}
			if (closingFieldId === fieldId) {
				queueClosingField('');
			}
			markDirty();
			render();
		}

		function addField(fieldId) {
			var index = state.inactive.findIndex(function (field) { return field.id === fieldId; });
			var field;

			if (index < 0) {
				return;
			}

			field = state.inactive.splice(index, 1)[0];
			state.active.push(field);
			state.selectedId = field.id;
			queueOpeningField(field.id);
			markDirty();
			render();
		}

		function updateSelectedSetting(key, value) {
			var field = getSearchFormFieldById(state, state.selectedId);

			if (!field) {
				return;
			}

			field.settings = field.settings || {};
			if (key === 'display_contexts') {
				field.settings[key] = normalizeDisplayContexts(value);
				field.visibility = getVisibilityForContexts(field.settings[key]);
			} else {
				field.settings[key] = value;
			}

			if (key === 'label' && value) {
				field.title = value;
			}

			markDirty();
			render();
		}

		function queuePreview() {
			if (previewTimer) {
				window.clearTimeout(previewTimer);
			}

			previewTimer = window.setTimeout(function () {
				refreshPreview();
			}, 450);
		}

		function refreshPreview() {
			var sequence = ++previewSequence;

			state.previewing = true;
			setBuilderStatus(labels.loading || 'Loading...', 'saving');

			searchFormRequest(searchFormConfig.previewAction, searchFormConfig, buildSearchFormPayload(state)).then(function (data) {
				if (sequence !== previewSequence) {
					return;
				}

				replaceSearchFormPreview(data.html || '', searchFormConfig);
				syncSearchFormPreviewSelection();
				state.previewing = false;
				setBuilderStatus(state.dirty ? (labels.changed || 'Unsaved changes') : (labels.ready || 'Ready'), state.dirty ? 'changed' : 'ready');
			}).catch(function (error) {
				if (sequence !== previewSequence) {
					return;
				}

				state.previewing = false;
				setBuilderStatus(error.message || (labels.error || 'Could not save'), 'error');
			});
		}

		function save() {
			if (!state.dirty) {
				return Promise.resolve();
			}

			if (previewTimer) {
				window.clearTimeout(previewTimer);
				previewTimer = null;
			}

			previewSequence++;
			state.previewing = false;
			setBuilderStatus(labels.saving || 'Saving...', 'saving');

			return searchFormRequest(
				searchFormConfig.saveAction,
				searchFormConfig,
				buildSearchFormPayload(state),
				{ base_hash: state.baseHash || '' }
			).then(function (data) {
				if (data.editor) {
					searchFormConfig.active = data.editor.active || searchFormConfig.active;
					searchFormConfig.inactive = data.editor.inactive || searchFormConfig.inactive;
					searchFormConfig.baseHash = data.editor.baseHash || searchFormConfig.baseHash;
					searchFormConfig.visibilityContexts = data.editor.visibilityContexts || searchFormConfig.visibilityContexts;
					searchFormConfig.visibilityChoices = data.editor.visibilityChoices || searchFormConfig.visibilityChoices;
					state.visibilityContexts = data.editor.visibilityContexts || state.visibilityContexts;
					state.visibilityChoices = normalizeVisibilityChoices(data.editor.visibilityChoices || state.visibilityChoices);
					state.active = (data.editor.active || state.active).map(cloneSearchFormField);
					state.inactive = (data.editor.inactive || state.inactive).map(cloneSearchFormField);
					state.baseHash = data.editor.baseHash || state.baseHash;
				}

				if (data.html) {
					replaceSearchFormPreview(data.html, searchFormConfig);
					syncSearchFormPreviewSelection();
				}

				state.dirty = false;
				setBuilderStatus(labels.saved || 'Saved', 'saved');
				render();
			});
		}

		function createButton(label, action, disabled) {
			var button = document.createElement('button');

			button.type = 'button';
			button.textContent = label;
			button.setAttribute('data-ph-search-form-action', action);
			if (disabled) {
				button.disabled = true;
			}

			return button;
		}

		function createIcon(name) {
			var icon = document.createElement('span');
			var icons = {
				move: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2l3.5 3.5h-2.4v5.4h5.4V8.5L22 12l-3.5 3.5v-2.4h-5.4v5.4h2.4L12 22l-3.5-3.5h2.4v-5.4H5.5v2.4L2 12l3.5-3.5v2.4h5.4V5.5H8.5L12 2z"></path></svg>',
				remove: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" data-ph-search-form-icon="remove"><path d="M3 6h18"></path><path d="M8 6V4c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v2"></path><path d="M19 6l-1 14c-.1 1.1-1 2-2.1 2H8.1c-1.1 0-2-.9-2.1-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>'
			};

			icon.className = 'ph-search-form-builder-icon ph-search-form-builder-icon-' + name;
			icon.innerHTML = icons[name] || '';

			return icon;
		}

		function createIconButton(label, action, iconName, disabled) {
			var button = document.createElement('button');

			button.type = 'button';
			button.className = 'ph-search-form-builder-icon-button';
			button.setAttribute('aria-label', label);
			button.setAttribute('title', label);
			button.setAttribute('data-ph-search-form-action', action);
			button.appendChild(createIcon(iconName));
			if (disabled) {
				button.disabled = true;
			}

			return button;
		}

		function renderActiveFields(container) {
			var list = document.createElement('div');

			list.className = 'ph-search-form-builder-list';
			list.setAttribute('data-ph-search-form-active-list', '');

			if (!state.active.length) {
				var empty = document.createElement('p');
				empty.className = 'ph-search-form-builder-empty';
				empty.textContent = 'No active fields';
				list.appendChild(empty);
			}

			state.active.forEach(function (field, index) {
				var item = document.createElement('div');
				var handle = document.createElement('button');
				var title = document.createElement('button');
				var titleLabel = document.createElement('span');
				var actions = document.createElement('div');
				var titleText = field.title || field.id;
				var visibility = field.visibility || {};

				item.className = 'ph-search-form-builder-item';
				item.classList.toggle('is-active', field.id === state.selectedId);
				item.setAttribute('data-ph-search-form-field', field.id);
				item.setAttribute('data-ph-search-form-index', String(index));

				handle.type = 'button';
				handle.className = 'ph-search-form-builder-drag-handle';
				handle.draggable = state.active.length > 1;
				handle.disabled = state.active.length < 2;
				handle.setAttribute('data-ph-search-form-drag-handle', '');
				handle.setAttribute('aria-label', 'Drag to reorder ' + titleText + '. Use arrow keys to move it.');
				handle.setAttribute('title', 'Drag to reorder ' + titleText);
				handle.appendChild(createIcon('move'));

				title.type = 'button';
				title.className = 'ph-search-form-builder-item-title';
				title.setAttribute('aria-expanded', field.id === state.selectedId ? 'true' : 'false');
				titleLabel.className = 'ph-search-form-builder-item-label';
				titleLabel.textContent = titleText;
				title.appendChild(titleLabel);
				if (visibility.label) {
					var visibilityLabel = document.createElement('span');
					visibilityLabel.className = 'ph-search-form-builder-item-visibility';
					visibilityLabel.textContent = 'Shown for ' + visibility.label;
					title.appendChild(visibilityLabel);
				}
				title.addEventListener('click', function () {
					selectField(field.id);
				});

				actions.className = 'ph-search-form-builder-item-actions';
				actions.appendChild(createIconButton('Remove ' + titleText, 'remove', 'remove', false));

				item.appendChild(handle);
				item.appendChild(title);
				item.appendChild(actions);
				if (field.id === state.selectedId || field.id === closingFieldId) {
					renderFieldSettings(
						item,
						field,
						field.id === closingFieldId && field.id !== state.selectedId,
						field.id === openingFieldId && field.id === state.selectedId
					);
				}
				list.appendChild(item);
			});

			container.appendChild(list);
		}

		function renderAvailableFields(container) {
			var groups = {};
			var wrapper = document.createElement('details');
			var summary = document.createElement('summary');
			var groupWrap = document.createElement('div');

			wrapper.className = 'ph-search-form-builder-add';
			summary.textContent = 'Add field';
			groupWrap.className = 'ph-search-form-builder-add-groups';

			state.inactive.forEach(function (field) {
				var category = field.category || 'core';
				if (!groups[category]) {
					groups[category] = [];
				}
				groups[category].push(field);
			});

			Object.keys(state.categories).forEach(function (category) {
				var fields = groups[category] || [];
				var group;
				var heading;

				if (!fields.length) {
					return;
				}

				group = document.createElement('div');
				heading = document.createElement('h4');
				heading.textContent = state.categories[category];
				group.appendChild(heading);

				fields.forEach(function (field) {
					var button = createButton(field.title || field.id, 'add', false);
					button.setAttribute('data-ph-search-form-add-field', field.id);
					group.appendChild(button);
				});

				groupWrap.appendChild(group);
			});

			if (!state.inactive.length) {
				var empty = document.createElement('p');
				empty.className = 'ph-search-form-builder-empty';
				empty.textContent = 'All available fields are active';
				groupWrap.appendChild(empty);
			}

			wrapper.appendChild(summary);
			wrapper.appendChild(groupWrap);
			container.appendChild(wrapper);
		}

		function appendSettingField(container, field, key, label, type) {
			var row = document.createElement('label');
			var span = document.createElement('span');
			var input = document.createElement(type === 'select' ? 'select' : 'input');

			row.className = 'ph-search-form-builder-setting';
			span.textContent = label;
			row.appendChild(span);

			if (type === 'checkbox') {
				input.type = 'checkbox';
				input.checked = !!field.settings[key];
			} else if (type === 'number') {
				input.type = 'number';
				input.value = field.settings[key] || '';
			} else {
				input.type = 'text';
				input.value = field.settings[key] || '';
			}

			input.addEventListener('change', function () {
				updateSelectedSetting(key, type === 'checkbox' ? input.checked : input.value);
			});

			row.appendChild(input);
			container.appendChild(row);

			return input;
		}

		function renderDisplayContextSettings(container, field) {
			var row = document.createElement('fieldset');
			var legend = document.createElement('legend');
			var choices = document.createElement('div');
			var contexts = normalizeDisplayContexts(field.settings.display_contexts || (field.visibility && field.visibility.contexts));
			var selectableContexts = getSelectableVisibilityContextKeys();

			row.className = 'ph-search-form-builder-setting ph-search-form-builder-visibility-setting';
			legend.textContent = 'Show for';
			choices.className = 'ph-search-form-builder-visibility-options';
			row.appendChild(legend);

			getVisibilityChoices().forEach(function (choice) {
				var label = document.createElement('label');
				var input = document.createElement('input');
				var text = document.createElement('span');

				input.type = 'checkbox';
				input.value = choice.id;
				input.checked = choice.contexts.every(function (context) {
					return contexts.indexOf(context) !== -1;
				});
				text.textContent = choice.label;

				input.addEventListener('change', function () {
					var selectedContexts = contexts.filter(function (context) {
						return selectableContexts.indexOf(context) === -1;
					});
					var selectedVisibleContexts = [];

					Array.prototype.slice.call(row.querySelectorAll('input[type="checkbox"]:checked')).forEach(function (checkbox) {
						var matchedChoice = getVisibilityChoices().filter(function (visibilityChoice) {
							return visibilityChoice.id === checkbox.value;
						})[0];

						if (!matchedChoice) {
							return;
						}

						matchedChoice.contexts.forEach(function (context) {
							if (selectedVisibleContexts.indexOf(context) === -1) {
								selectedVisibleContexts.push(context);
							}

							if (selectedContexts.indexOf(context) === -1) {
								selectedContexts.push(context);
							}
						});
					});

					if (!selectedVisibleContexts.length) {
						input.checked = true;
						return;
					}

					updateSelectedSetting('display_contexts', selectedContexts);
				});

				label.appendChild(input);
				label.appendChild(text);
				choices.appendChild(label);
			});

			row.appendChild(choices);
			container.appendChild(row);
		}

		function renderFieldSettings(container, selectedField, isClosing, isOpening) {
			var field = selectedField || getSearchFormFieldById(state, state.selectedId);
			var panel = document.createElement('div');
			var heading = document.createElement('h4');
			var advanced;
			var departmentType;
			var labelToggle;

			panel.className = 'ph-search-form-builder-settings';
			if (isClosing) {
				panel.classList.add('is-closing');
			}
			if (isOpening) {
				panel.classList.add('is-opening');
			}

			if (!field) {
				var prompt = document.createElement('p');
				prompt.className = 'ph-search-form-builder-empty';
				prompt.textContent = 'Select a field to edit its settings';
				panel.appendChild(prompt);
				container.appendChild(panel);
				return;
			}

			heading.textContent = field.title || field.id;
			panel.appendChild(heading);

			labelToggle = appendSettingField(panel, field, 'show_label', 'Show label', 'checkbox');
			appendSettingField(panel, field, 'label', 'Label', 'text');

			if (field.supports && field.supports.placeholder) {
				appendSettingField(panel, field, 'placeholder', 'Placeholder', 'text');
			}

			if (field.supports && field.supports.blank_option) {
				appendSettingField(panel, field, 'blank_option', 'Blank option', 'text');
			}

			renderDisplayContextSettings(panel, field);

			if (field.supports && field.supports.department_type) {
				var row = document.createElement('label');
				var span = document.createElement('span');
				departmentType = document.createElement('select');
				row.className = 'ph-search-form-builder-setting';
				span.textContent = 'Department style';
				row.appendChild(span);
				['radio', 'select'].forEach(function (value) {
					var option = document.createElement('option');
					option.value = value;
					option.textContent = value === 'radio' ? 'Radio buttons' : 'Dropdown';
					option.selected = (field.settings.type || field.type) === value;
					departmentType.appendChild(option);
				});
				departmentType.addEventListener('change', function () {
					updateSelectedSetting('type', departmentType.value);
				});
				row.appendChild(departmentType);
				panel.appendChild(row);
			}

			advanced = document.createElement('details');
			advanced.className = 'ph-search-form-builder-advanced';
			advanced.innerHTML = '<summary>Advanced field settings</summary>';

			if (field.supports && field.supports.slider) {
				appendSettingField(advanced, field, 'min', 'Minimum', 'number');
				appendSettingField(advanced, field, 'max', 'Maximum', 'number');
				appendSettingField(advanced, field, 'step', 'Step', 'number');
			}

			if (field.supports && field.supports.multiselect) {
				appendSettingField(advanced, field, 'multiselect', 'Multi-select', 'checkbox');
			}

			if (field.supports && field.supports.taxonomy_settings) {
				appendSettingField(advanced, field, 'parent_terms_only', 'Top-level terms only', 'checkbox');
				appendSettingField(advanced, field, 'hide_empty', 'Hide empty terms', 'checkbox');
				appendSettingField(advanced, field, 'dynamic_population', 'Cascading dropdowns', 'checkbox');
			}

			if (advanced.querySelector('.ph-search-form-builder-setting')) {
				panel.appendChild(advanced);
			}
			container.appendChild(panel);
		}

		function render() {
			var header = document.createElement('div');
			var title = document.createElement('strong');
			var link = document.createElement('a');
			var status = document.createElement('div');
			var body = document.createElement('div');

			root.innerHTML = '';

			header.className = 'ph-search-form-builder-header';
			title.textContent = 'Default search form';
			link.href = searchFormConfig.advancedUrl || '#';
			link.textContent = 'Advanced';
			link.target = '_blank';
			link.rel = 'noopener';
			header.appendChild(title);
			header.appendChild(link);

			status.className = 'ph-search-form-builder-status';
			status.setAttribute('data-ph-search-form-builder-status', '');

			body.className = 'ph-search-form-builder-body';

			root.appendChild(header);
			root.appendChild(status);
			root.appendChild(body);

			renderActiveFields(body);
			renderAvailableFields(body);

			setBuilderStatus(state.previewing ? (labels.loading || 'Loading...') : (state.dirty ? (labels.changed || 'Unsaved changes') : (labels.ready || 'Ready')), state.previewing ? 'saving' : (state.dirty ? 'changed' : 'ready'));
			refreshBuilderGroupBody();
			scheduleBuilderGroupBodyRefresh();
			syncSearchFormPreviewSelection();

			if (pendingHandleFocusId) {
				var focusId = pendingHandleFocusId;
				pendingHandleFocusId = '';
				if (window.requestAnimationFrame) {
					window.requestAnimationFrame(function () {
						focusFieldHandle(focusId);
					});
				} else {
					window.setTimeout(function () {
						focusFieldHandle(focusId);
					}, 0);
				}
			}
		}

		root.addEventListener('click', function (event) {
			var actionButton = event.target.closest('[data-ph-search-form-action]');
			var item;
			var fieldId;

			if (!actionButton) {
				return;
			}

			item = actionButton.closest('[data-ph-search-form-field]');
			fieldId = item ? item.getAttribute('data-ph-search-form-field') : actionButton.getAttribute('data-ph-search-form-add-field');

			if (!fieldId) {
				return;
			}

			if (actionButton.getAttribute('data-ph-search-form-action') === 'remove') {
				removeField(fieldId);
			}

			if (actionButton.getAttribute('data-ph-search-form-action') === 'add') {
				addField(fieldId);
			}
		});

		root.addEventListener('keydown', function (event) {
			var handle = event.target.closest('[data-ph-search-form-drag-handle]');
			var item;
			var fieldId;

			if (!handle) {
				return;
			}

			item = handle.closest('[data-ph-search-form-field]');
			fieldId = item ? item.getAttribute('data-ph-search-form-field') : '';

			if (!fieldId) {
				return;
			}

			if (event.key === 'ArrowUp') {
				event.preventDefault();
				moveField(fieldId, -1, true);
			}

			if (event.key === 'ArrowDown') {
				event.preventDefault();
				moveField(fieldId, 1, true);
			}
		});

		root.addEventListener('dragstart', function (event) {
			var handle = event.target.closest('[data-ph-search-form-drag-handle]');
			var item = handle ? handle.closest('[data-ph-search-form-field]') : null;

			if (!item || handle.disabled) {
				event.preventDefault();
				return;
			}

			draggedFieldId = item.getAttribute('data-ph-search-form-field') || '';
			if (!draggedFieldId) {
				event.preventDefault();
				return;
			}

			if (event.dataTransfer) {
				event.dataTransfer.effectAllowed = 'move';
				event.dataTransfer.setData('text/plain', draggedFieldId);
			}

			window.setTimeout(function () {
				item.classList.add('is-dragging');
			}, 0);
		});

		root.addEventListener('dragover', function (event) {
			var list = event.target.closest('[data-ph-search-form-active-list]');
			var item = event.target.closest('[data-ph-search-form-field]');
			var placement;

			if (!draggedFieldId || !list || !item || item.getAttribute('data-ph-search-form-field') === draggedFieldId) {
				return;
			}

			event.preventDefault();
			if (event.dataTransfer) {
				event.dataTransfer.dropEffect = 'move';
			}

			placement = getDropPlacement(event, item);
			clearDropTargets(false);
			item.classList.add(placement === 'after' ? 'is-drop-after' : 'is-drop-before');
		});

		root.addEventListener('dragleave', function (event) {
			var item = event.target.closest('[data-ph-search-form-field]');

			if (item && !item.contains(event.relatedTarget)) {
				item.classList.remove('is-drop-before', 'is-drop-after');
			}
		});

		root.addEventListener('drop', function (event) {
			var item = event.target.closest('[data-ph-search-form-field]');
			var targetFieldId = item ? item.getAttribute('data-ph-search-form-field') : '';
			var placement;

			if (!draggedFieldId || !targetFieldId || targetFieldId === draggedFieldId) {
				event.preventDefault();
				clearDropTargets(true);
				draggedFieldId = '';
				return;
			}

			event.preventDefault();
			placement = getDropPlacement(event, item);
			reorderField(draggedFieldId, targetFieldId, placement);
			clearDropTargets(true);
			draggedFieldId = '';
		});

		root.addEventListener('dragend', function () {
			clearDropTargets(true);
			draggedFieldId = '';
		});

		if (window.ResizeObserver) {
			builderResizeObserver = new window.ResizeObserver(function () {
				scheduleBuilderGroupBodyRefresh();
			});
			builderResizeObserver.observe(root);
		}

		state.active.forEach(syncFieldVisibilityState);
		state.inactive.forEach(syncFieldVisibilityState);
		state.selectedId = state.active.length ? state.active[0].id : '';
		render();

		return {
			isDirty: function () {
				return state.dirty;
			},
			save: save
		};
	}

	function init(config, editor, form, labels, helpers) {
		activeConfig = config || {};
		activeHelpers = helpers || {};

		return initSearchFormBuilder(editor, form, labels || {});
	}

	modules.searchFormBuilder = {
		buildPayload: buildSearchFormPayload,
		init: init
	};
}());
