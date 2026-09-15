(function () {
	'use strict';

	var cfg = window.UptownBooking;
	var root = document.querySelector('[data-ubk-root]');
	if (!cfg || !root) {
		return;
	}

	var state = {
		view: 'categories',
		categories: [],
		category: null,
		type: null,
		month: monthKey(new Date()),
		days: {},
		date: '',
		time: '',
		booking: null,
		error: '',
		busy: false
	};

	function monthKey(date) {
		return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
	}

	function monthLabel(key) {
		var parts = key.split('-');
		return new Date(Number(parts[0]), Number(parts[1]) - 1, 1).toLocaleDateString('de-DE', { month: 'long', year: 'numeric' });
	}

	function api(path, options) {
		options = options || {};
		return fetch(cfg.restUrl + path, {
			method: options.method || 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce
			},
			body: options.body ? JSON.stringify(options.body) : undefined
		}).then(function (res) {
			return res.json().then(function (data) {
				if (!res.ok) {
					throw new Error(data.message || 'Request failed');
				}
				return data;
			});
		});
	}

	function el(html) {
		var wrap = document.createElement('div');
		wrap.innerHTML = html.trim();
		return wrap.firstElementChild;
	}

	function esc(value) {
		return String(value == null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function back(view) {
		return '<button type="button" class="ubk-back" data-go="' + view + '">← Zurück</button>';
	}

	function side(title, body) {
		return '<aside class="ubk-side"><p class="ubk-kicker">Termin buchen</p><h2>' + esc(title) + '</h2>' + body + '</aside>';
	}

	function render() {
		if (state.view === 'done' && state.booking) {
			root.innerHTML = '<div class="ubk-done"><p class="ubk-kicker">Bestätigt</p><h2>Wir sehen uns.</h2><p>' + esc(state.booking.title) + ' mit ' + esc(state.booking.host) + '<br>' + esc(state.booking.when) + '–' + esc(state.booking.end) + '<br>' + esc(state.booking.location || '') + '</p><p>Eine Bestätigung geht an deine E-Mail.</p></div>';
			return;
		}

		if (state.view === 'categories') {
			root.innerHTML = '<div class="ubk-layout">' +
				side('Wähle dein Anliegen', '<p class="ubk-bio">Fitness, Business, Marketing oder Vertrieb – du landest direkt im Kalender des richtigen Teams.</p>') +
				'<div class="ubk-main"><div class="ubk-grid">' + state.categories.map(function (cat) {
					return '<button type="button" class="ubk-card" data-cat="' + esc(cat.id) + '"><strong>' + esc(cat.title) + '</strong><span>' + esc(cat.blurb) + '</span></button>';
				}).join('') + '</div></div></div>';
			return;
		}

		if (state.view === 'types') {
			root.innerHTML = '<div class="ubk-layout">' +
				side(state.category.title, '<p class="ubk-bio">' + esc(state.category.blurb) + '</p>') +
				'<div class="ubk-main">' + back('categories') + '<div class="ubk-grid">' + state.category.types.map(function (type) {
					return '<button type="button" class="ubk-card" data-type="' + esc(type.slug) + '"><strong>' + esc(type.title) + '</strong><span>' + esc(type.host.name) + ' · ' + esc(type.host.role) + ' · ' + type.duration + ' Min.</span></button>';
				}).join('') + '</div></div></div>';
			return;
		}

		var type = state.type || {};
		var host = type.host || {};
		var info = '<p class="ubk-meta">' + esc(host.name) + ' · ' + esc(host.role) + '</p>' +
			'<p class="ubk-bio">' + esc(type.description || host.bio || '') + '</p>' +
			'<div class="ubk-chips"><span class="ubk-chip">' + (type.duration || 0) + ' Min.</span>' +
			(type.location ? '<span class="ubk-chip">' + esc(type.location) + '</span>' : '') + '</div>';

		if (state.view === 'calendar') {
			root.innerHTML = '<div class="ubk-layout">' +
				side(type.title || 'Termin', info) +
				'<div class="ubk-main">' + back('types') + calendarHtml() + '</div></div>';
			return;
		}

		if (state.view === 'form') {
			root.innerHTML = '<div class="ubk-layout">' +
				side(type.title || 'Termin', info + '<p class="ubk-meta">' + esc(state.date) + ' · ' + esc(state.time) + '</p>') +
				'<div class="ubk-main">' + back('calendar') +
				(state.error ? '<p class="ubk-error">' + esc(state.error) + '</p>' : '') +
				'<form class="ubk-form" data-form>' +
				'<label>Name<input name="name" required></label>' +
				'<label>E-Mail<input name="email" type="email" required></label>' +
				'<label>Telefon <span style="font-weight:500;color:#aba7b1">(optional)</span><input name="phone"></label>' +
				'<label>Worum geht es?<textarea name="notes" rows="3"></textarea></label>' +
				'<button class="ubk-submit" type="submit"' + (state.busy ? ' disabled' : '') + '>' + (state.busy ? 'Bucht…' : 'Termin bestätigen') + '</button>' +
				'</form></div></div>';
		}
	}

	function calendarHtml() {
		var parts = state.month.split('-');
		var year = Number(parts[0]);
		var month = Number(parts[1]) - 1;
		var first = new Date(year, month, 1);
		var startPad = (first.getDay() + 6) % 7;
		var daysInMonth = new Date(year, month + 1, 0).getDate();
		var cells = [];
		var i;
		for (i = 0; i < startPad; i++) {
			cells.push('<span></span>');
		}
		for (i = 1; i <= daysInMonth; i++) {
			var key = state.month + '-' + String(i).padStart(2, '0');
			var open = state.days[key] && state.days[key].length;
			cells.push('<button type="button" class="ubk-day' + (open ? ' is-open' : '') + (state.date === key ? ' is-on' : '') + '" data-day="' + key + '" ' + (open ? '' : 'disabled') + '>' + i + '</button>');
		}
		var slots = (state.days[state.date] || []).map(function (time) {
			return '<button type="button" class="ubk-slot' + (state.time === time ? ' is-on' : '') + '" data-time="' + time + '">' + time + '</button>';
		}).join('');

		return '<div class="ubk-split"><div>' +
			'<div class="ubk-cal-head"><h3>' + esc(monthLabel(state.month)) + '</h3><div class="ubk-nav">' +
			'<button type="button" data-shift="-1" aria-label="Vorheriger Monat">‹</button>' +
			'<button type="button" data-shift="1" aria-label="Nächster Monat">›</button></div></div>' +
			'<div class="ubk-week"><span>Mo</span><span>Di</span><span>Mi</span><span>Do</span><span>Fr</span><span>Sa</span><span>So</span></div>' +
			'<div class="ubk-days">' + cells.join('') + '</div></div>' +
			'<div class="ubk-slots">' + (state.date ? (slots || '<p class="ubk-empty">Kein freier Slot.</p>') : '<p class="ubk-empty">Tag wählen</p>') + '</div></div>';
	}

	function loadSlots() {
		if (!state.type) {
			return Promise.resolve();
		}
		return api('slots?type=' + encodeURIComponent(state.type.slug) + '&month=' + encodeURIComponent(state.month)).then(function (data) {
			state.days = data.days || {};
			if (state.date && !state.days[state.date]) {
				state.date = '';
				state.time = '';
			}
			render();
		});
	}

	function findType(slug) {
		var i, j, cat, type;
		for (i = 0; i < state.categories.length; i++) {
			cat = state.categories[i];
			for (j = 0; j < cat.types.length; j++) {
				type = cat.types[j];
				if (type.slug === slug) {
					return { category: cat, type: type };
				}
			}
		}
		return null;
	}

	root.addEventListener('click', function (event) {
		var cat = event.target.closest('[data-cat]');
		var typeBtn = event.target.closest('[data-type]');
		var backBtn = event.target.closest('[data-go]');
		var shift = event.target.closest('[data-shift]');
		var day = event.target.closest('[data-day]');
		var time = event.target.closest('[data-time]');

		if (cat) {
			state.category = state.categories.filter(function (item) { return item.id === cat.getAttribute('data-cat'); })[0];
			state.view = 'types';
			render();
		} else if (typeBtn) {
			var found = findType(typeBtn.getAttribute('data-type'));
			if (!found) {
				return;
			}
			state.category = found.category;
			state.type = found.type;
			state.view = 'calendar';
			state.date = '';
			state.time = '';
			root.innerHTML = '<p class="ubk-loading">Freie Zeiten werden geladen…</p>';
			loadSlots();
		} else if (backBtn) {
			state.view = backBtn.getAttribute('data-go');
			state.error = '';
			render();
		} else if (shift) {
			var base = new Date(state.month + '-01T12:00:00');
			base.setMonth(base.getMonth() + Number(shift.getAttribute('data-shift')));
			state.month = monthKey(base);
			state.date = '';
			state.time = '';
			loadSlots();
		} else if (day && !day.disabled) {
			state.date = day.getAttribute('data-day');
			state.time = '';
			render();
		} else if (time) {
			state.time = time.getAttribute('data-time');
			state.view = 'form';
			state.error = '';
			render();
		}
	});

	root.addEventListener('submit', function (event) {
		var form = event.target.closest('[data-form]');
		if (!form) {
			return;
		}
		event.preventDefault();
		var data = new FormData(form);
		state.busy = true;
		state.error = '';
		render();
		api('book', {
			method: 'POST',
			body: {
				type: state.type.slug,
				date: state.date,
				time: state.time,
				name: data.get('name'),
				email: data.get('email'),
				phone: data.get('phone'),
				notes: data.get('notes')
			}
		}).then(function (res) {
			state.booking = res.booking;
			state.view = 'done';
			state.busy = false;
			render();
		}).catch(function (err) {
			state.busy = false;
			state.error = err.message || 'Buchung fehlgeschlagen.';
			render();
		});
	});

	api('catalog').then(function (data) {
		state.categories = data.categories || [];
		var wanted = cfg.agenda || document.querySelector('[data-ubk]').getAttribute('data-agenda');
		if (wanted) {
			var match = state.categories.filter(function (cat) { return cat.id === wanted; })[0];
			if (match) {
				state.category = match;
				state.view = 'types';
			}
		}
		render();
	}).catch(function () {
		root.innerHTML = '<p class="ubk-empty">Kalender konnte nicht geladen werden.</p>';
	});
})();
