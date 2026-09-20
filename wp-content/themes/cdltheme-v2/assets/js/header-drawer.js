/**
 * Drawer do menu mobile do cabeçalho CDL.
 */
(function () {
	'use strict';

	var DRAWER_DURATION = 320;

	function setupDrawer() {
		var toggle = document.querySelector('.cdl-header__menu-toggle');
		var drawer = document.getElementById('cdl-header-drawer');
		if (!toggle || !drawer) {
			return;
		}

		var closeBtn = drawer.querySelector('.cdl-header__drawer-close');
		var backdrop = drawer.querySelector('.cdl-header__drawer-backdrop');
		var panel = drawer.querySelector('.cdl-header__drawer-panel');
		var closing = false;
		var lastFocus = null;

		function isOpen() {
			return toggle.getAttribute('aria-expanded') === 'true';
		}

		function openDrawer() {
			if (isOpen() || closing) {
				return;
			}

			lastFocus = document.activeElement;
			toggle.setAttribute('aria-expanded', 'true');
			drawer.setAttribute('aria-hidden', 'false');
			document.body.classList.add('cdl-drawer-open');

			window.requestAnimationFrame(function () {
				drawer.classList.add('cdl-drawer--active');
			});

			if (closeBtn) {
				closeBtn.focus();
			}
		}

		function finishClose() {
			toggle.setAttribute('aria-expanded', 'false');
			drawer.setAttribute('aria-hidden', 'true');
			drawer.classList.remove('cdl-drawer--active');
			document.body.classList.remove('cdl-drawer-open');
			closing = false;

			if (lastFocus && typeof lastFocus.focus === 'function') {
				lastFocus.focus();
			}
		}

		function closeDrawer() {
			if (!isOpen() || closing) {
				return;
			}

			closing = true;
			drawer.classList.remove('cdl-drawer--active');
			document.body.classList.remove('cdl-drawer-open');
			window.setTimeout(finishClose, DRAWER_DURATION);
		}

		toggle.addEventListener('click', function () {
			if (isOpen()) {
				closeDrawer();
			} else {
				openDrawer();
			}
		});

		if (closeBtn) {
			closeBtn.addEventListener('click', closeDrawer);
		}

		if (backdrop) {
			backdrop.addEventListener('click', closeDrawer);
		}

		drawer.querySelectorAll('.cdl-header__nav-list--drawer a').forEach(function (link) {
			link.addEventListener('click', closeDrawer);
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && isOpen()) {
				closeDrawer();
			}

			if (event.key === 'Tab' && isOpen() && panel) {
				var focusable = panel.querySelectorAll(
					'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
				);
				if (!focusable.length) {
					return;
				}

				var first = focusable[0];
				var last = focusable[focusable.length - 1];

				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupDrawer);
	} else {
		setupDrawer();
	}
})();
