/**
 * Shortcodes Reference Page JavaScript
 *
 * @package Kura-ai-Booking-Free
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Copy to Clipboard
		$('.kab-copy-btn').on('click', function(e) {
			e.preventDefault();

			var shortcode = $(this).data('shortcode');
			var $button = $(this);

			// Copy to clipboard
			copyToClipboard(shortcode);

			// Visual feedback
			var originalText = $button.html();
			$button.html('<span class="dashicons dashicons-yes-alt"></span> Copied!');
			$button.css('background', '#10b981');

			// Show toast
			showToast();

			// Reset button after 2 seconds
			setTimeout(function() {
				$button.html(originalText);
				$button.css('background', '');
			}, 2000);
		});

		// Copy to clipboard function
		function copyToClipboard(text) {
			// Modern approach
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).catch(function(err) {
					// Fallback
					fallbackCopyToClipboard(text);
				});
			} else {
				// Fallback for older browsers
				fallbackCopyToClipboard(text);
			}
		}

		// Fallback copy method
		function fallbackCopyToClipboard(text) {
			var $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(text).select();
			document.execCommand('copy');
			$temp.remove();
		}

		// Show toast notification
		function showToast() {
			var $toast = $('#kab-copy-toast');
			$toast.addClass('show');

			setTimeout(function() {
				$toast.removeClass('show');
			}, 3000);
		}

		// Search functionality
		$('#kab-shortcode-search').on('input', function() {
			var searchTerm = $(this).val().toLowerCase();

			$('.kab-shortcode-card').each(function() {
				var $card = $(this);
				var title = $card.find('h3').text().toLowerCase();
				var description = $card.find('.kab-card-description').text().toLowerCase();
				var code = $card.find('.kab-shortcode-code').text().toLowerCase();
				var category = $card.data('category') || '';

				var matches = title.includes(searchTerm) ||
							  description.includes(searchTerm) ||
							  code.includes(searchTerm) ||
							  category.includes(searchTerm);

				if (matches || searchTerm === '') {
					$card.removeClass('hidden').fadeIn(300);
				} else {
					$card.addClass('hidden').fadeOut(300);
				}
			});

			// Show "no results" message
			var visibleCards = $('.kab-shortcode-card:not(.hidden)').length;
			$('.kab-no-results').remove();

			if (visibleCards === 0 && searchTerm !== '') {
				var $noResults = $('<div class="kab-no-results">')
					.html('<p style="text-align: center; color: #64748b; padding: 60px 20px; font-size: 16px;"><span class="dashicons dashicons-search" style="font-size: 48px; display: block; margin-bottom: 15px; opacity: 0.3;"></span>No shortcodes found matching "' + escapeHtml(searchTerm) + '"</p>');
				$('.kab-shortcodes-grid').after($noResults);
			}
		});

		// Escape HTML helper
		function escapeHtml(text) {
			var map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;'
			};
			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
		}

		// Highlight search term
		$('#kab-shortcode-search').on('input', function() {
			var searchTerm = $(this).val();

			$('.kab-shortcode-card').each(function() {
				var $card = $(this);
				var $title = $card.find('h3');
				var $description = $card.find('.kab-card-description');

				// Remove existing highlights
				removeHighlights($title);
				removeHighlights($description);

				// Add new highlights
				if (searchTerm.length > 0) {
					highlightText($title, searchTerm);
					highlightText($description, searchTerm);
				}
			});
		});

		function highlightText($element, term) {
			if (!$element.data('original-html')) {
				$element.data('original-html', $element.html());
			}

			var html = $element.data('original-html');
			var regex = new RegExp('(' + escapeRegExp(term) + ')', 'gi');
			var highlighted = html.replace(regex, '<mark style="background: #fef08a; padding: 2px 4px; border-radius: 2px;">$1</mark>');
			$element.html(highlighted);
		}

		function removeHighlights($element) {
			if ($element.data('original-html')) {
				$element.html($element.data('original-html'));
			}
		}

		function escapeRegExp(string) {
			return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
		}

		// Smooth scroll to card on search
		$('#kab-shortcode-search').on('keypress', function(e) {
			if (e.which === 13) { // Enter key
				e.preventDefault();
				var $firstVisible = $('.kab-shortcode-card:not(.hidden)').first();
				if ($firstVisible.length) {
					$('html, body').animate({
						scrollTop: $firstVisible.offset().top - 100
					}, 500);
				}
			}
		});

		// Add keyboard shortcuts hint
		$(document).on('keydown', function(e) {
			// Ctrl/Cmd + K to focus search
			if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
				e.preventDefault();
				$('#kab-shortcode-search').focus();
			}

			// Escape to clear search
			if (e.keyCode === 27) {
				$('#kab-shortcode-search').val('').trigger('input');
			}
		});

		// Animate cards on page load
		$('.kab-shortcode-card').each(function(index) {
			var $card = $(this);
			setTimeout(function() {
				$card.css({
					opacity: 0,
					transform: 'translateY(20px)'
				}).animate({
					opacity: 1
				}, 400, function() {
					$(this).css('transform', 'translateY(0)');
				});
			}, index * 50);
		});

		// Add keyboard shortcut hint to search
		$('#kab-shortcode-search').attr('placeholder', $('#kab-shortcode-search').attr('placeholder') + ' (Ctrl/⌘ + K)');
	});

})(jQuery);
