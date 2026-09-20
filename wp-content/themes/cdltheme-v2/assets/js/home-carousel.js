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

		var prevBtns = qsa(section, '.cdl-carousel__prev .wp-block-button__link');
		if (prevBtns.length === 0) {
			prevBtns = qsa(section, '.cdl-carousel__prev');
		}
		var nextBtns = qsa(section, '.cdl-carousel__next .wp-block-button__link');
		if (nextBtns.length === 0) {
			nextBtns = qsa(section, '.cdl-carousel__next');
		}

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
			prevBtns.forEach(function (btn) {
				btn.setAttribute('aria-disabled', page <= 0 ? 'true' : 'false');
				if (page <= 0) {
					btn.setAttribute('tabindex', '-1');
				} else {
					btn.removeAttribute('tabindex');
				}
			});
			nextBtns.forEach(function (btn) {
				btn.setAttribute('aria-disabled', page >= maxP ? 'true' : 'false');
				if (page >= maxP) {
					btn.setAttribute('tabindex', '-1');
				} else {
					btn.removeAttribute('tabindex');
				}
			});
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

		var touchStartX = 0;
		var touchCurrentX = 0;
		var dragging = false;

		function pointerX(event) {
			if (event.touches && event.touches.length) {
				return event.touches[0].clientX;
			}
			if (event.changedTouches && event.changedTouches.length) {
				return event.changedTouches[0].clientX;
			}
			return 0;
		}

		function onTouchStart(event) {
			dragging = true;
			touchStartX = pointerX(event);
			touchCurrentX = touchStartX;
			track.style.transition = 'none';
		}

		function onTouchMove(event) {
			if (!dragging) {
				return;
			}
			touchCurrentX = pointerX(event);
			var delta = touchCurrentX - touchStartX;
			var offset = page * stepPx();
			track.style.transform = 'translate3d(' + (-offset + delta) + 'px,0,0)';
		}

		function onTouchEnd() {
			if (!dragging) {
				return;
			}
			dragging = false;
			track.style.transition = '';
			var delta = touchCurrentX - touchStartX;
			var threshold = stepPx() * 0.15;
			if (delta <= -threshold) {
				go(1);
			} else if (delta >= threshold) {
				go(-1);
			} else {
				updateTransform();
			}
		}

		applyLayout();
		updateTransform();
		updateButtons();

		prevBtns.forEach(function (btn) {
			btn.addEventListener('click', onPrev);
		});
		nextBtns.forEach(function (btn) {
			btn.addEventListener('click', onNext);
		});

		viewport.addEventListener('touchstart', onTouchStart, { passive: true });
		viewport.addEventListener('touchmove', onTouchMove, { passive: true });
		viewport.addEventListener('touchend', onTouchEnd);
		viewport.addEventListener('touchcancel', onTouchEnd);

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
