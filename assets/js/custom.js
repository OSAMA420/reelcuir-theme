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

	// Mega menu promo cards: [chhota text, heading, button, link]. Photos custom.css mein hain.
	var CARDS = {
		a2bc170: ['Bestselling Style', 'Cafe Racer Edit', 'Explore', '/product-category/cafe-racer-jackets/'],
		db6bc72: ['New In', 'Women’s Bomber Edit', 'Discover', '/product-category/women/women-leather-jackets/']
	};

	// Desktop nested menu (Elementor) mein extra top-level links. Mobile/side menu WordPress
	// ke "Main Menu" (id 20) se aata hai, wahan ye items wp-admin mein jode gaye hain.
	var NAV_ITEMS = [
		{ key: 'new', text: 'NEW ARRIVALS', href: '/new-arrivals/', first: true },
		{ key: 'sale', text: 'SALE', href: '/sale/', first: false }
	];

	function addNavItems() {
		document.querySelectorAll('.elementor-location-header ul.e-n-menu-heading').forEach(function (ul) {
			var template = Array.prototype.find.call(ul.children, function (li) {
				return !li.querySelector('.e-n-menu-content') && li.querySelector('a.e-n-menu-title-container') && !li.hasAttribute('data-rc-nav');
			});
			if (!template) return;
			NAV_ITEMS.forEach(function (item) {
				if (ul.querySelector('[data-rc-nav="' + item.key + '"]')) return;
				var li = template.cloneNode(true);
				li.setAttribute('data-rc-nav', item.key);
				li.removeAttribute('data-rc-empty-cat');
				li.style.display = '';
				li.querySelectorAll('[id]').forEach(function (el) { el.removeAttribute('id'); });
				var a = li.querySelector('a.e-n-menu-title-container');
				a.setAttribute('href', item.href);
				a.removeAttribute('data-focus-index');
				li.querySelector('.e-n-menu-title-text').textContent = item.text;
				if (item.first) {
					ul.insertBefore(li, ul.firstElementChild);
				} else {
					ul.appendChild(li);
				}
			});
		});
	}

	function setText(el, text) {
		if (el && el.textContent.trim() !== text) el.textContent = text;
	}

	function rewriteCards() {
		Object.keys(CARDS).forEach(function (id) {
			var c = CARDS[id];
			document.querySelectorAll('.elementor-location-header .elementor-element-' + id).forEach(function (card) {
				var titles = card.querySelectorAll('.elementor-heading-title');
				setText(titles[0], c[0]);
				setText(titles[1] && (titles[1].querySelector('a') || titles[1]), c[1]);
				setText(titles[2] && (titles[2].querySelector('a') || titles[2]), c[2]);
				card.querySelectorAll('a[href]').forEach(function (a) {
					if (a.getAttribute('href').indexOf(c[3]) === -1) a.setAttribute('href', c[3]);
				});
			});
		});
	}

	function slugOf(href) {
		var m = /\/product-category\/(.+?)\/?(?:[?#].*)?$/.exec(href || '');
		if (!m) return null;
		var parts = m[1].split('/');
		return parts[parts.length - 1];
	}

	function hideEmpty() {
		rewriteCards();
		addNavItems();
		if (!empty.length) return;
		document.querySelectorAll('.elementor-location-header a[href*="/product-category/"]').forEach(function (a) {
			if (empty.indexOf(slugOf(a.getAttribute('href'))) === -1) return;
			var item = a.closest('.elementor-icon-list-item, .elementor-widget, .e-n-menu-item, .menu-item');
			(item || a).style.display = 'none';
			(item || a).setAttribute('data-rc-empty-cat', '1');
		});
	}

	// Elementor page load ke baad header menu dobara banata hai, is liye load par
	// aur header badalne par bhi chalao.
	var timer = null;
	function schedule() {
		clearTimeout(timer);
		timer = setTimeout(hideEmpty, 50);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', hideEmpty);
	} else {
		hideEmpty();
	}
	window.addEventListener('load', hideEmpty);

	var header = document.querySelector('.elementor-location-header');
	if (header) {
		new MutationObserver(schedule).observe(header, { childList: true, subtree: true });
	}
})();

/*
 * Shop filters (Nova Ajax Product Filter) + theme infinite scroll fix.
 * Masla: filter click par plugin page upar scroll karta hai; isi dauraan theme ka infinite
 * scroll purane (bina filter) "Next" link se page 2 mangwa leta hai, jo filter ke baad aa kar
 * galat products list mein jod deta hai (Women filter par men's jackets).
 * Saath hi filter ke baad "Load more" button gayab ho jata tha (agla page kabhi nahi aata), aur
 * plugin ka novaapfAfterUpdate ek ghair-maujood `ajax_load_items` call kar ke error deta tha.
 */
