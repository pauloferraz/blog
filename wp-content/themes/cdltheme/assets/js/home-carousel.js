/**
 * Carrossel da home: N posts; 3 visíveis no desktop e 1 no mobile (≤781px).
 * Avança uma “página” (largura do viewport) de cada vez.
 */
(function () {
	'use strict';

	var MQ_MOBILE = '(max-width: 781px)';

	function qs(root, sel) {
		return root ? root.querySelector(sel) : null;
	}

	function qsa(root, sel) {
		return root ? Array.prototype.slice.call(root.querySelectorAll(sel)) : [];
	}

	function trackListItems(track) {
		var out = [];
		var ch = track.children;
		var i;
		for (i = 0; i < ch.length; i++) {
			if (ch[i].tagName === 'LI') {
				out.push(ch[i]);
			}
		}
		return out;
	}

	function initCarousel(root) {
		var section = root.closest('.cdl-carousel-section');
		if (!section) {
			return;
		}

		var viewport = qs(root, '.cdl-carousel__viewport');
		var track =
			qs(root, '.cdl-carousel__track') ||
			qs(root, 'ul.wp-block-post-template') ||
			qs(root, '.wp-block-post-template');

		if (!viewport || !track) {
			return;
		}

		var slideGroups = qsa(root, '.cdl-carousel__slide');
		if (slideGroups.length === 0) {
			return;
		}

		var items = trackListItems(track);
		if (items.length === 0) {
			items = slideGroups.map(function (g) {
				return g.closest('li') || g;
			});
		}

		var desktopVisible = parseInt(root.getAttribute('data-cdl-visible'), 10) || 3;
		var mq = window.matchMedia(MQ_MOBILE);

		function getVisible() {
			return mq.matches ? 1 : desktopVisible;
		}

		var prevBtn = qs(section, '.cdl-carousel__prev .wp-block-button__link') || qs(section, '.cdl-carousel__prev');
		var nextBtn = qs(section, '.cdl-carousel__next .wp-block-button__link') || qs(section, '.cdl-carousel__next');

		var page = 0;

		function gapPx() {
			var styles = window.getComputedStyle(track);
			var g = styles.columnGap || styles.gap || '0';
			var parsed = parseFloat(g);
			return isNaN(parsed) ? 0 : parsed;
		}

		function slideWidthPx() {
			var visible = getVisible();
			var w = viewport.getBoundingClientRect().width;
			var gap = gapPx();
			var totalGaps = Math.max(0, visible - 1) * gap;
			return (w - totalGaps) / visible;
		}

		function maxPage() {
			var visible = getVisible();
			return Math.max(0, Math.ceil(slideGroups.length / visible) - 1);
		}

		function applyLayout() {
			var visible = getVisible();
			var sw = slideWidthPx();
			var gap = gapPx();
			root.style.setProperty('--cdl-slide-width', sw + 'px');
			root.style.setProperty('--cdl-carousel-gap', gap ? gap + 'px' : '1.25rem');

			var i;
			for (i = 0; i < items.length; i++) {
				items[i].style.flexBasis = sw + 'px';
				items[i].style.width = sw + 'px';
				items[i].style.maxWidth = sw + 'px';
			}
		}

		function stepPx() {
			return viewport.getBoundingClientRect().width;
		}

		function updateTransform() {
			var offset = page * stepPx();
			track.style.transform = 'translate3d(' + -offset + 'px,0,0)';
		}

		function updateButtons() {
			var maxP = maxPage();
			if (prevBtn) {
				prevBtn.setAttribute('aria-disabled', page <= 0 ? 'true' : 'false');
				if (page <= 0) {
					prevBtn.setAttribute('tabindex', '-1');
				} else {
					prevBtn.removeAttribute('tabindex');
				}
			}
			if (nextBtn) {
				nextBtn.setAttribute('aria-disabled', page >= maxP ? 'true' : 'false');
				if (page >= maxP) {
					nextBtn.setAttribute('tabindex', '-1');
				} else {
					nextBtn.removeAttribute('tabindex');
				}
			}
		}

		function syncAfterResize() {
			applyLayout();
			page = Math.min(page, maxPage());
			updateTransform();
			updateButtons();
		}

		function go(delta) {
			var maxP = maxPage();
			page = Math.min(maxP, Math.max(0, page + delta));
			applyLayout();
			updateTransform();
			updateButtons();
		}

		function onPrev(event) {
			if (event) {
				event.preventDefault();
			}
			go(-1);
		}

		function onNext(event) {
			if (event) {
				event.preventDefault();
			}
			go(1);
		}

		function onMqChange() {
			page = Math.min(page, maxPage());
			syncAfterResize();
		}

		applyLayout();
		updateTransform();
		updateButtons();

		if (prevBtn) {
			prevBtn.addEventListener('click', onPrev);
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', onNext);
		}

		if (typeof mq.addEventListener === 'function') {
			mq.addEventListener('change', onMqChange);
		} else if (typeof mq.addListener === 'function') {
			mq.addListener(onMqChange);
		}

		if (typeof window.ResizeObserver === 'function') {
			var ro = new ResizeObserver(function () {
				syncAfterResize();
			});
			ro.observe(viewport);
		} else {
			window.addEventListener('resize', syncAfterResize);
		}
	}

	document.querySelectorAll('[data-cdl-carousel]').forEach(initCarousel);
})();
