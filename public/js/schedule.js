// ============================================
// SCHEDULE MANAGEMENT
// ============================================

class ScheduleManager {
    constructor() {
        this.scheduleView = document.querySelector('[data-schedule-view]');
        this.init();
    }

    init() {
        if (this.scheduleView) {
            this.renderScheduleView();
        }
    }

    renderScheduleView() {
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        const dayNames = {
            'monday': 'Lundi',
            'tuesday': 'Mardi',
            'wednesday': 'Mercredi',
            'thursday': 'Jeudi',
            'friday': 'Vendredi',
            'saturday': 'Samedi'
        };

        let html = '<table class="table table-bordered">';
        html += '<tr><th>Jour</th><th>Matière</th><th>Enseignant</th><th>Heure</th><th>Salle</th></tr>';

        days.forEach(day => {
            const daySchedules = document.querySelectorAll(`[data-schedule-day="${day}"]`);
            
            if (daySchedules.length > 0) {
                daySchedules.forEach((schedule, index) => {
                    html += '<tr>';
                    if (index === 0) {
                        html += `<td rowspan="${daySchedules.length}"><strong>${dayNames[day]}</strong></td>`;
                    }
                    html += `<td>${schedule.dataset.subject}</td>`;
                    html += `<td>${schedule.dataset.teacher}</td>`;
                    html += `<td>${schedule.dataset.startTime} - ${schedule.dataset.endTime}</td>`;
                    html += `<td>${schedule.dataset.room}</td>`;
                    html += '</tr>';
                });
            }
        });

        html += '</table>';
        this.scheduleView.innerHTML = html;
    }

    exportSchedule() {
        const table = this.scheduleView.querySelector('table');
        exportTableToCSV(`[data-schedule-view] table`, 'emploi-du-temps.csv');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-schedule-view]')) {
        new ScheduleManager();
    }
});
