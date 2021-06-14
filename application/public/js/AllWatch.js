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

    // $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
    //     $.each( $.fn.dataTable.tables(true), function () {
    //         // noinspection JSUnresolvedFunction
    //         $(table).DataTable().columns.adjust();
    //     });
    // })

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
            hsl.l -= (!isDarkMode && i === 3) ? 0.1 : 0.0;
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
        $(ctx).attr("height", "50")
        const categoryPercentage = 0.6

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
                        },
                        categoryPercentage,
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
                        },
                        categoryPercentage,
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
                            display: true,
                            ticks: {
                                display: false,
                                max: maxChartTick
                            },
                            gridLines: {
                                drawTicks: false,
                                lineWidth: 0,
                                zeroLineWidth: 1,
                                zeroLineColor: isDarkMode ? '#777' : '#aaa'
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

    let scoreBarColors, scoreTextColors;
    if (isDarkMode) {
        scoreBarColors = [
            "#c90d0d",                         // th8a red / th8a should
            whiten("#c90d0d", 0.25), // weak red / highly favorable
            whiten("#c90d0d", 0.5),  // weaker red / favorable
            whiten("#000", 1.0),    // white / neutral
            whiten("#000", 0.25)     // black / unfavorable
        ];
        // Use dark text on light background and vice versa.
        scoreTextColors = [
            "#fff",
            "#fff",
            "#000",
            "#444",
            "#fff"
        ];
    } else {
        scoreBarColors = [
            "#c90d0d",                         // th8a red / th8a should
            whiten("#c90d0d", 0.25), // weak red / highly favorable
            whiten("#c90d0d", 0.5),  // weaker red / favorable
            whiten("#000", 1.0),    // white / neutral
            whiten("#000", 0.25)     // black / unfavorable
        ];
        // Use dark text on light background and vice versa.
        scoreTextColors = [
            "#fff",
            "#fff",
            "#fff",
            "#444",
            "#fff"
        ];
    }
    const scoreDarkerColors = calcDarkerColors(scoreBarColors);

    $('.all_watch_bar_score_chart').each( function () {
        // // patch for making this runnable as a snippet: completely replace the canvas element
        // const canvas = document.getElementById($(this).attr('id'))
        // const parent = $(canvas).parent()
        // const ctx = $(canvas).clone().appendTo(parent).get()
        // $(canvas).remove()
        const ctx = document.getElementById($(this).attr('id'))
        const data = $(this).data('scores')
        const maxChartTick = $(this).data('maxscorecount')
        const minChartTick = $(this).data('minscorecount') * -1
        // make the zero-line of the x-axis longer: taller height + smaller category percentage
        // numbers are somewhat arbitrary / not pixel perfect
        $(ctx).attr("height", "50")
        const categoryPercentage = 0.6
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
                        data: [-data[4]],
                        borderColor: scoreDarkerColors[4],
                        borderSkipped: false,
                        borderWidth: 1,
                        backgroundColor: scoreBarColors[4],
                        datalabels: {
                            display: data[4] > 0,
                            color: scoreTextColors[4],
                            formatter: function(value) {
                                return value * -1
                            }
                        },
                        categoryPercentage,
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
                        },
                        categoryPercentage,

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
                        },
                        categoryPercentage,
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
                        },
                        categoryPercentage,
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
                        },
                        categoryPercentage,
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
                            display: true,
                            ticks: {
                                display: false,
                                maxRotation: 0,
                                min: minChartTick,
                                max: maxChartTick, // data[0] + data[1] + data[2] + data[3] + data[4],
                                fontSize: 20
                            },
                            gridLines: {
                                drawTicks: false,
                                lineWidth: 0,
                                zeroLineWidth: 1,
                                zeroLineColor: isDarkMode ? '#777' : '#aaa'
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

    $('#select_mode').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        window.location.replace('?mode=' + val)
    })

})(window, jQuery);
