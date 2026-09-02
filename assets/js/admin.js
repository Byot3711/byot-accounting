(function($) {
    'use strict';

    $(document).ready(function() {
        var ctx = document.getElementById('byotMainChart');
        if (!ctx) {
            return;
        }

        $.ajax({
            url: byotAjax.ajaxUrl,
            type: 'GET',
            data: {
                action: 'byot_get_chart_data',
                nonce: byotAjax.nonce,
                range: 'year'
            },
            success: function(response) {
                if (!response.success) {
                    return;
                }
                var data = response.data;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Vanzari',
                                data: data.sales,
                                borderColor: '#2271b1',
                                backgroundColor: 'rgba(34,113,177,0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Cheltuieli',
                                data: data.expenses,
                                borderColor: '#b32d2e',
                                backgroundColor: 'rgba(179,45,46,0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Achizitii',
                                data: data.purchases,
                                borderColor: '#40860d',
                                backgroundColor: 'rgba(64,134,13,0.1)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    });
})(jQuery);