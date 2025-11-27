(function ($) {
	'use strict';

	/**
	 * Setup Wizard JavaScript functionality
	 * Handles dynamic service addition and form interactions
	 */

	var KAB_Setup_Wizard = {

		/**
		 * Initialize the setup wizard functionality
		 */
		init: function () {
			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function () {
			var self = this;

			// Add service button
			$( document ).on(
				'click',
				'#kab-add-service',
				function (e) {
					e.preventDefault();
					self.addServiceItem();
				}
			);

			// Remove service button
			$( document ).on(
				'click',
				'.kab-remove-service',
				function (e) {
					e.preventDefault();
					self.removeServiceItem( $( this ) );
				}
			);

			// Form validation
			$( document ).on(
				'submit',
				'.kab-setup-form',
				function (e) {
					return self.validateForm( $( this ) );
				}
			);
		},

		/**
		 * Add a new service item to the services container
		 */
		addServiceItem: function () {
			var container    = $( '#kab-services-container' );
			var serviceCount = container.find( '.kab-service-item' ).length;
			var nextIndex    = serviceCount;

			var serviceHtml = '<div class="kab-service-item">' +
				'<div class="kab-service-header">' +
				'<h4>' + kabSetupWizard.i18n.service + ' #' + (nextIndex + 1) + '</h4>' +
				'<button type="button" class="kab-remove-service button-link button-link-delete">' + kabSetupWizard.i18n.remove + '</button>' +
				'</div>' +
				'<table class="form-table">' +
				'<tr>' +
				'<th scope="row">' +
				'<label>' + kabSetupWizard.i18n.serviceName + '</label>' +
				'</th>' +
				'<td>' +
				'<input type="text" name="services[' + nextIndex + '][name]" class="regular-text" placeholder="' + kabSetupWizard.i18n.serviceNamePlaceholder + '">' +
				'</td>' +
				'</tr>' +
				'<tr>' +
				'<th scope="row">' +
				'<label>' + kabSetupWizard.i18n.duration + '</label>' +
				'</th>' +
				'<td>' +
				'<input type="number" name="services[' + nextIndex + '][duration]" value="60" min="5" step="5" class="small-text">' +
				'<span class="description">' + kabSetupWizard.i18n.minutes + '</span>' +
				'</td>' +
				'</tr>' +
				'<tr>' +
				'<th scope="row">' +
				'<label>' + kabSetupWizard.i18n.price + '</label>' +
				'</th>' +
				'<td>' +
				'<input type="number" name="services[' + nextIndex + '][price]" value="0" min="0" step="0.01" class="small-text">' +
				'<span class="description">' + kabSetupWizard.i18n.freeServiceNote + '</span>' +
				'</td>' +
				'</tr>' +
				'<tr>' +
				'<th scope="row">' +
				'<label>' + kabSetupWizard.i18n.description + '</label>' +
				'</th>' +
				'<td>' +
				'<textarea name="services[' + nextIndex + '][description]" rows="3" class="large-text" placeholder="' + kabSetupWizard.i18n.descriptionPlaceholder + '"></textarea>' +
				'</td>' +
				'</tr>' +
				'</table>' +
				'</div>';

			container.append( serviceHtml );

			// Update service numbers
			this.updateServiceNumbers();
		},

		/**
		 * Remove a service item
		 */
		removeServiceItem: function (button) {
			var serviceItem = button.closest( '.kab-service-item' );

			// Don't remove the last service item
			if ($( '#kab-services-container .kab-service-item' ).length <= 1) {
				alert( kabSetupWizard.i18n.cannotRemoveLastService );
				return;
			}

			serviceItem.remove();

			// Update service numbers
			this.updateServiceNumbers();
		},

		/**
		 * Update service numbers and indices
		 */
		updateServiceNumbers: function () {
			$( '#kab-services-container .kab-service-item' ).each(
				function (index) {
					var header = $( this ).find( 'h4' );
					header.text( kabSetupWizard.i18n.service + ' #' + (index + 1) );

					// Update input names with correct indices
					$( this ).find( 'input, textarea' ).each(
						function () {
							var name = $( this ).attr( 'name' );
							if (name && name.includes( 'services[' )) {
								var newName = name.replace( /services\[\d+\]/, 'services[' + index + ']' );
								$( this ).attr( 'name', newName );
							}
						}
					);
				}
			);
		},

		/**
		 * Validate the form before submission
		 */
		validateForm: function (form) {
			var currentStep = form.closest( '.kab-setup-wizard' ).data( 'current-step' );

			if (currentStep === 'services') {
				return this.validateServicesStep( form );
			}

			if (currentStep === 'business') {
				return this.validateBusinessStep( form );
			}

			return true;
		},

		/**
		 * Validate the services step
		 */
		validateServicesStep: function (form) {
			var hasValidService = false;
			var errorMessages   = [];

			$( 'input[name^="services["][name$="][name]"]' ).each(
				function () {
					var serviceName  = $( this ).val().trim();
					var serviceRow   = $( this ).closest( '.kab-service-item' );
					var serviceIndex = serviceRow.index();

					if (serviceName) {
						hasValidService = true;

						// Validate duration
						var durationInput = serviceRow.find( 'input[name$="[duration]"]' );
						var duration      = parseInt( durationInput.val(), 10 );

						if (isNaN( duration ) || duration < 5) {
							errorMessages.push( kabSetupWizard.i18n.invalidDuration.replace( '%s', serviceName ) );
							durationInput.addClass( 'error' );
						} else {
							durationInput.removeClass( 'error' );
						}

						// Validate price
						var priceInput = serviceRow.find( 'input[name$="[price]"]' );
						var price      = parseFloat( priceInput.val() );

						if (isNaN( price ) || price < 0) {
							errorMessages.push( kabSetupWizard.i18n.invalidPrice.replace( '%s', serviceName ) );
							priceInput.addClass( 'error' );
						} else {
							priceInput.removeClass( 'error' );
						}
					}
				}
			);

			if ( ! hasValidService) {
				errorMessages.push( kabSetupWizard.i18n.atLeastOneService );
			}

			if (errorMessages.length > 0) {
				this.showErrors( errorMessages );
				return false;
			}

			return true;
		},

		/**
		 * Validate the business step
		 */
		validateBusinessStep: function (form) {
			var errorMessages = [];

			// Validate business name
			var businessName = $( '#business_name' ).val().trim();
			if ( ! businessName) {
				errorMessages.push( kabSetupWizard.i18n.businessNameRequired );
				$( '#business_name' ).addClass( 'error' );
			} else {
				$( '#business_name' ).removeClass( 'error' );
			}

			// Validate business email
			var businessEmail = $( '#business_email' ).val().trim();
			var emailRegex    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

			if ( ! businessEmail) {
				errorMessages.push( kabSetupWizard.i18n.businessEmailRequired );
				$( '#business_email' ).addClass( 'error' );
			} else if ( ! emailRegex.test( businessEmail )) {
				errorMessages.push( kabSetupWizard.i18n.invalidEmail );
				$( '#business_email' ).addClass( 'error' );
			} else {
				$( '#business_email' ).removeClass( 'error' );
			}

			if (errorMessages.length > 0) {
				this.showErrors( errorMessages );
				return false;
			}

			return true;
		},

		/**
		 * Show validation errors
		 */
		showErrors: function (messages) {
			// Remove any existing error messages
			$( '.kab-setup-error' ).remove();

			var errorHtml = '<div class="notice notice-error kab-setup-error">' +
				'<p><strong>' + kabSetupWizard.i18n.validationErrors + '</strong></p>' +
				'<ul>';

			$.each(
				messages,
				function (index, message) {
					errorHtml += '<li>' + message + '</li>';
				}
			);

			errorHtml += '</ul></div>';

			// Insert error message at the top of the form
			$( '.kab-setup-content' ).prepend( errorHtml );

			// Scroll to top to show errors
			$( 'html, body' ).animate(
				{
					scrollTop: $( '.kab-setup-error' ).offset().top - 100
				},
				500
			);
		}
	};

	// Initialize when document is ready
	$( document ).ready(
		function () {
			KAB_Setup_Wizard.init();
		}
	);

})( jQuery );