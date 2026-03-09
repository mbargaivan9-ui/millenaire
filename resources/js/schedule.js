/**
 * Schedule & Timetable Management
 * Millénaire Connect
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeScheduleFilters();
    initializeTimeValidation();
    initializeScheduleCalendar();
});

/**
 * Initialize Schedule Filters
 */
function initializeScheduleFilters() {
    const classSelect = document.querySelector('select[name="classe_id"]');
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            const form = this.closest('form');
            form.submit();
        });
    }
}

/**
 * Initialize Time Validation
 */
function initializeTimeValidation() {
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');

    if (startTimeInput && endTimeInput) {
        endTimeInput.addEventListener('change', function() {
            validateScheduleTime(startTimeInput.value, this.value);
        });

        startTimeInput.addEventListener('change', function() {
            validateScheduleTime(this.value, endTimeInput.value);
        });
    }
}

/**
 * Validate Schedule Time Range
 */
function validateScheduleTime(startTime, endTime) {
    if (!startTime || !endTime) return;

    const start = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);

    const endInput = document.querySelector('input[name="end_time"]');
    const feedback = endInput.parentElement.querySelector('.invalid-feedback') || 
                    document.createElement('div');

    if (end <= start) {
        endInput.classList.add('is-invalid');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = 'L\'heure de fin doit être après l\'heure de début';
        if (!endInput.parentElement.querySelector('.invalid-feedback')) {
            endInput.parentElement.appendChild(feedback);
        }
    } else {
        endInput.classList.remove('is-invalid');
        endInput.classList.add('is-valid');
        if (feedback.parentElement) feedback.remove();
    }
}

/**
 * Initialize Schedule Calendar View
 */
function initializeScheduleCalendar() {
    const calendarContainer = document.getElementById('scheduleCalendar');
    if (!calendarContainer) return;

    const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    const timeSlots = generateTimeSlots('08:00', '18:00', 60); // 60-minute intervals

    let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
    html += '<thead><tr><th style="min-width: 80px">Heure</th>';
    days.forEach(day => {
        html += `<th>${day}</th>`;
    });
    html += '</tr></thead><tbody>';

    timeSlots.forEach(time => {
        html += `<tr><td class="fw-bold bg-light">${time}</td>`;
        days.forEach(day => {
            html += `<td data-day="${day}" data-time="${time}" class="schedule-slot"></td>`;
        });
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    calendarContainer.innerHTML = html;

    loadScheduleIntoCalendar();
}

/**
 * Generate Time Slots
 */
function generateTimeSlots(startTime, endTime, intervalMinutes) {
    const slots = [];
    let current = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);

    while (current <= end) {
        const hours = String(current.getHours()).padStart(2, '0');
        const minutes = String(current.getMinutes()).padStart(2, '0');
        slots.push(`${hours}:${minutes}`);
        current.setMinutes(current.getMinutes() + intervalMinutes);
    }

    return slots;
}

/**
 * Load Schedule into Calendar
 */
function loadScheduleIntoCalendar() {
    const slots = document.querySelectorAll('.schedule-slot');
    
    // Fetch schedule data
    fetch('/api/admin/schedule/calendar', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        data.schedules.forEach(schedule => {
            const slot = document.querySelector(
                `.schedule-slot[data-day="${schedule.day}"][data-time="${schedule.time}"]`
            );
            if (slot) {
                slot.innerHTML = `
                    <small><strong>${schedule.subject}</strong><br>
                    ${schedule.teacher}</small>
                `;
                slot.style.backgroundColor = 'rgba(79, 70, 229, 0.1)';
                slot.style.borderLeft = '3px solid #4F46E5';
            }
        });
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Export Schedule as iCal
 */
function exportScheduleAsIcal(classeId) {
    const link = document.createElement('a');
    link.href = `/admin/schedule/export/${classeId}`;
    link.download = 'emploi_temps.ics';
    link.click();
}

/**
 * Print Timetable
 */
function printTimetable() {
    const table = document.querySelector('table');
    if (!table) return;

    const printWindow = window.open('', '', 'width=900,height=700');
    printWindow.document.write(`
        <html>
        <head>
            <title>Emploi du Temps</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        </head>
        <body>
            <h2 class="mb-3">Emploi du Temps</h2>
            ${table.outerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Conflict Detection
 */
function checkScheduleConflict(teacherId, classeId, dayOfWeek, startTime, endTime) {
    fetch('/api/admin/schedule/check-conflict', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            teacher_id: teacherId,
            classe_id: classeId,
            day_of_week: dayOfWeek,
            start_time: startTime,
            end_time: endTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.conflict) {
            showWarningMessage(`Conflit détecté: ${data.message}`);
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Show Warning Message
 */
function showWarningMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-warning alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.insertBefore(alertDiv, document.body.firstChild);
}
