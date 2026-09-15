(function () {
	'use strict';

	function boot() {
	var cfg = window.UptownChatbot;
	if (!cfg) {
		return;
	}

	var root = document.getElementById('uptown-chatbot');
	if (!root) {
		return;
	}

	var panel = root.querySelector('.ucb-panel');
	var messagesEl = root.querySelector('[data-ucb="messages"]');
	var chipsEl = root.querySelector('[data-ucb="chips"]');
	var dividerEl = root.querySelector('[data-ucb="divider"]');
	var inputEl = root.querySelector('[data-ucb="input"]');
	var formEl = root.querySelector('[data-ucb="form"]');
	var menuEl = document.getElementById('ucb-menu');
	var tipEl = root.querySelector('[data-ucb="tip"]');
	var tipText = root.querySelector('[data-ucb="tip-text"]');
	var tipOk = root.querySelector('[data-ucb="tip-ok"]');
	var poweredEl = root.querySelector('[data-ucb="powered"]');
	var toggleBtn = root.querySelector('[data-ucb="toggle"]');
	var titleEl = document.getElementById('ucb-title');

	var storageKey = 'uptownChatbot.v1';
	var state = loadState();
	var sending = false;

	function detectLang() {
		if (cfg.language === 'de' || cfg.language === 'en') {
			return cfg.language;
		}
		if (state.lang === 'de' || state.lang === 'en') {
			return state.lang;
		}
		var nav = (navigator.language || document.documentElement.lang || 'de').toLowerCase();
		return nav.indexOf('de') === 0 ? 'de' : 'en';
	}

	function t() {
		return cfg.i18n[state.lang] || cfg.i18n.de;
	}

	function loadState() {
		try {
			return JSON.parse(sessionStorage.getItem(storageKey)) || {};
		} catch (e) {
			return {};
		}
	}

	function saveState() {
		sessionStorage.setItem(storageKey, JSON.stringify(state));
	}

	function welcomeText() {
		return t().welcome.replace('{name}', cfg.botName || 'Uptown');
	}

	function applyCopy() {
		var copy = t();
		dividerEl.textContent = copy.newMessages;
		inputEl.placeholder = copy.placeholder;
		poweredEl.textContent = copy.powered;
		tipText.textContent = copy.tip;
		tipOk.textContent = copy.tipOk;
		toggleBtn.setAttribute('aria-label', root.classList.contains('is-open') ? copy.close : copy.open);
		titleEl.textContent = cfg.botName || 'Uptown';
		renderChips();
	}

	function renderChips() {
		chipsEl.innerHTML = '';
		chipsEl.hidden = false;
		t().chips.forEach(function (chip) {
			var label = typeof chip === 'string' ? chip : chip.label;
			var message = typeof chip === 'string' ? chip : (chip.message || chip.label);
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'ucb-chip';
			btn.textContent = label;
			btn.title = message;
			btn.addEventListener('click', function () {
				sendMessage(message);
			});
			chipsEl.appendChild(btn);
		});
	}

	function addBubble(role, text, typing) {
		var row = document.createElement('div');
		row.className = 'ucb-row is-' + role;
		if (role === 'bot') {
			var avatar = document.createElement('span');
			avatar.className = 'ucb-avatar';
			avatar.innerHTML = '<svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>';
			row.appendChild(avatar);
		}
		var bubble = document.createElement('div');
		bubble.className = 'ucb-bubble' + (typing ? ' ucb-typing' : '');
		bubble.textContent = text;
		if (typing) {
			bubble.innerHTML = '<span></span><span></span><span></span>';
		}
		row.appendChild(bubble);
		messagesEl.appendChild(row);
		messagesEl.scrollTop = messagesEl.scrollHeight;
		return row;
	}

	function resetChat() {
		state.history = [];
		saveState();
		messagesEl.innerHTML = '';
		addBubble('bot', welcomeText());
		renderChips();
	}

	function openPanel() {
		root.classList.add('is-open');
		root.classList.remove('is-closed');
		panel.hidden = false;
		toggleBtn.setAttribute('aria-label', t().close);
		if (!messagesEl.childElementCount) {
			addBubble('bot', welcomeText());
		}
		inputEl.focus();
	}

	function closePanel() {
		root.classList.remove('is-open');
		root.classList.add('is-closed');
		panel.hidden = true;
		menuEl.hidden = true;
		toggleBtn.setAttribute('aria-label', t().open);
	}

	function sendMessage(text) {
		var value = (text || inputEl.value || '').trim();
		if (!value || sending) {
			return;
		}

		inputEl.value = '';
		addBubble('user', value);
		state.history = state.history || [];
		state.history.push({ role: 'user', text: value });
		saveState();

		if (!cfg.configured) {
			addBubble('bot', t().offline);
			return;
		}

		sending = true;
		var pending = addBubble('bot', '', true);

		fetch(cfg.restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.nonce
			},
			body: JSON.stringify({
				message: value,
				lang: state.lang,
				history: state.history.slice(0, -1)
			})
		})
			.then(function (res) {
				return res.json().then(function (data) {
					return { ok: res.ok, data: data };
				});
			})
			.then(function (pack) {
				var reply = pack.ok && pack.data.reply ? pack.data.reply : (pack.data && pack.data.message) || t().sendError;
				pending.remove();
				addBubble('bot', reply);
				state.history.push({ role: 'model', text: reply });
				saveState();
			})
			.catch(function () {
				pending.remove();
				addBubble('bot', t().sendError);
			})
			.finally(function () {
				sending = false;
			});
	}

	root.hidden = false;
	root.classList.add('is-closed');
	state.lang = detectLang();
	applyCopy();

	if (state.history && state.history.length) {
		state.history.forEach(function (item) {
			addBubble(item.role === 'user' ? 'user' : 'bot', item.text);
		});
	}

	if (state.tipDismissed) {
		tipEl.hidden = true;
	}

	toggleBtn.addEventListener('click', function () {
		if (root.classList.contains('is-open')) {
			closePanel();
		} else {
			openPanel();
		}
	});

	root.querySelector('[data-ucb="minimize"]').addEventListener('click', closePanel);

	root.querySelector('[data-ucb="menu"]').addEventListener('click', function (event) {
		menuEl.hidden = !menuEl.hidden;
		event.currentTarget.setAttribute('aria-expanded', menuEl.hidden ? 'false' : 'true');
	});

	root.querySelectorAll('[data-ucb="lang"]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			state.lang = btn.getAttribute('data-lang');
			saveState();
			applyCopy();
			menuEl.hidden = true;
			if (!state.history || !state.history.length) {
				messagesEl.innerHTML = '';
				addBubble('bot', welcomeText());
			}
		});
	});

	root.querySelector('[data-ucb="reset"]').addEventListener('click', function () {
		resetChat();
		menuEl.hidden = true;
	});

	formEl.addEventListener('submit', function (event) {
		event.preventDefault();
		sendMessage();
	});

	tipOk.addEventListener('click', function () {
		tipEl.hidden = true;
		state.tipDismissed = true;
		saveState();
	});

	document.addEventListener('click', function (event) {
		if (!root.contains(event.target)) {
			menuEl.hidden = true;
		}
	});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
