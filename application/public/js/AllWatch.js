(function(window, $) {
    // import * as d3Color from "https://cdn.skypack.dev/d3-color@2.0.0";
    // import * as d3Interpolate from "https://cdn.skypack.dev/d3-interpolate@2.0.1";
    // import ChartDataLabels from 'https://cdn.skypack.dev/chartjs-plugin-datalabels@1.0.0-beta.1';

    // chartjs-plugin-datalabels: https://chartjs-plugin-datalabels.netlify.app/guide/getting-started.html
    Chart.helpers.merge(Chart.defaults.global.plugins.datalabels, {
        anchor: "end",
        // Don't render datalabel when the value is 0.
        // `context.active && ...` to only show on hover. (It feels too busy though)
        // Trying out the datalabel-always-outside strategy
        //display: (context) => context.dataset.data[context.dataIndex] !== 0
    });

    $('#select_season').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace(Routing.generate('all_watch_index') + '?season=' + val)
    })

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.each( $.fn.dataTable.tables(true), function () {
            // noinspection JSUnresolvedFunction
            $(table).DataTable().columns.adjust();
        });
    })

    $('#select_show').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace(Routing.generate('all_watch_index') + '#' + val)
    })

    const calcMaxChartTick = function (maxScore) {
        let maxChartTick
        if (maxScore < 6) {
            maxChartTick = maxScore + 1
        } else {
            const stepSize = Math.max(Math.floor(maxScore / 6), 1)
            maxChartTick = stepSize * (6 + 1)
        }
        return maxChartTick
    }

    const whiten = (color, ratio) => d3.interpolateRgb(color, "#fff")(ratio);

    const calcTextColors = (barColors) => {
        return barColors.map((rgba) => {
            const hsl = d3.hsl(rgba);
            //const isLight = hsl.l > 0.5;
            // Trying out the datalabel-always-outside strategy ('outside' is always light, unless we support dark mode)
            const isLight = true;
            hsl.l += isLight ? -0.6 : 0.6;
            return hsl + "";
        });
    }

    const calcDarkerColors = (barColors) => {
        return barColors.map((rgba, i) => {
            const hsl = d3.hsl(rgba);
            hsl.l -= 0.1;
            return hsl + "";
        });
    }

    const isBarTooNarrow = (context) => {
        // Trying out the datalabel-always-outside strategy
        return true;
        // const currentValue = context.dataset.data[context.dataIndex];
        // const yAxisLabelWidth = 60; // magic number
        // const totalWidth = Number($(this).attr("width"));
        // const approxBarLength = (totalWidth - yAxisLabelWidth) * currentValue / maxChartTick;
        //// more magic numbers.. maybe it's better to always render the datalabel outside the bar
        // return currentValue.toString().length * 10 + 26 > approxBarLength;
    }

    $('.all_watch_bar_activity_chart').each( function () {
        const ctx = document.getElementById($(this).attr('id'))
        const data = $(this).data('scores')
        const maxScore = $(this).data('maxscore')
        const maxChartTick = calcMaxChartTick(maxScore)
        const barColors = [
            "#007eb9", // blue / finished
            whiten("#007eb9", 0.25), // mid blue / watching
            whiten("#007eb9", 0.5),  // weaker blue / paused
            whiten("#007eb9", 0.75), // weakest blue / ptw
            whiten("#000", 0.85)     // grey / dropped
        ];
        // Use dark text on light background and vice versa.
        const textColors = calcTextColors(barColors);
        const darkerColors = calcDarkerColors(barColors);

        // noinspection JSUnusedLocalSymbols
        const myChart = new Chart(ctx, {
            type: 'horizontalBar',
            plugins: [ChartDataLabels],
            defaults: {
                horizontalBar: {},
                global: {
                    title: {
                        display: false
                    }
                }
            },
            data: {
                labels: [
                    'Finished',
                    'Watching',
                    'Paused',
                    'PTW',
                    'Dropped'
                ],
                datasets: [{
                    data: data,
                    borderColor: darkerColors,
                    borderSkipped: false,
                    borderWidth: 1,
                    backgroundColor: barColors,
                    categoryPercentage: 0.9, // Tighten up the space between bars
                    datalabels: {
                        color: textColors,
                        align: (context) => isBarTooNarrow(context) ? "end" : "start"
                    }
                }]
            },
            options: {
                scales: {
                    xAxes: [
                        {
                            display: true,
                            ticks: {
                                display: false,
                                maxRotation: 0,
                                min: 0,
                                max: maxChartTick
                            },
                            gridLines: {
                                drawTicks: false,
                                lineWidth: 0,
                                zeroLineWidth: 1
                            }
                        }
                    ],
                    yAxes: [
                        {
                            gridLines: { display: false }
                        }
                    ]
                },
                responsive: false,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: false
                },
                animation: {
                    duration: 0
                }
            }
        })
    })

    $('.all_watch_bar_score_chart').each( function () {
        const ctx = document.getElementById($(this).attr('id'))
        const data = $(this).data('scores')
        const maxScore = $(this).data('maxscore')
        const maxChartTick = calcMaxChartTick(maxScore)
        const barColors = [
            "#c80d0d",                         // th8a red / th8a should
            whiten("#c80d0d", 0.25), // weak red / highly favorable
            whiten("#c80d0d", 0.5),  // weaker red / favorable
            whiten("#000", 0.85),    // grey / neutral
            whiten("#000", 0.25)      // black / unfavorable
        ];
        // Use dark text on light background and vice versa.
        const textColors = calcTextColors(barColors);
        const darkerColors = calcDarkerColors(barColors);

        // noinspection JSUnusedLocalSymbols
        const myChart = new Chart(ctx, {
            type: 'horizontalBar',
            plugins: [ChartDataLabels],
            defaults: {
                horizontalBar: {},
                global: {
                    title: {
                        display: false
                    }
                }
            },
            data: {
                labels: [
                    'Th8a should',
                    'Highly favorable',
                    'Favorable',
                    'Neutral',
                    'Unfavorable'
                ],
                datasets: [
                    {
                        data: data,
                        borderColor: darkerColors,
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: barColors,
                        categoryPercentage: 0.9,
                        datalabels: {
                            color: textColors,
                            align: (context) => isBarTooNarrow(context) ? "end" : "start"
                        }
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [
                        {
                            display: true,
                            ticks: {
                                display: false,
                                maxRotation: 0,
                                min: 0,
                                max: maxChartTick
                            },
                            gridLines: {
                                drawTicks: false,
                                lineWidth: 0,
                                zeroLineWidth: 1
                            }
                        }
                    ],
                    yAxes: [
                        {
                            gridLines: { display: false }
                        }
                    ]
                },
                responsive: false,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: false
                },
                animation: {
                    duration: 0
                }
            }
        })
    })

    const containerValues = {}
    let currentAnchorId = ''
    const changeSelector = function () {
        // noinspection JSUnresolvedVariable
        const anchorId = document.querySelector('#' + getKeyForMax(containerValues)).dataset.anchorid
        if (currentAnchorId !== anchorId) {
            currentAnchorId = anchorId
            document.querySelector('#select_show [value="show_target_' + anchorId + '"]').selected = true
        }
    }
    const showContainers = document.querySelectorAll('.show_container')
    const observer = new IntersectionObserver(function (entries) {
        for (const entry of entries) {
            // if (entry['isIntersecting'] === true) {
                containerValues[entry['target'].id] = entry['intersectionRatio']
                setTimeout(() => {changeSelector()}, 1000)
            // }
        }
    }, { threshold: [0.2, 0.4, 0.6, 0.8]})
    showContainers.forEach(showContainer => {
        observer.observe(showContainer)
    })

    const getKeyForMax = function (dict) {
        let keyForMax = '';
        let max = 0;
        for (const [key, value] of Object.entries(dict)) {
            if (value > max) {
                max = value
                keyForMax = key
            }
        }
        return keyForMax
    }
})(window, jQuery);
