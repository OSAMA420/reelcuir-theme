/* Reel Cuir custom scripts */
(function () {
	'use strict';

	/*
	 * Search overlay fix.
	 * Header (Elementor post 1043) ki inline script `.rc-search-overlay` dhoondti hai,
	 * lekin overlay container (9d1eeeb) par ye class nahi lagi. Is wajah se overlay ka
	 * apna search input bhi readonly ho jata tha aur kuch type nahi hota tha.
	 */
	var OVERLAY = '.elementor-location-header .elementor-element-9d1eeeb';

	function overlay() {
		return document.querySelector('.rc-search-overlay') || document.querySelector(OVERLAY);
	}

	function unlockOverlayInputs() {
		var o = overlay();
		if (!o) return null;
		o.classList.add('rc-search-overlay');
		var inputs = o.querySelectorAll('input[type=search], input[type=text]');
		inputs.forEach(function (i) {
			i.removeAttribute('readonly');
			i.style.cursor = '';
		});
		return inputs[0] || null;
	}

	// Header script overlay ke submit button ka click bhi preventDefault karti hai,
	// jis se Enter/submit par search nahi hoti. Document capture par rok do taake
	// woh handler chale hi nahi aur form normal submit ho.
	document.addEventListener('click', function (e) {
		var o = overlay();
		if (o && e.target.closest && o.contains(e.target) && e.target.closest('.kitify-search__submit')) {
			e.stopPropagation();
		}
	}, true);

	// Overlay khulte hi input unlock aur focus karo.
	var wasOpen = false;
	new MutationObserver(function () {
		var isOpen = document.body.classList.contains('rc-search-open');
		if (isOpen === wasOpen) return;
		wasOpen = isOpen;
		if (!isOpen) return;
		var input = unlockOverlayInputs();
		if (input) {
			setTimeout(function () {
				try { input.focus(); } catch (x) {}
			}, 150);
		}
	}).observe(document.body, { attributes: true, attributeFilter: ['class'] });

	// Header script 1.2s aur 3s par dobara readonly lagati hai, is liye baad mein bhi unlock karo.
	unlockOverlayInputs();
	setTimeout(unlockOverlayInputs, 1300);
	setTimeout(unlockOverlayInputs, 3100);
})();
