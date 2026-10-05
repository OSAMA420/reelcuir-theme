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

/*
 * Header menu: khaali categories ke links chhupao (list functions.php se aati hai).
 * Sab se kareeb wala item chhupta hai: icon-list item, button/heading widget,
 * ya top-level menu item. Is liye dropdown ka koi andar wala link poora menu nahi chhupata.
 */
(function () {
	'use strict';

	var empty = window.rcEmptyCats || [];
	if (!empty.length) return;

	function slugOf(href) {
		var m = /\/product-category\/(.+?)\/?(?:[?#].*)?$/.exec(href || '');
		if (!m) return null;
		var parts = m[1].split('/');
		return parts[parts.length - 1];
	}

	function hideEmpty() {
		document.querySelectorAll('.elementor-location-header a[href*="/product-category/"]').forEach(function (a) {
			if (empty.indexOf(slugOf(a.getAttribute('href'))) === -1) return;
			var item = a.closest('.elementor-icon-list-item, .elementor-widget, .e-n-menu-item, .menu-item');
			(item || a).style.display = 'none';
			(item || a).setAttribute('data-rc-empty-cat', '1');
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', hideEmpty);
	} else {
		hideEmpty();
	}
})();

/*
 * Checkout: live feedback.
 * - Sahi bhare field par tick (data-rc-valid)
 * - Step ke saare zaroori fields bhar jayen to number ki jagah ✓ (data-rc-done)
 * - Place Order ke neeche trust strip
 * Checkout React se banta hai aur re-render hota rehta hai, is liye classes ki jagah
 * data attributes use kiye hain aur MutationObserver se dobara lagate hain.
 */
(function () {
	'use strict';

	if (!document.body.classList.contains('woocommerce-checkout')) return;

	var ICON = {
		lock: '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>',
		returns: '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><polyline points="3 3 3 9 9 9"/></svg>',
		leather: '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><polyline points="9 12 11 14 15 10"/></svg>'
	};

	function fieldValid(input) {
		if (!input.value || !input.value.trim()) return false;
		var wrap = input.closest('.wc-block-components-text-input');
		if (wrap && wrap.classList.contains('has-error')) return false;
		return input.checkValidity();
	}

	function markField(input) {
		var wrap = input.closest('.wc-block-components-text-input');
		if (!wrap) return;
		if (fieldValid(input)) {
			wrap.setAttribute('data-rc-valid', '1');
		} else {
			wrap.removeAttribute('data-rc-valid');
		}
	}

	function stepDone(step) {
		var fields = step.querySelectorAll('input[required], select[required], input[aria-required="true"], select[aria-required="true"]');
		if (!fields.length) {
			// Payment step: koi option select ho to done
			return !!step.querySelector('.wc-block-components-radio-control__input:checked');
		}
		for (var i = 0; i < fields.length; i++) {
			var f = fields[i];
			if (f.offsetParent === null) continue;
			if (f.tagName === 'SELECT' ? !f.value : !fieldValid(f)) return false;
		}
		return true;
	}

	function update() {
		document.querySelectorAll('.wc-block-checkout__main .wc-block-components-text-input input').forEach(markField);
		document.querySelectorAll('.wc-block-checkout__main .wc-block-components-checkout-step:not(.wc-block-checkout__order-notes)').forEach(function (step) {
			if (stepDone(step)) {
				step.setAttribute('data-rc-done', '1');
			} else {
				step.removeAttribute('data-rc-done');
			}
		});
		addTrust();
	}

	function addTrust() {
		var actions = document.querySelector('.wc-block-checkout__actions');
		if (!actions || actions.parentNode.querySelector('.rc-trust')) return;
		var ul = document.createElement('ul');
		ul.className = 'rc-trust';
		ul.innerHTML =
			'<li>' + ICON.lock + '<span>Secure checkout</span></li>' +
			'<li>' + ICON.returns + '<span>30-day returns &amp; exchanges</span></li>' +
			'<li>' + ICON.leather + '<span>100% genuine leather</span></li>';
		actions.parentNode.insertBefore(ul, actions.nextSibling);
	}

	var timer = null;
	function schedule() {
		clearTimeout(timer);
		timer = setTimeout(update, 120);
	}

	document.addEventListener('input', schedule, true);
	document.addEventListener('change', schedule, true);
	document.addEventListener('focusout', schedule, true);

	var root = document.querySelector('.wp-block-woocommerce-checkout') || document.body;
	new MutationObserver(schedule).observe(root, { childList: true, subtree: true });
	schedule();
})();
