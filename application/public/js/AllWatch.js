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
        const maxChartTick = $(this).data('maxcharttick')
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
                    'Th8a',
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
                        '#198754',
                        '#0d6efd',
                        '#0d6efd',
                        '#0dcaf0',
                        '#6c757d',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {stepSize: 1, min: 0, max: maxChartTick}
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