(function ($) {
	'use strict';

	if (!$) return;

	var BTN = '.products_ajax_button';
	var filtering = false;
	var pending = [];

	function isPagedRequest(url) {
		return /\/page\/\d+\/?/.test(url || '') && url !== window.location.href;
	}

	// Theme ka infinite scroll jawab mein se `.products > .product` uthata hai, jis mein header ke
	// search popup ki "Best Sellers" list bhi aa jati hai (duplicate/ghair-mutalliq products).
	// Jawab mein sirf asli shop list rehne do.
	function keepMainProducts(html) {
		if (typeof html !== 'string' || html.indexOf('products') === -1 || !window.DOMParser) return html;
		var doc = new DOMParser().parseFromString(html, 'text/html');
		var main = doc.querySelector('.novaapf-before-products ul.products') ||
			doc.querySelector('.elementor-location-archive ul.products');
		if (!main) return html;
		doc.querySelectorAll('ul.products').forEach(function (ul) {
			if (ul !== main) ul.remove();
		});
		return '<!DOCTYPE html>' + doc.documentElement.outerHTML;
	}

	$.ajaxPrefilter(function (opts) {
		if ((opts.type || 'GET').toUpperCase() !== 'GET' || !isPagedRequest(opts.url)) return;
		var original = opts.dataFilter;
		opts.dataFilter = function (data, type) {
			if (original) data = original(data, type);
			return keepMainProducts(data);
		};
	});

	// Infinite scroll ki requests yaad rakho; filter ke dauraan nayi request shuru hi na ho.
	$(document).ajaxSend(function (e, jqXHR, opts) {
		if ((opts.type || 'GET').toUpperCase() !== 'GET' || !isPagedRequest(opts.url)) return;
		if (filtering) {
			jqXHR.abort();
			return;
		}
		pending.push(jqXHR);
		jqXHR.always(function () {
			pending = pending.filter(function (x) { return x !== jqXHR; });
		});
	});

	function restoreLoadMore() {
		var type = window.nova_js_var && window.nova_js_var.shop_pagination_type;
		if (type !== 'infinite_scroll' && type !== 'load_more_button') return;
		// Plugin ka apna ajax_load_items.init() har filter ke baad bina check naya button jodta hai.
		// Do buttons hon to ek scroll par dono click hote hain aur agla page do dafa judta hai.
		$(BTN).slice(1).remove();
		var $pagination = $('body.woocommerce-shop .woocommerce-pagination');
		if (!$pagination.length || $(BTN).length) return;
		var label = (window.nova_js_var && window.nova_js_var.load_more_btn) || 'Load more';
		$pagination.first().before('<div class="products_ajax_button" data-processing="0"><span>' + $('<i>').text(label).html() + '</span></div>');
		if (!$('.woocommerce-pagination a.next').length) $(BTN).addClass('disabled');
	}

	function wrap() {
		if (window.__rcNovaapfWrapped || typeof window.novaapfBeforeUpdate !== 'function') return;
		window.__rcNovaapfWrapped = true;

		var before = window.novaapfBeforeUpdate;
		var after = window.novaapfAfterUpdate;

		window.novaapfBeforeUpdate = function () {
			filtering = true;
			$(BTN).attr('data-processing', 1);
			pending.slice().forEach(function (x) { x.abort(); });
			pending = [];
			return before.apply(this, arguments);
		};

		window.novaapfAfterUpdate = function () {
			try {
				if (typeof after === 'function') after.apply(this, arguments);
			} catch (err) {
				// Plugin `ajax_load_items.init()` call karta hai jo theme mein nahi hai.
			} finally {
				filtering = false;
				restoreLoadMore();
				$(BTN).attr('data-processing', 0);
			}
		};
	}

	// Plugin ka naya button $(document).ready ke andar (yani hamare code ke baad) judta hai,
	// is liye jab bhi doosra button aaye foran hatao.
	function watchButtons() {
		var root = document.querySelector('.novaapf-before-products');
		if (!root || !window.MutationObserver) return;
		new MutationObserver(function () {
			if ($(BTN).length > 1) $(BTN).slice(1).remove();
		}).observe(root.parentNode || root, { childList: true, subtree: true });
	}

	$(function () { setTimeout(wrap, 0); watchButtons(); });
	$(window).on('load', wrap);
})(window.jQuery);

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
