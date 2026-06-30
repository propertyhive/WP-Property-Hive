(function () {
	'use strict';

	var modules = window.phTemplateSetModules = window.phTemplateSetModules || {};
	var activeLightbox = null;
	var lastFocusedElement = null;
	var config = {};
	var searchCardLinksReady = false;
	var keyboardControlsReady = false;
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
		if (searchCardLinksReady) {
			return;
		}

		searchCardLinksReady = true;
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

	function resetPanel(panelName) {
		document.querySelectorAll('[data-ph-template-gallery]').forEach(function (gallery) {
			var panel = gallery.querySelector('[data-ph-gallery-panel="' + panelName + '"]');

			if (panel && !panel.hidden) {
				showPanel(gallery, 'photos');
			}
		});
	}

	function initKeyboardControls() {
		if (keyboardControlsReady) {
			return;
		}

		keyboardControlsReady = true;
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
	}

	function init(nextConfig) {
		config = nextConfig || {};
		document.querySelectorAll('[data-ph-template-gallery]').forEach(initGallery);
		document.querySelectorAll('[data-ph-card-gallery-data]').forEach(initCardGallery);
		initSearchCardLinks();
		initKeyboardControls();
	}

	modules.gallery = {
		init: init,
		resetPanel: resetPanel,
		setVariant: setAllGalleryVariants
	};

	window.phTemplateSetGallery = {
		setVariant: setAllGalleryVariants
	};
}());
