(function ($) {
	'use strict';

	$(function () {
		var $color = $('.ucb-color');
		var $preview = $('.ucb-preview');
		var $name = $('#ucb-bot-name');
		var $bar = $('.ucb-preview__bar');

		$color.wpColorPicker({
			change: function (event, ui) {
				$preview.css('--ucb-theme', ui.color.toString());
			},
			clear: function () {
				$preview.css('--ucb-theme', '#f53c0f');
			}
		});

		$('.ucb-preset').on('click', function () {
			var hex = $(this).data('color');
			$color.wpColorPicker('color', hex);
			$preview.css('--ucb-theme', hex);
		});

		$name.on('input', function () {
			$bar.text($(this).val() || 'Uptown');
		});

		$('#ucb-toggle-key').on('click', function () {
			var $input = $('#ucb-api-key');
			var show = $input.attr('type') === 'password';
			$input.attr('type', show ? 'text' : 'password');
			$(this).text(show ? 'Hide' : 'Show');
		});

		$('#ucb-test-key').on('click', function () {
			var $btn = $(this);
			var $out = $('#ucb-test-result');
			$btn.prop('disabled', true);
			$out.removeClass('is-ok is-err').text('Testing…');

			fetch(UptownChatbotAdmin.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': UptownChatbotAdmin.nonce
				},
				body: JSON.stringify({})
			})
				.then(function (res) { return res.json().then(function (data) { return { res: res, data: data }; }); })
				.then(function (pack) {
					if (pack.res.ok && pack.data.ok) {
						$out.addClass('is-ok').text(pack.data.message);
					} else {
						$out.addClass('is-err').text(pack.data.message || 'Connection failed.');
					}
				})
				.catch(function () {
					$out.addClass('is-err').text('Connection failed.');
				})
				.finally(function () {
					$btn.prop('disabled', false);
				});
		});
	});
})(jQuery);
