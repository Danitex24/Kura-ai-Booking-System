/**
 * Frontend Forms JavaScript
 *
 * @package Kura-ai-Booking-Free
 */

(function($) {
	'use strict';

	// Star Rating
	$('.kab-star-rating .star').on('click', function() {
		var rating = $(this).data('rating');
		var $container = $(this).closest('.kab-star-rating');

		// Set hidden input value
		$container.siblings('input[name="rating"]').val(rating);

		// Update visual state
		$container.find('.star').each(function(index) {
			if (index < rating) {
				$(this).addClass('active').text('★');
			} else {
				$(this).removeClass('active').text('☆');
			}
		});
	});

	// Star hover effect
	$('.kab-star-rating .star').on('mouseenter', function() {
		var rating = $(this).data('rating');
		$(this).parent().find('.star').each(function(index) {
			if (index < rating) {
				$(this).text('★');
			} else {
				$(this).text('☆');
			}
		});
	});

	$('.kab-star-rating').on('mouseleave', function() {
		var currentRating = $(this).siblings('input[name="rating"]').val();
		$(this).find('.star').each(function(index) {
			if (currentRating && index < currentRating) {
				$(this).text('★').addClass('active');
			} else {
				$(this).text('☆').removeClass('active');
			}
		});
	});

	// Waitlist Form Submission
	$('.kab-waitlist-submission').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $container = $form.closest('.kab-form-container');
		var $message = $form.find('.kab-form-message');
		var $submitBtn = $form.find('button[type="submit"]');

		// Disable submit button
		$submitBtn.prop('disabled', true).text('Submitting...');
		$message.removeClass('success error').hide();

		var formData = {
			action: 'kab_submit_waitlist',
			nonce: kabForms.nonce,
			item_type: $form.data('item-type'),
			item_id: $form.data('item-id'),
			customer_name: $form.find('input[name="customer_name"]').val(),
			customer_email: $form.find('input[name="customer_email"]').val(),
			customer_phone: $form.find('input[name="customer_phone"]').val(),
			booking_date: $form.find('input[name="booking_date"]').val()
		};

		$.post(kabForms.ajaxurl, formData, function(response) {
			if (response.success) {
				$message.addClass('success').text(response.data.message).show();
				$form[0].reset();
			} else {
				$message.addClass('error').text(response.data.message).show();
			}
		}).fail(function() {
			$message.addClass('error').text('An error occurred. Please try again.').show();
		}).always(function() {
			$submitBtn.prop('disabled', false).text('Join Waitlist');
		});
	});

	// Review Form Submission
	$('.kab-review-submission').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $message = $form.find('.kab-form-message');
		var $submitBtn = $form.find('button[type="submit"]');

		// Validate rating
		var rating = $form.find('input[name="rating"]').val();
		if (!rating) {
			$message.addClass('error').text('Please select a rating.').show();
			return;
		}

		// Disable submit button
		$submitBtn.prop('disabled', true).text('Submitting...');
		$message.removeClass('success error').hide();

		var formData = {
			action: 'kab_submit_review',
			nonce: kabForms.nonce,
			booking_id: $form.data('booking-id'),
			rating: rating,
			title: $form.find('input[name="title"]').val(),
			comment: $form.find('textarea[name="comment"]').val(),
			customer_name: $form.find('input[name="customer_name"]').val(),
			customer_email: $form.find('input[name="customer_email"]').val()
		};

		$.post(kabForms.ajaxurl, formData, function(response) {
			if (response.success) {
				$message.addClass('success').text(response.data.message).show();
				$form[0].reset();
				$form.find('.kab-star-rating .star').removeClass('active').text('☆');
			} else {
				$message.addClass('error').text(response.data.message).show();
			}
		}).fail(function() {
			$message.addClass('error').text('An error occurred. Please try again.').show();
		}).always(function() {
			$submitBtn.prop('disabled', false).text('Submit Review');
		});
	});

	// Cancellation Form Submission
	$('.kab-cancellation-submission').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $message = $form.find('.kab-form-message');
		var $submitBtn = $form.find('button[type="submit"]');

		// Confirmation
		if (!confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
			return;
		}

		// Disable submit button
		$submitBtn.prop('disabled', true).text('Processing...');
		$message.removeClass('success error').hide();

		var formData = {
			action: 'kab_cancel_booking',
			nonce: kabForms.nonce,
			booking_id: $form.find('input[name="booking_id"]').val(),
			reason: $form.find('textarea[name="reason"]').val()
		};

		$.post(kabForms.ajaxurl, formData, function(response) {
			if (response.success) {
				$message.addClass('success').text(response.data.message).show();
				$form[0].reset();
			} else {
				$message.addClass('error').text(response.data.message).show();
			}
		}).fail(function() {
			$message.addClass('error').text('An error occurred. Please try again.').show();
		}).always(function() {
			$submitBtn.prop('disabled', false).text('Request Cancellation');
		});
	});

	// Booking Form Type Toggle
	$('#booking_type').on('change', function() {
		var type = $(this).val();
		var $serviceSelect = $('.kab-service-select');
		var $eventSelect = $('.kab-event-select');

		if (type === 'service') {
			$serviceSelect.show();
			$eventSelect.hide();
			$('#service_id').prop('required', true);
			$('#event_id').prop('required', false);
		} else if (type === 'event') {
			$serviceSelect.hide();
			$eventSelect.show();
			$('#service_id').prop('required', false);
			$('#event_id').prop('required', true);
		} else {
			$serviceSelect.hide();
			$eventSelect.hide();
			$('#service_id').prop('required', false);
			$('#event_id').prop('required', false);
		}
	});

	// Booking Form Submission
	$('.kab-booking-submission').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $message = $form.find('.kab-form-message');
		var $submitBtn = $form.find('button[type="submit"]');

		// Validate booking type
		var bookingType = $form.find('#booking_type').val();
		if (!bookingType) {
			$message.addClass('error').text('Please select a booking type.').show();
			return;
		}

		// Validate service or event selection
		if (bookingType === 'service' && !$form.find('#service_id').val()) {
			$message.addClass('error').text('Please select a service.').show();
			return;
		}

		if (bookingType === 'event' && !$form.find('#event_id').val()) {
			$message.addClass('error').text('Please select an event.').show();
			return;
		}

		// Disable submit button
		$submitBtn.prop('disabled', true).text('Processing...');
		$message.removeClass('success error').hide();

		var formData = {
			action: 'kab_submit_booking',
			nonce: kabForms.nonce,
			booking_type: bookingType,
			service_id: $form.find('#service_id').val(),
			event_id: $form.find('#event_id').val(),
			booking_date: $form.find('#booking_date').val(),
			booking_time: $form.find('#booking_time').val(),
			customer_name: $form.find('#customer_name').val(),
			customer_email: $form.find('#customer_email').val(),
			customer_phone: $form.find('#customer_phone').val(),
			notes: $form.find('#notes').val()
		};

		$.post(kabForms.ajaxurl, formData, function(response) {
			if (response.success) {
				$message.addClass('success').text(response.data.message).show();
				$form[0].reset();
				// Reset visibility
				$('.kab-service-select, .kab-event-select').hide();
			} else {
				$message.addClass('error').text(response.data.message).show();
			}
		}).fail(function() {
			$message.addClass('error').text('An error occurred. Please try again.').show();
		}).always(function() {
			$submitBtn.prop('disabled', false).text('Book Now');
		});
	});

})(jQuery);
