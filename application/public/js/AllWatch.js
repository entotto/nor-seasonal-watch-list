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

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        $.each( $.fn.dataTable.tables(true), function () {
            // noinspection JSUnresolvedFunction
            $(table).DataTable().columns.adjust();
        });
    })

    const calcMaxChartTick = function (maxScoreCount) {
        return maxScoreCount
        // let maxChartTick
        // if (maxScoreCount < 6) {
        //     maxChartTick = maxScoreCount + 1
        // } else {
        //     const stepSize = Math.max(Math.floor(maxScoreCount / 6), 1)
        //     maxChartTick = stepSize * (6 + 1)
        // }
        // return maxChartTick
    }

    const whiten = (color, ratio) => d3.interpolateRgb(color, "#fff")(ratio);

    const calcDarkerColors = (barColors) => {
        // noinspection JSUnusedLocalSymbols
        return barColors.map((rgba, i) => {
            const hsl = d3.hsl(rgba);
            hsl.l -= isDarkMode ? 0.0 : 0.1;
            return hsl + "";
        });
    }

    let activityBarColors, activityTextColors;
    if (isDarkMode) {
        activityBarColors = [
            "#007eb9", // blue : watching/finished
            whiten("#007eb9", 0.5),  // weaker blue : ptw
        ]
        activityTextColors = [
            '#fff',
            '#000'
        ]
    } else {
        activityBarColors = [
            "#007eb9", // blue : watching/finished
            whiten("#007eb9", 0.5),  // weaker blue : ptw
        ]
        activityTextColors = [
            '#fff',
            '#fff'
        ]
    }

    const activityDarkerColors = calcDarkerColors(activityBarColors)

    $('.all_watch_bar_activity_chart').each( function () {
        const ctx = document.getElementById($(this).attr('id'))
        const data = $(this).data('scores')
        const maxActivityCount = $(this).data('maxactivitycount')
        const maxChartTick = calcMaxChartTick(maxActivityCount)

        // noinspection JSUnusedLocalSymbols
        const myChart = new Chart(ctx, {
            type: 'horizontalBar',
            plugins: [ChartDataLabels],
            defaults: {
                horizontalBar: {},
                global: {
                    title: {
                        display: false
                    },
                    defaultFontSize: 20
                }
            },
            data: {
                datasets: [
                    {
                        data: [data[1]],
                        borderColor: activityDarkerColors[1],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: activityBarColors[1],
                        datalabels: {
                            display: data[1] > 0,
                            color: activityTextColors[1]
                        }
                    },
                    {
                        data: [data[0]],
                        borderColor: activityDarkerColors[0],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: activityBarColors[0],
                        datalabels: {
                            display: data[0] > 0,
                            color: activityTextColors[0]
                        }
                    }
                ]
            },
            options: {
                layout: {
                    padding: {
                        left: -15
                    }
                },
                scales: {
                    xAxes: [
                        {
                            stacked: true,
                            display: false,
                            ticks: {
                                display: false,
                                max: maxChartTick
                            },
                        }
                    ],
                    yAxes: [
                        {
                            stacked: true,
                            gridLines: { display: false }
                        }
                    ]
                },
                plugins: {
                    datalabels: {
                        anchor: 'center',
                        font: {
                            size: 16,
                            weight: 'bold',
                            family: 'san-serif'
                        }
                    }
                },
                responsive: true,
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

    let scoreBarColors, scoreTextColors;
    if (isDarkMode) {
        scoreBarColors = [
            "#c90d0d",                         // th8a red / th8a should
            whiten("#c90d0d", 0.25), // weak red / highly favorable
            whiten("#c90d0d", 0.5),  // weaker red / favorable
            whiten("#000", 0.85),    // grey / neutral
            whiten("#000", 0.25)     // black / unfavorable
        ];
        // Use dark text on light background and vice versa.
        scoreTextColors = [
            "#fff",
            "#fff",
            "#000",
            "#000",
            "#fff"
        ];
    } else {
        scoreBarColors = [
            "#c90d0d",                         // th8a red / th8a should
            whiten("#c90d0d", 0.25), // weak red / highly favorable
            whiten("#c90d0d", 0.5),  // weaker red / favorable
            whiten("#000", 0.85),    // grey / neutral
            whiten("#000", 0.25)     // black / unfavorable
        ];
        // Use dark text on light background and vice versa.
        scoreTextColors = [
            "#fff",
            "#fff",
            "#fff",
            "#000",
            "#fff"
        ];
    }
    const scoreDarkerColors = calcDarkerColors(scoreBarColors);

    $('.all_watch_bar_score_chart').each( function () {
        const ctx = document.getElementById($(this).attr('id'))
        const data = $(this).data('scores')

        // noinspection JSUnusedLocalSymbols
        const myChart = new Chart(ctx, {
            type: 'horizontalBar',
            plugins: [ChartDataLabels],
            defaults: {
                horizontalBar: {},
                global: {
                    title: {
                        display: false
                    },
                    defaultFontSize: 20
                }
            },
            data: {
                datasets: [
                    {
                        data: [data[4]],
                        borderColor: scoreDarkerColors[4],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: scoreBarColors[4],
                        datalabels: {
                            display: data[4] > 0,
                            color: scoreTextColors[4]
                        }
                    },
                    {
                        data: [data[3]],
                        borderColor: scoreDarkerColors[3],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: scoreBarColors[3],
                        datalabels: {
                            display: data[3] > 0,
                            color: scoreTextColors[3]
                        }
                    },
                    {
                        data: [data[2]],
                        borderColor: scoreDarkerColors[2],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: scoreBarColors[2],
                        datalabels: {
                            display: data[2] > 0,
                            color: scoreTextColors[2]
                        }
                    },
                    {
                        data: [data[1]],
                        borderColor: scoreDarkerColors[1],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: scoreBarColors[1],
                        datalabels: {
                            display: data[1] > 0,
                            color: scoreTextColors[1]
                        }
                    },
                    {
                        data: [data[0]],
                        borderColor: scoreDarkerColors[0],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: scoreBarColors[0],
                        datalabels: {
                            display: data[0] > 0,
                            color: scoreTextColors[0]
                        }
                    }
                ]
            },
            options: {
                layout: {
                    padding: {
                        left: -15
                    }
                },
                scales: {
                    xAxes: [
                        {
                            stacked: true,
                            display: false,
                            ticks: {
                                display: false,
                                maxRotation: 0,
                                min: 0,
                                // max: maxChartTick,
                                fontSize: 20
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
                            stacked: true,
                            gridLines: { display: false }
                        }
                    ]
                },
                plugins: {
                    datalabels: {
                        anchor: 'center',
                        font: {
                            size: 16,
                            weight: 'bold',
                            family: 'san-serif'
                        }
                    }
                },
                responsive: true,
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

    // $('.mood-emoji-container').each( function () {
    //     const moodValue = $(this).data('moodValue')
    //     if (moodValue > 5) {
    //         $(this).css('color', '#ee9e47') // #eeb408
    //     } else if (moodValue > 1) {
    //         $(this).css('color', '#eecb62')
    //     } else if (moodValue > -1) {
    //         $(this).css('color', '#bbbbbb')
    //     } else {
    //         $(this).css('color', '#555555')
    //     }
    // })

    // Work around scroll-to-anchor bug in chrome
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    if (window.location.hash && isChrome) {
        setTimeout(function () {
            const hash = window.location.hash;
            window.location.hash = "";
            window.location.hash = hash;
        }, 900);
    }


})(window, jQuery);
