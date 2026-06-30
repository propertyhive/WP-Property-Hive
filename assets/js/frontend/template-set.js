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
			// Storage can be unavailable in privacy modes; the current page still updates.
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
				{ id: 'search-form', label: 'Search form', controls: ['ph_search_form_builder'] },
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

		return window.fetch(config.ajaxUrl || '', {
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
		var searchFormConfig = config.searchFormEditor || {};
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
			refreshActiveEditorGroupBodyWithoutTransition(form.querySelector('[data-ph-template-editor-groups]'));
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
			setEditorStatus(editor, labels.changed || 'Unsaved changes', 'changed');
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
		var searchFormBuilder;

		if (!editor || !config.editorActive) {
			return;
		}

		form = editor.querySelector('[data-ph-template-editor-form]');
		labels = config.labels || {};

		if (!form) {
			return;
		}

		searchFormBuilder = initSearchFormBuilder(editor, form, labels);

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
					throw new Error(payload && payload.data && payload.data.message ? payload.data.message : (labels.error || 'Could not save'));
				}

				config.settings = payload.data && payload.data.settings ? payload.data.settings : config.settings;

				if (searchFormBuilder && searchFormBuilder.isDirty()) {
					return searchFormBuilder.save();
				}
			}).then(function () {
				editor.classList.remove('is-dirty');
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
}());
