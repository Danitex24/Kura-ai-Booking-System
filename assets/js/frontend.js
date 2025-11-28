(function ($) {
	'use strict';

	/**
	 * Kura-ai Booking System Frontend JavaScript
	 * Handles booking form submissions and AJAX interactions
	 */

	var KAB_Frontend = {

		/**
		 * Initialize frontend functionality
		 */
		init: function () {
			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function () {
			var self = this;

			// Booking form submission
			$( document ).on( 'submit', '.kab-booking-form', function (e) {
				e.preventDefault();
				self.handleBookingForm( $( this ) );
			});

			// Cancel booking button
			$( document ).on( 'click', '.kab-cancel-booking', function (e) {
				e.preventDefault();
				self.handleCancelBooking( $( this ) );
			});
		},

		/**
		 * Handle booking form submission
		 */
		handleBookingForm: function (form) {
			var self = this;
			var formData = form.serializeArray();
			var submitButton = form.find( 'button[type="submit"]' );

			// Show loading state
			submitButton.prop( 'disabled', true ).addClass( 'kab-loading' );

			// Add nonce to form data
			formData.push( { name: 'nonce', value: kab_frontend.nonce } );
			formData.push( { name: 'action', value: 'kab_book_appointment' } );

			$.ajax( {
				url: kab_frontend.ajax_url,
				type: 'POST',
				data: formData,
				success: function (response) {
					if ( response.success ) {
						self.showSuccessMessage( response.data.message );
						// Reset form on success
						form[0].reset();
						// Reload events list if on events page
						if ( $( '.kab-events-list' ).length ) {
							location.reload();
						}
					} else {
						self.showErrorMessage( response.data );
					}
				},
				error: function () {
					self.showErrorMessage( kab_frontend.i18n.booking_error );
				},
				complete: function () {
					submitButton.prop( 'disabled', false ).removeClass( 'kab-loading' );
				}
			} );
		},

		/**
		 * Handle booking cancellation
		 */
		handleCancelBooking: function (button) {
			var self = this;
			var bookingId = button.data( 'booking-id' );

			// Confirm cancellation
			if ( ! confirm( kab_frontend.i18n.cancel_confirm ) ) {
				return;
			}

			$.ajax( {
				url: kab_frontend.ajax_url,
				type: 'POST',
				data: {
					action: 'kab_cancel_booking',
					nonce: kab_frontend.nonce,
					booking_id: bookingId
				},
				success: function (response) {
					if ( response.success ) {
						self.showSuccessMessage( response.data );
						// Reload the page to update booking list
						setTimeout( function () {
							location.reload();
						}, 1500 );
					} else {
						self.showErrorMessage( response.data );
					}
				},
				error: function () {
					self.showErrorMessage( kab_frontend.i18n.booking_error );
				}
			} );
		},

		/**
		 * Show success message
		 */
		showSuccessMessage: function (message) {
			this.showMessage( message, 'success' );
		},

		/**
		 * Show error message
		 */
		showErrorMessage: function (message) {
			this.showMessage( message, 'error' );
		},

		/**
		 * Show message using SweetAlert2 if available, otherwise alert
		 */
		showMessage: function (message, type) {
			if ( typeof Swal !== 'undefined' ) {
				Swal.fire( {
					icon: type,
					title: message,
					showConfirmButton: false,
					timer: 3000
				} );
			} else {
				alert( message );
			}
		}

	};

	// Initialize on document ready
	$( document ).ready( function () {
		KAB_Frontend.init();
	} );

})( jQuery );