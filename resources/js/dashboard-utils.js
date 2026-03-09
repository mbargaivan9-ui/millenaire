/**
 * Millénaire Connect - Dashboard Charts
 * Gère les graphiques et visualisations des tableaux de bord
 */

(function() {
    'use strict';

    const ChartManager = {
        charts: {},

        /**
         * Initialiser les graphiques Chart.js
         */
        initCharts() {
            const chartElements = document.querySelectorAll('[data-chart-type]');
            chartElements.forEach(element => {
                const type = element.dataset.chartType;
                const dataAttr = element.dataset.chartData;

                if (window.Chart && dataAttr) {
                    try {
                        const data = JSON.parse(dataAttr);
                        this.createChart(element, type, data);
                    } catch (e) {
                        console.error('Erreur parsing données graphique:', e);
                    }
                }
            });
        },

        createChart(element, type, data) {
            if (!window.Chart) {
                console.warn('Chart.js non disponible');
                return;
            }

            const ctx = element.getContext('2d');
            const chartId = element.id;

            // Destroyer ancien graphique si existe
        } 
    }
})