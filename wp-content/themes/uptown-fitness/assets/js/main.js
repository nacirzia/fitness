(() => {
	'use strict';

	const header = document.querySelector('[data-header]');
	const menu = document.querySelector('[data-menu]');
	const toggle = document.querySelector('[data-menu-toggle]');

	const setHeader = () => {
		if (header) header.classList.toggle('is-scrolled', window.scrollY > 20);
	};
	setHeader();
	window.addEventListener('scroll', setHeader, { passive: true });

	if (toggle && menu) {
		toggle.addEventListener('click', () => {
			const open = toggle.getAttribute('aria-expanded') !== 'true';
			toggle.setAttribute('aria-expanded', String(open));
			menu.classList.toggle('is-open', open);
			document.body.classList.toggle('menu-open', open);
		});

		menu.querySelectorAll('a').forEach((link) => {
			link.addEventListener('click', () => {
				toggle.setAttribute('aria-expanded', 'false');
				menu.classList.remove('is-open');
				document.body.classList.remove('menu-open');
			});
		});
	}

	const revealItems = document.querySelectorAll('.reveal, .image-reveal');
	if ('IntersectionObserver' in window) {
		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				entry.target.classList.add('is-visible');
				observer.unobserve(entry.target);
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -35px' });

		revealItems.forEach((item, index) => {
			item.style.transitionDelay = `${Math.min((index % 4) * 70, 210)}ms`;
			observer.observe(item);
		});
	} else {
		revealItems.forEach((item) => item.classList.add('is-visible'));
	}

	const counters = document.querySelectorAll('[data-counter]');
	if (counters.length && 'IntersectionObserver' in window) {
		const counterObserver = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				const element = entry.target;
				const target = Number(element.dataset.counter);
				const duration = 1500;
				const start = performance.now();
				const suffix = target === 96 ? '%' : target === 24 ? '/7' : target >= 1000 ? '+' : '';

				const tick = (now) => {
					const progress = Math.min((now - start) / duration, 1);
					const eased = 1 - Math.pow(1 - progress, 3);
					const value = Math.round(target * eased);
					element.textContent = target >= 1000
						? new Intl.NumberFormat('de-DE').format(value) + suffix
						: value + suffix;
					if (progress < 1) requestAnimationFrame(tick);
				};

				requestAnimationFrame(tick);
				counterObserver.unobserve(element);
			});
		}, { threshold: 0.6 });
		counters.forEach((counter) => counterObserver.observe(counter));
	}

	const hero = document.querySelector('.home-hero');
	if (hero && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		window.addEventListener('scroll', () => {
			if (window.scrollY < window.innerHeight) {
				hero.style.backgroundPositionY = `${window.scrollY * 0.16}px`;
			}
		}, { passive: true });
	}

	document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
		anchor.addEventListener('click', (event) => {
			const id = anchor.getAttribute('href');
			if (id === '#') return;
			const target = document.querySelector(id);
			if (!target) return;
			event.preventDefault();
			target.scrollIntoView({ behavior: 'smooth', block: 'start' });
		});
	});
})();
