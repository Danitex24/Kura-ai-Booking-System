document.addEventListener('DOMContentLoaded', function () {
    const deleteServiceLinks = document.querySelectorAll('.kab-delete-service');

    deleteServiceLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const serviceName = this.getAttribute('data-service-name');
            if (confirm(`Are you sure you want to delete the service "${serviceName}"?`)) {
                window.location.href = this.href;
            }
        });
    });

    const deleteEventLinks = document.querySelectorAll('.kab-delete-event');

    deleteEventLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const eventName = this.getAttribute('data-event-name');
            if (confirm(`Are you sure you want to delete the event \"${eventName}\"?`)) {
                window.location.href = this.href;
            }
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
});