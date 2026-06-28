/**
 * Custom archive category filter — toggle without native select styling.
 */
(function () {
	'use strict';

	var filters = document.querySelectorAll('[data-art-theme-archive-filter]');

	if (!filters.length) {
		return;
	}

	function closeFilter(filter) {
		var toggle = filter.querySelector('.art-theme-archive-filter__toggle');
		var list = filter.querySelector('.art-theme-archive-filter__list');

		if (!toggle || !list) {
			return;
		}

		filter.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
		list.hidden = true;
	}

	function openFilter(filter) {
		var toggle = filter.querySelector('.art-theme-archive-filter__toggle');
		var list = filter.querySelector('.art-theme-archive-filter__list');

		if (!toggle || !list) {
			return;
		}

		filters.forEach(function (other) {
			if (other !== filter) {
				closeFilter(other);
			}
		});

		filter.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');
		list.hidden = false;
	}

	filters.forEach(function (filter) {
		var toggle = filter.querySelector('.art-theme-archive-filter__toggle');

		if (!toggle) {
			return;
		}

		toggle.addEventListener('click', function () {
			if (filter.classList.contains('is-open')) {
				closeFilter(filter);
			} else {
				openFilter(filter);
			}
		});
	});

	document.addEventListener('click', function (event) {
		filters.forEach(function (filter) {
			if (!filter.contains(event.target)) {
				closeFilter(filter);
			}
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key !== 'Escape') {
			return;
		}

		filters.forEach(closeFilter);
	});
})();
