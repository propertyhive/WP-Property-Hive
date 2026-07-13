(function () {
	'use strict';

	var modules = window.phTemplateSetModules = window.phTemplateSetModules || {};
	var activeEditorForm = null;
	var editorSidebarEventsReady = false;
	var fallbackEditorSidebarLayout = {
		active: { search: 'layout', detail: 'media' },
		groups: {
			search: [
				{ id: 'template', label: 'Template', controls: ['template_set_search_template'] },
				{ id: 'search-form', label: 'Search form', controls: ['ph_search_form_builder'] },
				{ id: 'layout', label: 'Layout', controls: ['template_set_search_layout', 'template_set_search_grid_columns'] },
				{ id: 'card-appearance', label: 'Card appearance', controls: ['template_set_search_card_size', 'template_set_image_style'] },
				{ id: 'details', label: 'Details shown', controls: ['template_set_show_branch', 'template_set_show_badges'] }
			],
			detail: [
				{ id: 'template', label: 'Template', controls: ['template_set_detail_template'] },
				{ id: 'media', label: 'Media', controls: ['template_set_gallery_layout', 'template_set_cinema_card_position', 'template_set_editorial_show_brief', 'template_set_show_floorplans', 'template_set_show_virtual_tours'] },
				{ id: 'enquiry', label: 'Enquiries', controls: ['template_set_button_style', 'template_set_contact_card_style', 'template_set_show_mobile_cta'] },
				{ id: 'recommended', label: 'Related properties', controls: ['template_set_show_recommended', 'template_set_recommended_count', 'template_set_recommended_layout', 'template_set_recommended_image_size'] }
			]
		}
	};

	function removePrefixedClass(element, prefix) {
		Array.prototype.slice.call(element.classList).forEach(function (className) {
			if (className.indexOf(prefix) === 0) {
				element.classList.remove(className);
			}
		});
	}

	function normalizeEditorSidebarLayout(layout) {
		if (!layout || !layout.groups || !layout.groups.search || !layout.groups.detail) {
			return fallbackEditorSidebarLayout;
		}

		return layout;
	}

	function getEditorGroupStorageKey(context) {
		return 'propertyhive-template-editor-active-group-' + (context || 'search');
	}

	function getStoredEditorGroup(context) {
		try {
			return window.sessionStorage.getItem(getEditorGroupStorageKey(context)) || '';
		} catch (error) {
			return '';
		}
	}

	function storeEditorGroup(context, groupId) {
		try {
			if (groupId) {
				window.sessionStorage.setItem(getEditorGroupStorageKey(context), groupId);
			} else {
				window.sessionStorage.removeItem(getEditorGroupStorageKey(context));
			}
		} catch (error) {
			// Storage can be unavailable in privacy modes; the in-page state still works.
		}
	}

	function getEditorControlItem(control) {
		if (!control || !control.name || control.type === 'hidden') {
			return null;
		}

		if (control.name === 'template_set_gallery_layout') {
			return control.closest('.ph-template-editor-field') || control.closest('.ph-template-editor-segmented');
		}

		return control.closest('.ph-template-editor-field, .ph-template-editor-toggle');
	}

	function getEditorControlItems(form) {
		var items = {};

		form.querySelectorAll('[data-ph-template-editor-panel-control]').forEach(function (item) {
			var name = item.getAttribute('data-ph-template-editor-panel-control');

			if (name && !items[name]) {
				items[name] = item;
			}
		});

		form.querySelectorAll('[data-ph-template-editor-control]').forEach(function (control) {
			var item;

			if (!control.name || items[control.name]) {
				return;
			}

			item = getEditorControlItem(control);

			if (!item) {
				return;
			}

			item.setAttribute('data-ph-template-editor-control-item', control.name);
			items[control.name] = item;
		});

		return items;
	}

	function getEditorLayoutGroups(layout, context, items) {
		return (layout.groups[context] || []).map(function (group) {
			var controls = group.controls.filter(function (controlName) {
				return !!items[controlName];
			});

			return {
				id: group.id,
				label: group.label,
				controls: controls
			};
		}).filter(function (group) {
			return group.controls.length > 0;
		});
	}

	function createEditorGroupPanel(layoutId, group, items) {
		var panel = document.createElement('section');
		var button = createEditorGroupButton(layoutId, group);
		var body = document.createElement('div');
		var content = document.createElement('div');

		panel.className = 'ph-template-editor-group';
		panel.setAttribute('data-ph-template-editor-group', group.id);

		body.className = 'ph-template-editor-group-body';
		body.id = 'ph-template-editor-' + layoutId + '-' + group.id;
		body.setAttribute('data-ph-template-editor-group-body', group.id);

		content.className = 'ph-template-editor-group-content';

		group.controls.forEach(function (controlName) {
			content.appendChild(items[controlName]);
		});

		body.appendChild(content);
		panel.appendChild(button);
		panel.appendChild(body);

		return panel;
	}

	function createEditorGroupButton(layoutId, group) {
		var button = document.createElement('button');

		button.type = 'button';
		button.className = 'ph-template-editor-group-toggle';
		button.setAttribute('data-ph-template-editor-group-toggle', group.id);
		button.setAttribute('aria-controls', 'ph-template-editor-' + layoutId + '-' + group.id);
		button.setAttribute('aria-expanded', 'false');
		button.textContent = group.label;

		return button;
	}

	function updateEditorGroupBodyHeight(body) {
		if (!body) {
			return;
		}

		body.style.setProperty('--ph-template-editor-group-height', body.scrollHeight + 'px');
	}

	function updateEditorGroupBodyHeightWithoutTransition(body) {
		if (!body) {
			return;
		}

		body.classList.add('is-resizing-content');
		updateEditorGroupBodyHeight(body);
		window.requestAnimationFrame(function () {
			body.classList.remove('is-resizing-content');
		});
	}

	function setEditorGroupBodyOpen(body, isActive) {
		if (!body) {
			return;
		}

		if (isActive) {
			updateEditorGroupBodyHeight(body);
			body.removeAttribute('aria-hidden');
			if ('inert' in body) {
				body.inert = false;
			}
			return;
		}

		body.style.setProperty('--ph-template-editor-group-height', '0px');
		body.setAttribute('aria-hidden', 'true');
		if ('inert' in body) {
			body.inert = true;
		}
	}

	function refreshActiveEditorGroupBody(organizer) {
		var activeBody = organizer ? organizer.querySelector('.ph-template-editor-group.is-active [data-ph-template-editor-group-body]') : null;

		updateEditorGroupBodyHeight(activeBody);
	}

	function refreshActiveEditorGroupBodyWithoutTransition(organizer) {
		var activeBody = organizer ? organizer.querySelector('.ph-template-editor-group.is-active [data-ph-template-editor-group-body]') : null;

		updateEditorGroupBodyHeightWithoutTransition(activeBody);
	}

	function refreshActiveEditorGroupBodyOnNextFrame(organizer) {
		window.requestAnimationFrame(function () {
			refreshActiveEditorGroupBody(organizer);
		});
	}

	function setActiveEditorGroup(organizer, activeGroupId) {
		organizer.querySelectorAll('[data-ph-template-editor-group]').forEach(function (group) {
			var groupId = group.getAttribute('data-ph-template-editor-group');
			var button = organizer.querySelector('[data-ph-template-editor-group-toggle="' + groupId + '"]');
			var body = group.querySelector('[data-ph-template-editor-group-body]');
			var isActive = groupId === activeGroupId;

			group.classList.toggle('is-active', isActive);
			setEditorGroupBodyOpen(body, isActive);

			if (button) {
				button.classList.toggle('is-active', isActive);
				button.setAttribute('aria-expanded', isActive ? 'true' : 'false');
			}
		});
	}

	function createEditorSidebarOrganizer(layoutId, layout, context, groups, items) {
		var organizer = document.createElement('div');
		var storedGroupId = getStoredEditorGroup(context);
		var activeGroupId = storedGroupId || (layout.active && layout.active[context] ? layout.active[context] : groups[0].id);

		if (!groups.some(function (group) { return group.id === activeGroupId; })) {
			activeGroupId = groups[0].id;
		}

		organizer.className = 'ph-template-editor-groups ph-template-editor-groups-compact-tabs';
		organizer.setAttribute('data-ph-template-editor-groups', layoutId);
		organizer.setAttribute('data-ph-template-editor-active-group', activeGroupId);

		groups.forEach(function (group) {
			var panel = createEditorGroupPanel(layoutId, group, items);
			var button = panel.querySelector('[data-ph-template-editor-group-toggle]');

			if (button) {
				button.addEventListener('click', function () {
					var isOpen = panel.classList.contains('is-active');
					var nextGroupId = isOpen ? '' : group.id;
					setActiveEditorGroup(organizer, nextGroupId);
					organizer.setAttribute('data-ph-template-editor-active-group', nextGroupId);
					storeEditorGroup(context, nextGroupId);
					refreshActiveEditorGroupBodyOnNextFrame(organizer);
				});
			}

			organizer.appendChild(panel);
		});

		setActiveEditorGroup(organizer, activeGroupId);
		refreshActiveEditorGroupBody(organizer);

		return organizer;
	}

	function hideEditorSourceSections(form) {
		form.querySelectorAll('.ph-template-editor-source-section').forEach(function (section) {
			section.hidden = true;
			section.setAttribute('aria-hidden', 'true');
		});
	}

	function renderEditorSidebarGroups(editor, form, layout) {
		layout = normalizeEditorSidebarLayout(layout);

		var currentOrganizer = form.querySelector('[data-ph-template-editor-groups]');
		var footer = form.querySelector('.ph-template-editor-footer');
		var context = editor.getAttribute('data-ph-template-editor-context') || 'search';
		var items = getEditorControlItems(form);
		var groups = getEditorLayoutGroups(layout, context, items);
		var organizer;

		if (!groups.length) {
			return;
		}

		removePrefixedClass(editor, 'ph-template-editor-layout-');
		editor.classList.add('ph-template-editor-layout-compact-tabs');

		organizer = createEditorSidebarOrganizer('compact-tabs', layout, context, groups, items);

		if (footer && footer.parentNode) {
			footer.parentNode.insertBefore(organizer, footer);
		} else {
			form.appendChild(organizer);
		}

		setActiveEditorGroup(organizer, organizer.getAttribute('data-ph-template-editor-active-group') || '');
		refreshActiveEditorGroupBody(organizer);

		window.requestAnimationFrame(function () {
			refreshActiveEditorGroupBody(organizer);
			organizer.classList.add('is-ready');
		});

		if (currentOrganizer && currentOrganizer.parentNode) {
			currentOrganizer.parentNode.removeChild(currentOrganizer);
		}

		hideEditorSourceSections(form);
	}

	function initEditorSidebarGroups(editor, form, layout) {
		activeEditorForm = form;
		renderEditorSidebarGroups(editor, form, layout);

		if (editorSidebarEventsReady) {
			return;
		}

		editorSidebarEventsReady = true;

		window.addEventListener('resize', function () {
			var organizer = activeEditorForm ? activeEditorForm.querySelector('[data-ph-template-editor-groups]') : null;
			refreshActiveEditorGroupBody(organizer);
		});

		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(function () {
				var organizer = activeEditorForm ? activeEditorForm.querySelector('[data-ph-template-editor-groups]') : null;
				refreshActiveEditorGroupBody(organizer);
			});
		}
	}

	function refreshActiveGroupBody(form) {
		refreshActiveEditorGroupBody(form ? form.querySelector('[data-ph-template-editor-groups]') : null);
	}

	function refreshActiveGroupBodyWithoutTransition(form) {
		refreshActiveEditorGroupBodyWithoutTransition(form ? form.querySelector('[data-ph-template-editor-groups]') : null);
	}

	modules.editorSidebar = {
		fallbackLayout: fallbackEditorSidebarLayout,
		init: initEditorSidebarGroups,
		refreshActiveGroupBody: refreshActiveGroupBody,
		refreshActiveGroupBodyWithoutTransition: refreshActiveGroupBodyWithoutTransition
	};
}());
