(function () {
	'use strict';

	var activeLightbox = null;
	var lastFocusedElement = null;
	var config = window.phTemplateSet || {};
	var galleryVariants = ['showcase', 'cinema', 'mosaic', 'editorial', 'strip'];
	var galleryVariantStorageKey = 'phTemplateGalleryVariant';

	function setActiveTab(gallery, activeTab) {
		var tabs = gallery.querySelectorAll('[data-ph-gallery-tab]');

		tabs.forEach(function (tab) {
			var isActive = tab.getAttribute('data-ph-gallery-tab') === activeTab;
			tab.classList.toggle('is-active', isActive);
			tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
		});
	}

	function showPanel(gallery, panelName) {
		var heroImage = gallery.querySelector('[data-ph-gallery-hero-image]');
		var photoTrigger = gallery.querySelector('[data-ph-gallery-open]');
		var panels = gallery.querySelectorAll('[data-ph-gallery-panel]');
		var showingPhotos = panelName === 'photos';

		if (heroImage) {
			heroImage.hidden = !showingPhotos;
		}

		if (photoTrigger) {
			photoTrigger.hidden = !showingPhotos;
		}

		panels.forEach(function (panel) {
			panel.hidden = panel.getAttribute('data-ph-gallery-panel') !== panelName;
		});

		gallery.classList.toggle('is-showing-panel', !showingPhotos);
		setActiveTab(gallery, panelName);
	}

	function setActiveThumb(gallery, activeThumb) {
		var thumbs = gallery.querySelectorAll('[data-ph-gallery-thumb]');

		thumbs.forEach(function (thumb) {
			var isActive = thumb === activeThumb;
			thumb.classList.toggle('is-active', isActive);

			if (isActive) {
				thumb.setAttribute('aria-current', 'true');
			} else {
				thumb.removeAttribute('aria-current');
			}
		});
	}

	function getGalleryImages(gallery) {
		return Array.prototype.slice.call(gallery.querySelectorAll('[data-ph-gallery-thumb]')).map(function (thumb) {
			var alt = thumb.getAttribute('data-alt') || '';

			return {
				alt: alt,
				caption: thumb.getAttribute('data-caption') || alt,
				src: thumb.getAttribute('data-src') || '',
				thumb: thumb
			};
		}).filter(function (image) {
			return image.src;
		});
	}

	function getActiveImageIndex(gallery) {
		var images = getGalleryImages(gallery);
		var activeThumb = gallery.querySelector('[data-ph-gallery-thumb].is-active');
		var index = images.findIndex(function (image) {
			return image.thumb === activeThumb;
		});

		return index >= 0 ? index : 0;
	}

	function setPhotoTriggerLabel(gallery, label) {
		var photoTrigger = gallery.querySelector('[data-ph-gallery-open]');

		if (!photoTrigger) {
			return;
		}

		photoTrigger.setAttribute('aria-label', 'Open larger photo: ' + label);
	}

	function showImage(gallery, thumb) {
		var heroImage = gallery.querySelector('[data-ph-gallery-hero-image]');
		var caption = gallery.querySelector('[data-ph-gallery-caption]');
		var src = thumb.getAttribute('data-src');
		var alt = thumb.getAttribute('data-alt') || '';
		var captionText = thumb.getAttribute('data-caption') || alt;

		if (!heroImage || !src) {
			return;
		}

		heroImage.src = src;
		heroImage.alt = alt;

		if (caption) {
			caption.textContent = captionText;
		}

		setPhotoTriggerLabel(gallery, captionText);
		setActiveThumb(gallery, thumb);
		showPanel(gallery, 'photos');
	}

	function getStoredGalleryVariant() {
		try {
			return window.localStorage.getItem(galleryVariantStorageKey);
		} catch (error) {
			return '';
		}
	}

	function storeGalleryVariant(variant) {
		try {
			window.localStorage.setItem(galleryVariantStorageKey, variant);
		} catch (error) {
			// Storage can be unavailable in privacy modes; the switcher still works for the current page.
		}
	}

	function setGalleryVariant(gallery, variant, persist) {
		var selectedVariant = galleryVariants.indexOf(variant) >= 0 ? variant : 'showcase';
		var buttons = gallery.querySelectorAll('[data-ph-gallery-variant]');

		galleryVariants.forEach(function (galleryVariant) {
			gallery.classList.toggle('ph-gallery-variant-' + galleryVariant, galleryVariant === selectedVariant);
		});

		gallery.setAttribute('data-ph-gallery-current-variant', selectedVariant);

		buttons.forEach(function (button) {
			var isActive = button.getAttribute('data-ph-gallery-variant') === selectedVariant;
			button.classList.toggle('is-active', isActive);
			button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});

		if (persist) {
			storeGalleryVariant(selectedVariant);
		}
	}

	function setAllGalleryVariants(variant, persist) {
		document.querySelectorAll('[data-ph-template-gallery]').forEach(function (gallery) {
			setGalleryVariant(gallery, variant, persist);
		});
	}

	function normaliseIndex(index, length) {
		if (length < 1) {
			return 0;
		}

		return (index + length) % length;
	}

	function createLightbox(gallery) {
		var lightbox;

		if (gallery.phTemplateLightbox) {
			return gallery.phTemplateLightbox;
		}

		lightbox = document.createElement('div');
		lightbox.className = 'ph-template-gallery-lightbox';
		lightbox.hidden = true;
		lightbox.setAttribute('aria-label', 'Property photos');
		lightbox.setAttribute('aria-modal', 'true');
		lightbox.setAttribute('role', 'dialog');
		lightbox.innerHTML = [
			'<div class="ph-template-gallery-lightbox-stage" role="document">',
			'<button type="button" class="ph-template-gallery-lightbox-close" data-ph-lightbox-close>Close</button>',
			'<button type="button" class="ph-template-gallery-lightbox-nav ph-template-gallery-lightbox-prev" data-ph-lightbox-prev aria-label="Previous photo"><span aria-hidden="true">&lt;</span></button>',
			'<figure class="ph-template-gallery-lightbox-frame">',
			'<img src="" alt="" data-ph-lightbox-image>',
			'<figcaption class="ph-template-gallery-lightbox-caption">',
			'<span data-ph-lightbox-caption></span>',
			'<span data-ph-lightbox-count></span>',
			'</figcaption>',
			'</figure>',
			'<button type="button" class="ph-template-gallery-lightbox-nav ph-template-gallery-lightbox-next" data-ph-lightbox-next aria-label="Next photo"><span aria-hidden="true">&gt;</span></button>',
			'</div>'
		].join('');

		lightbox.addEventListener('click', function (event) {
			if (event.target === lightbox) {
				closeLightbox();
			}
		});

		lightbox.querySelector('[data-ph-lightbox-close]').addEventListener('click', closeLightbox);
		lightbox.querySelector('[data-ph-lightbox-prev]').addEventListener('click', function () {
			moveLightbox(gallery, -1);
		});
		lightbox.querySelector('[data-ph-lightbox-next]').addEventListener('click', function () {
			moveLightbox(gallery, 1);
		});

		document.body.appendChild(lightbox);
		gallery.phTemplateLightbox = lightbox;

		return lightbox;
	}

	function updateLightbox(gallery, index) {
		var images = getGalleryImages(gallery);
		var lightbox = createLightbox(gallery);
		var safeIndex = normaliseIndex(index, images.length);
		var image = images[safeIndex];
		var lightboxImage = lightbox.querySelector('[data-ph-lightbox-image]');
		var caption = lightbox.querySelector('[data-ph-lightbox-caption]');
		var count = lightbox.querySelector('[data-ph-lightbox-count]');
		var navButtons = lightbox.querySelectorAll('[data-ph-lightbox-prev], [data-ph-lightbox-next]');

		if (!image || !lightboxImage) {
			return;
		}

		lightbox.phTemplateImageIndex = safeIndex;
		lightboxImage.src = image.src;
		lightboxImage.alt = image.alt;

		if (caption) {
			caption.textContent = image.caption;
		}

		if (count) {
			count.textContent = (safeIndex + 1) + ' / ' + images.length;
		}

		navButtons.forEach(function (button) {
			button.hidden = images.length < 2;
		});
	}

	function focusLightbox(lightbox) {
		var closeButton = lightbox.querySelector('[data-ph-lightbox-close]');

		if (!closeButton) {
			return;
		}

		try {
			closeButton.focus({ preventScroll: true });
		} catch (error) {
			closeButton.focus();
		}
	}

	function openLightbox(gallery, index) {
		var images = getGalleryImages(gallery);
		var lightbox;

		if (!images.length) {
			return;
		}

		if (activeLightbox) {
			closeLightbox();
		}

		lightbox = createLightbox(gallery);
		lastFocusedElement = document.activeElement;
		updateLightbox(gallery, index);
		lightbox.hidden = false;
		document.body.classList.add('ph-template-lightbox-open');
		activeLightbox = {
			gallery: gallery,
			lightbox: lightbox
		};
		focusLightbox(lightbox);
	}

	function closeLightbox() {
		if (!activeLightbox) {
			return;
		}

		activeLightbox.lightbox.hidden = true;
		document.body.classList.remove('ph-template-lightbox-open');
		activeLightbox = null;

		if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
			try {
				lastFocusedElement.focus({ preventScroll: true });
			} catch (error) {
				lastFocusedElement.focus();
			}
		}

		lastFocusedElement = null;
	}

	function moveLightbox(gallery, step) {
		var images = getGalleryImages(gallery);
		var lightbox = createLightbox(gallery);
		var nextIndex = normaliseIndex((lightbox.phTemplateImageIndex || 0) + step, images.length);

		if (!images[nextIndex]) {
			return;
		}

		showImage(gallery, images[nextIndex].thumb);
		updateLightbox(gallery, nextIndex);
	}

	function keepFocusInLightbox(event) {
		var focusable;
		var first;
		var last;

		if (!activeLightbox || event.key !== 'Tab') {
			return;
		}

		focusable = Array.prototype.slice.call(activeLightbox.lightbox.querySelectorAll('button:not([hidden])'));

		if (!focusable.length) {
			return;
		}

		first = focusable[0];
		last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	function initGallery(gallery) {
		var thumbs = gallery.querySelectorAll('[data-ph-gallery-thumb]');
		var tabs = gallery.querySelectorAll('[data-ph-gallery-tab]');
		var photoTrigger = gallery.querySelector('[data-ph-gallery-open]');
		var variantButtons = gallery.querySelectorAll('[data-ph-gallery-variant]');
		var storedVariant = (!config.editorActive && variantButtons.length) ? getStoredGalleryVariant() : '';

		setGalleryVariant(gallery, storedVariant || gallery.getAttribute('data-ph-gallery-current-variant') || 'showcase', false);

		thumbs.forEach(function (thumb) {
			thumb.addEventListener('click', function () {
				showImage(gallery, thumb);
			});
		});

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				showPanel(gallery, tab.getAttribute('data-ph-gallery-tab') || 'photos');
			});
		});

		if (photoTrigger) {
			photoTrigger.addEventListener('click', function () {
				openLightbox(gallery, getActiveImageIndex(gallery));
			});
		}

		variantButtons.forEach(function (button) {
			button.addEventListener('click', function () {
				setGalleryVariant(gallery, button.getAttribute('data-ph-gallery-variant'), true);
			});
		});
	}

	function parseCardGalleryImages(dataNode) {
		var images;

		try {
			images = JSON.parse(dataNode.textContent || '[]');
		} catch (error) {
			return [];
		}

		if (!Array.isArray(images)) {
			return [];
		}

		return images.filter(function (image) {
			return image && image.src;
		});
	}

	function setCardGalleryImage(card, images, index) {
		var safeIndex = normaliseIndex(index, images.length);
		var imageData = images[safeIndex];
		var image = card.querySelector('.thumbnail img');
		var count = card.querySelector('[data-ph-card-gallery-count]');

		if (!image || !imageData) {
			return;
		}

		card.phTemplateCardGalleryIndex = safeIndex;
		image.src = imageData.src;
		image.alt = imageData.alt || image.alt || '';

		if (count) {
			count.textContent = (safeIndex + 1) + ' / ' + images.length;
		}
	}

	function moveCardGallery(card, images, step) {
		setCardGalleryImage(card, images, (card.phTemplateCardGalleryIndex || 0) + step);
	}

	function initCardGallery(dataNode) {
		var card = dataNode.closest('.ph-template-card');
		var thumbnail = dataNode.closest('.thumbnail');
		var images = parseCardGalleryImages(dataNode);
		var controls;

		if (!card || !thumbnail || images.length < 2 || card.phTemplateCardGalleryReady) {
			return;
		}

		card.phTemplateCardGalleryReady = true;
		card.phTemplateCardGalleryIndex = 0;
		thumbnail.classList.add('has-ph-card-gallery');

		controls = document.createElement('div');
		controls.className = 'ph-template-card-gallery-controls';
		controls.innerHTML = [
			'<button type="button" class="ph-template-card-gallery-button ph-template-card-gallery-prev" data-ph-card-gallery-prev aria-label="Previous listing photo"><span aria-hidden="true">&lt;</span></button>',
			'<span class="ph-template-card-gallery-count" data-ph-card-gallery-count>1 / ' + images.length + '</span>',
			'<button type="button" class="ph-template-card-gallery-button ph-template-card-gallery-next" data-ph-card-gallery-next aria-label="Next listing photo"><span aria-hidden="true">&gt;</span></button>'
		].join('');

		controls.addEventListener('click', function (event) {
			var previous = event.target.closest('[data-ph-card-gallery-prev]');
			var next = event.target.closest('[data-ph-card-gallery-next]');

			if (!previous && !next) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();
			moveCardGallery(card, images, previous ? -1 : 1);
		});

		thumbnail.appendChild(controls);
		setCardGalleryImage(card, images, 0);
	}

	function getCardUrl(card) {
		var link = card.querySelector('.details h3 a, .thumbnail a, a.button');

		return link ? link.href : '';
	}

	function shouldIgnoreCardClick(event) {
		if (event.button && event.button !== 0) {
			return true;
		}

		return !!event.target.closest('a, button, input, select, textarea, label, [role="button"], [data-ph-card-gallery-prev], [data-ph-card-gallery-next]');
	}

	function initSearchCardLinks() {
		document.addEventListener('click', function (event) {
			var card;
			var url;

			if (shouldIgnoreCardClick(event)) {
				return;
			}

			card = event.target.closest('.ph-template-search ul.properties > li.ph-template-card');

			if (!card) {
				return;
			}

			url = getCardUrl(card);

			if (!url) {
				return;
			}

			if (event.metaKey || event.ctrlKey) {
				window.open(url, '_blank');
				return;
			}

			window.location.href = url;
		});
	}

	document.addEventListener('keydown', function (event) {
		if (!activeLightbox) {
			return;
		}

		if (event.key === 'Escape') {
			event.preventDefault();
			closeLightbox();
		} else if (event.key === 'ArrowLeft') {
			event.preventDefault();
			moveLightbox(activeLightbox.gallery, -1);
		} else if (event.key === 'ArrowRight') {
			event.preventDefault();
			moveLightbox(activeLightbox.gallery, 1);
		} else {
			keepFocusInLightbox(event);
		}
	});

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-ph-template-gallery]').forEach(initGallery);
		document.querySelectorAll('[data-ph-card-gallery-data]').forEach(initCardGallery);
		initSearchCardLinks();
		initTemplateEditor();
	});

	window.phTemplateSetGallery = {
		setVariant: setAllGalleryVariants
	};

	function setEditorStatus(editor, message, state) {
		var status = editor.querySelector('[data-ph-template-editor-status]');

		if (status) {
			status.textContent = message;
		}

		editor.setAttribute('data-ph-template-editor-state', state || 'ready');
	}

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
		document.querySelectorAll('[data-ph-template-gallery]').forEach(function (gallery) {
			var panel = gallery.querySelector('[data-ph-gallery-panel="' + panelName + '"]');

			if (panel && !panel.hidden) {
				showPanel(gallery, 'photos');
			}
		});
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

	var editorSidebarLayout = {
		active: { search: 'layout', detail: 'media' },
		groups: {
			search: [
				{ id: 'template', label: 'Template', controls: ['template_set_search_template'] },
				{ id: 'layout', label: 'Layout', controls: ['template_set_search_layout', 'template_set_search_grid_columns'] },
				{ id: 'card-appearance', label: 'Card appearance', controls: ['template_set_search_card_size', 'template_set_image_style'] },
				{ id: 'details', label: 'Details shown', controls: ['template_set_show_branch', 'template_set_show_badges'] }
			],
			detail: [
				{ id: 'template', label: 'Template', controls: ['template_set_detail_template'] },
				{ id: 'media', label: 'Media', controls: ['template_set_gallery_layout', 'template_set_show_floorplans', 'template_set_show_virtual_tours'] },
				{ id: 'enquiry', label: 'Enquiries', controls: ['template_set_button_style', 'template_set_contact_card_style', 'template_set_show_mobile_cta'] },
				{ id: 'recommended', label: 'Related properties', controls: ['template_set_show_recommended', 'template_set_recommended_count', 'template_set_recommended_layout', 'template_set_recommended_image_size'] }
			]
		}
	};

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
		var activeGroupId = layout.active && layout.active[context] ? layout.active[context] : groups[0].id;

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
					setActiveEditorGroup(organizer, isOpen ? '' : group.id);
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

	function renderEditorSidebarGroups(editor, form) {
		var currentOrganizer = form.querySelector('[data-ph-template-editor-groups]');
		var footer = form.querySelector('.ph-template-editor-footer');
		var context = editor.getAttribute('data-ph-template-editor-context') || 'search';
		var items = getEditorControlItems(form);
		var groups = getEditorLayoutGroups(editorSidebarLayout, context, items);
		var organizer;

		if (!groups.length) {
			return;
		}

		removePrefixedClass(editor, 'ph-template-editor-layout-');
		editor.classList.add('ph-template-editor-layout-compact-tabs');

		organizer = createEditorSidebarOrganizer('compact-tabs', editorSidebarLayout, context, groups, items);

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

	function initEditorSidebarGroups(editor, form) {
		renderEditorSidebarGroups(editor, form);

		window.addEventListener('resize', function () {
			var organizer = form.querySelector('[data-ph-template-editor-groups]');
			refreshActiveEditorGroupBody(organizer);
		});

		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(function () {
				refreshActiveEditorGroupBody(form.querySelector('[data-ph-template-editor-groups]'));
			});
		}
	}

	function applyEditorControl(control) {
		var name = control.name;
		var value = control.type === 'checkbox' ? (control.checked ? 'yes' : '') : control.value;

		if (control.type === 'radio' && !control.checked) {
			return;
		}

		if (name === 'template_set_gallery_layout') {
			setAllGalleryVariants(value, false);
			updateSegmentedControl(control);
		}

		if (name === 'template_set_button_style') {
			setBodyOption('ph-template-buttons-', value);
		}

		if (name === 'template_set_search_layout') {
			setSearchOption('ph-search-view-', value);
		}

		if (name === 'template_set_search_card_size') {
			setSearchOption('ph-search-card-size-', value);
		}

		if (name === 'template_set_search_grid_columns') {
			setSearchOption('ph-search-grid-columns-', value);
		}

		if (name === 'template_set_image_style') {
			setBodyOption('ph-template-images-', value);
		}

		if (name === 'template_set_contact_card_style') {
			setBodyOption('ph-template-contact-card-', value);
		}

		if (name === 'template_set_show_branch') {
			setBodyToggle('ph-template-show-branch', 'ph-template-hide-branch', value === 'yes');
		}

		if (name === 'template_set_show_badges') {
			setBodyToggle('ph-template-show-badges', 'ph-template-hide-badges', value === 'yes');
		}

		if (name === 'template_set_show_mobile_cta') {
			setBodyToggle('ph-template-show-mobile-cta', 'ph-template-hide-mobile-cta', value === 'yes');
		}

		if (name === 'template_set_show_floorplans') {
			setBodyToggle('ph-template-show-floorplans', 'ph-template-hide-floorplans', value === 'yes');

			if (value !== 'yes') {
				resetGalleryPanel('floorplan');
			}
		}

		if (name === 'template_set_show_virtual_tours') {
			setBodyToggle('ph-template-show-virtual-tours', 'ph-template-hide-virtual-tours', value === 'yes');

			if (value !== 'yes') {
				resetGalleryPanel('virtual-tour');
			}
		}

		if (name === 'template_set_show_recommended') {
			setBodyToggle('ph-template-show-recommended', 'ph-template-hide-recommended', value === 'yes');
		}

		if (name === 'template_set_recommended_count') {
			setBodyOption('ph-template-recommended-count-', value);
			applyRecommendedCount(value);
		}

		if (name === 'template_set_recommended_layout') {
			setBodyOption('ph-template-recommended-layout-', value);
		}

		if (name === 'template_set_recommended_image_size') {
			setBodyOption('ph-template-recommended-images-', value);
		}
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

	function initTemplateEditor() {
		var editor = document.querySelector('[data-ph-template-editor]');
		var form;
		var labels;

		if (!editor || !config.editorActive) {
			return;
		}

		form = editor.querySelector('[data-ph-template-editor-form]');
		labels = config.labels || {};

		if (!form) {
			return;
		}

		initEditorSidebarGroups(editor, form);

		form.querySelectorAll('[data-ph-template-editor-control]').forEach(function (control) {
			control.addEventListener('change', function () {
				if (maybeNavigateTemplatePreview(control)) {
					setEditorStatus(editor, labels.loading || 'Loading...', 'saving');
					return;
				}

				applyEditorControl(control);
				editor.classList.add('is-dirty');
				setEditorStatus(editor, labels.changed || 'Unsaved changes', 'changed');
			});

			if (control.type === 'color') {
				control.addEventListener('input', function () {
					applyEditorControl(control);
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
					throw new Error('Save failed');
				}

				config.settings = payload.data && payload.data.settings ? payload.data.settings : config.settings;
				editor.classList.remove('is-dirty');
				setEditorStatus(editor, labels.saved || 'Saved', 'saved');
			}).catch(function () {
				setEditorStatus(editor, labels.error || 'Could not save', 'error');
			}).finally(function () {
				if (saveButton) {
					saveButton.disabled = false;
				}
			});
		});
	}
}());
