/*
 * Kura-ai Booking System - Admin Invoices JavaScript
 * Handles invoice management interface functionality
 *
 * @package Kura-ai-Booking-Free
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
	'use strict';

	// Invoice list functionality
	var KAB_Invoices_Admin = {

		/**
		 * Initialize invoice admin functionality
		 */
		init: function() {
			this.bindEvents();
			this.setupDatePickers();
			this.setupFilters();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			// Invoice actions
			$(document).on('click', '.kab-btn-download', this.handleDownload.bind(this));
			$(document).on('click', '.kab-btn-preview', this.handlePreview.bind(this));
			$(document).on('click', '.kab-btn-resend', this.handleResend.bind(this));
			$(document).on('click', '.kab-btn-print', this.handlePrint.bind(this));

			// Filter form submission
			$('#kab-invoice-filters').on('submit', this.handleFilterSubmit.bind(this));
			$('#kab-reset-filters').on('click', this.handleResetFilters.bind(this));

			// Bulk actions
			$('#doaction, #doaction2').on('click', this.handleBulkActions.bind(this));

			// Invoice status changes
			$(document).on('click', '.kab-update-status', this.handleStatusUpdate.bind(this));
		},

		/**
		 * Setup date pickers for filters
		 */
		setupDatePickers: function() {
			if (typeof $.fn.datepicker !== 'undefined') {
				$('.kab-datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true
				});
			}
		},

		/**
		 * Setup filter functionality
		 */
		setupFilters: function() {
			// Real-time search
			var searchTimeout;
			$('#kab-search-input').on('keyup', function() {
				clearTimeout(searchTimeout);
				searchTimeout = setTimeout(function() {
					$('#kab-invoice-filters').trigger('submit');
				}, 500);
			});
		},

		/**
		 * Handle invoice download
		 */
		handleDownload: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var invoiceId = $btn.data('invoice-id');
			
			this.showLoading($btn);
			
			window.location.href = ajaxurl + '?action=kab_download_invoice&invoice_id=' + invoiceId + '&_wpnonce=' + $btn.data('nonce');
			
			// Reset button after short delay
			setTimeout(function() {
				KAB_Invoices_Admin.hideLoading($btn);
			}, 2000);
		},

		/**
		 * Handle invoice preview
		 */
		handlePreview: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var invoiceId = $btn.data('invoice-id');
			
			this.showLoading($btn);
			
			// Open preview in new tab
			var previewUrl = ajaxurl + '?action=kab_preview_invoice&invoice_id=' + invoiceId + '&_wpnonce=' + $btn.data('nonce');
			window.open(previewUrl, '_blank');
			
			this.hideLoading($btn);
		},

		/**
		 * Handle invoice resend
		 */
		handleResend: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var invoiceId = $btn.data('invoice-id');
			
			this.showLoading($btn);
			
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'kab_resend_invoice',
					invoice_id: invoiceId,
					_wpnonce: $btn.data('nonce')
				},
				success: function(response) {
					if (response.success) {
						KAB_Invoices_Admin.showNotice('success', response.data.message);
					} else {
						KAB_Invoices_Admin.showNotice('error', response.data.message);
					}
				},
				error: function() {
					KAB_Invoices_Admin.showNotice('error', 'An error occurred while resending the invoice.');
				},
				complete: function() {
					KAB_Invoices_Admin.hideLoading($btn);
				}
			});
		},

		/**
		 * Handle print action
		 */
		handlePrint: function(e) {
			e.preventDefault();
			window.print();
		},

		/**
		 * Handle filter form submission
		 */
		handleFilterSubmit: function(e) {
			e.preventDefault();
			
			var formData = $(this).serialize();
			
			// Show loading indicator
			this.showLoading($('#kab-apply-filters'));
			
			// Submit via AJAX for better UX
			$.ajax({
				url: window.location.href,
				method: 'GET',
				data: formData,
				success: function(response) {
					// Extract the table content from response
					var $response = $(response);
					var $newTable = $response.find('.kab-invoices-table');
					
					if ($newTable.length) {
						$('.kab-invoices-table').replaceWith($newTable);
					}
				},
				complete: function() {
					KAB_Invoices_Admin.hideLoading($('#kab-apply-filters'));
				}
			});
		},

		/**
		 * Handle reset filters
		 */
		handleResetFilters: function(e) {
			e.preventDefault();
			
			// Clear all filter inputs
			$('#kab-invoice-filters').find('input, select').val('');
			
			// Submit the form
			$('#kab-invoice-filters').trigger('submit');
		},

		/**
		 * Handle bulk actions
		 */
		handleBulkActions: function(e) {
			var action = $('#bulk-action-selector-top').val() || $('#bulk-action-selector-bottom').val();
			
			if (action === 'export' || action === 'resend') {
				e.preventDefault();
				
				var selected = [];
				$('input[name="invoice[]"]:checked').each(function() {
					selected.push($(this).val());
				});
				
				if (selected.length === 0) {
					this.showNotice('warning', 'Please select at least one invoice.');
					return;
				}
				
				if (action === 'export') {
					this.exportInvoices(selected);
				} else if (action === 'resend') {
					this.resendInvoices(selected);
				}
			}
		},

		/**
		 * Handle status updates
		 */
		handleStatusUpdate: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var invoiceId = $btn.data('invoice-id');
			var newStatus = $btn.data('status');
			
			this.showLoading($btn);
			
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'kab_update_invoice_status',
					invoice_id: invoiceId,
					status: newStatus,
					_wpnonce: $btn.data('nonce')
				},
				success: function(response) {
					if (response.success) {
						KAB_Invoices_Admin.showNotice('success', response.data.message);
						// Reload the page to reflect changes
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						KAB_Invoices_Admin.showNotice('error', response.data.message);
					}
				},
				error: function() {
					KAB_Invoices_Admin.showNotice('error', 'An error occurred while updating the status.');
				},
				complete: function() {
					KAB_Invoices_Admin.hideLoading($btn);
				}
			});
		},

		/**
		 * Export selected invoices
		 */
		exportInvoices: function(invoiceIds) {
			var exportUrl = ajaxurl + '?action=kab_export_invoices&invoice_ids=' + invoiceIds.join(',') + '&_wpnonce=' + kab_invoices_admin.nonce;
			window.location.href = exportUrl;
		},

		/**
		 * Resend selected invoices
		 */
		resendInvoices: function(invoiceIds) {
			this.showLoading($('#doaction'));
			
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'kab_bulk_resend_invoices',
					invoice_ids: invoiceIds,
					_wpnonce: kab_invoices_admin.nonce
				},
				success: function(response) {
					if (response.success) {
						KAB_Invoices_Admin.showNotice('success', response.data.message);
					} else {
						KAB_Invoices_Admin.showNotice('error', response.data.message);
					}
				},
				error: function() {
					KAB_Invoices_Admin.showNotice('error', 'An error occurred while resending invoices.');
				},
				complete: function() {
					KAB_Invoices_Admin.hideLoading($('#doaction'));
				}
			});
		},

		/**
		 * Show loading state on button
		 */
		showLoading: function($button) {
			$button.prop('disabled', true);
			var originalText = $button.text();
			$button.data('original-text', originalText);
			$button.html('<span class="spinner" style="visibility: visible; margin: 0 5px;"></span> Processing...');
		},

		/**
		 * Hide loading state
		 */
		hideLoading: function($button) {
			$button.prop('disabled', false);
			var originalText = $button.data('original-text');
			if (originalText) {
				$button.text(originalText);
			}
		},

		/**
		 * Show admin notice
		 */
		showNotice: function(type, message) {
			var noticeClass = type === 'success' ? 'notice notice-success' : 
							   type === 'error' ? 'notice notice-error' : 
							   'notice notice-warning';
			
			var $notice = $('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
			
			// Add to notices container or create one
			var $noticesContainer = $('.wrap > .notice, .wrap > .updated, .wrap > .error');
			if ($noticesContainer.length) {
				$noticesContainer.first().before($notice);
			} else {
				$('.wrap h1').after($notice);
			}
			
			// Auto-dismiss after 5 seconds
			setTimeout(function() {
				$notice.fadeOut(500, function() {
					$(this).remove();
				});
			}, 5000);
		}

	};

	// Initialize
	KAB_Invoices_Admin.init();

});