document.addEventListener('DOMContentLoaded', function () {
    const deleteServiceLinks = document.querySelectorAll('.kab-delete-service');

    deleteServiceLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const serviceName = this.getAttribute('data-service-name');
            
            Swal.fire({
                title: 'Delete Service',
                html: `Are you sure you want to delete the service <strong>${serviceName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'kab-btn kab-btn-danger',
                    cancelButton: 'kab-btn kab-btn-outline'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = this.href;
                }
            });
        });
    });

    const deleteEventLinks = document.querySelectorAll('.kab-delete-event');

    deleteEventLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const eventName = this.getAttribute('data-event-name');
            
            Swal.fire({
                title: 'Delete Event',
                html: `Are you sure you want to delete the event <strong>${eventName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'kab-btn kab-btn-danger',
                    cancelButton: 'kab-btn kab-btn-outline'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = this.href;
                }
            });
        });
    });

    const validationForm = document.getElementById('kab-validate-ticket-form');
    if (validationForm) {
        validationForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const ticketId = document.getElementById('ticket_id').value;
            const resultDiv = document.getElementById('kab-validation-result');
            resultDiv.style.display = 'none';

            if (!ticketId) {
                return;
            }

            const apiUrl = `/wp-json/kuraai/v1/validate-ticket/${ticketId}`;

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    resultDiv.style.display = 'block';
                    if (data.valid) {
                        resultDiv.className = 'notice notice-success';
                        resultDiv.innerHTML = `<p><strong>${data.status.toUpperCase()}:</strong> Ticket is valid.</p>`
                        + `<p><strong>Booking ID:</strong> ${data.details.booking_id}</p>`
                        + `<p><strong>Ticket ID:</strong> ${data.details.ticket_id}</p>`
                        + `<p><strong>Status:</strong> ${data.details.status}</p>`;
                    } else {
                        resultDiv.className = 'notice notice-error';
                        resultDiv.innerHTML = `<p><strong>${data.status.toUpperCase()}:</strong> ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'notice notice-error';
                    resultDiv.innerHTML = `<p>An error occurred: ${error}</p>`;
                });
        });
    }

    // Header dropdowns (click/touch friendly)
    const navGroups = document.querySelectorAll('.kab-nav-group');
    navGroups.forEach(group => {
        const trigger = group.querySelector('.kab-nav-link');
        if (!trigger) return;
        trigger.addEventListener('click', function (e) {
            // Toggle dropdown instead of navigating
            e.preventDefault();
            const isOpen = group.classList.contains('open');
            document.querySelectorAll('.kab-nav-group.open').forEach(g => g.classList.remove('open'));
            if (!isOpen) group.classList.add('open');
        });
    });
    document.addEventListener('click', function (e) {
        const inside = e.target.closest('.kab-nav-group');
        if (!inside) {
            document.querySelectorAll('.kab-nav-group.open').forEach(g => g.classList.remove('open'));
        }
    });

    // Settings forms SweetAlert confirmation
    const settingForms = document.querySelectorAll('.kab-settings-card form');
    settingForms.forEach(form => {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const titleEl = form.closest('.kab-settings-card').querySelector('.kab-card-title');
            const section = titleEl ? titleEl.textContent.trim() : 'Settings';
            const submitBtn = form.querySelector('button[type="submit"]');
            const proceed = () => { if (submitBtn) submitBtn.classList.add('kab-loading'); form.submit(); };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Save ' + section + '?',
                    text: 'Apply changes to ' + section + ' now.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    cancelButtonText: 'Cancel',
                    customClass: { confirmButton: 'kab-btn kab-btn-primary', cancelButton: 'kab-btn kab-btn-outline' },
                    buttonsStyling: false
                }).then(res => { if (res.isConfirmed) proceed(); });
            } else {
                proceed();
            }
        });
    });

    // Settings success notice
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.get('settings-updated') === 'true' || params.get('settings-updated') === '1') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Settings saved', icon: 'success', timer: 1800, showConfirmButton: false });
            }
        }
    } catch (err) {}
});
