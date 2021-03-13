(function(window, $) {
    $('#select_season').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction
        window.location.replace(Routing.generate('all_watch_index') + '?season=' + val)
    })
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.each( $.fn.dataTable.tables(true), function () {
            $(table).DataTable().columns.adjust();
        });
    })
    $('.all_watch_bar_chart').each( function () {
        const ctx = document.getElementById($(this).attr('id'))
        const data = $(this).data('scores')
        const maxScore = $(this).data('maxscore')
        let stepSize, maxChartTick
        if (maxScore < 6) {
            stepSize = 1
            maxChartTick = maxScore + 1
        } else {
            stepSize = Math.max(Math.floor(maxScore / 6), 1)
            maxChartTick = stepSize * (6 + 1)
        }
        // noinspection JSUnusedLocalSymbols
        const myChart = new Chart(ctx, {
            type: 'horizontalBar',
            defaults: {
                horizontalBar: {

                },
                global: {
                    title: {
                        display: false
                    }
                }
            },
            data: {
                labels: [
                    'Th8a should',
                    'Suggested',
                    'Watching',
                    'PTW',
                    'Dropped',
                    'Disliked'
                ],
                datasets: [{
                    data: data,
                    borderColor: '#aaaaaa',
                    borderWidth: 1,
                    backgroundColor: [
                        '#6f42c1',  // purple
                        '#198754',  // green
                        '#0d6efd',  // blue
                        '#0dcaf0',  // cyan
                        '#6c757d',  // gray-600
                        '#dc3545'   // red
                    ]
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {stepSize: stepSize, maxRotation: 0, min: 0, max: maxChartTick}
                    }],
                    yAxes: [{
                        gridLines: {display: false}
                    }]
                },
                responsive: false,
                legend: {
                    display: false
                }
            }
        })
    })
})(window, jQuery);
