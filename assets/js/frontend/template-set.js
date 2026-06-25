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

	function setTemplateColour(property, value) {
		var targets = [document.documentElement].concat(Array.prototype.slice.call(document.querySelectorAll('.ph-template-set, .ph-template-set-active')));

		targets.forEach(function (target) {
			target.style.setProperty(property, value);
		});
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

		if (name === 'template_set_brand_colour') {
			setTemplateColour('--ph-template-brand', value);
		}

		if (name === 'template_set_accent_colour') {
			setTemplateColour('--ph-template-accent', value);
		}

		if (name === 'template_set_button_style') {
			setBodyOption('ph-template-buttons-', value);
		}

		if (name === 'template_set_card_density') {
			setBodyOption('ph-template-density-', value);
		}

		if (name === 'template_set_image_style') {
			setBodyOption('ph-template-images-', value);
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

		form.querySelectorAll('[data-ph-template-editor-control]').forEach(function (control) {
			control.addEventListener('change', function () {
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
