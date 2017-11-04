/**
 * Created by Jeremy on 2017/10/6.
 */
// 显示企业主题分布气泡图
function showCompanyBubble(params) {
    params = params || [];
    console.log(JSON.stringify(params));
    var chartDom = document.getElementById('company_theme_bubble_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    $.get('/DataMonitor/getBubbleData', params, function (res) {
        $('#searching_icon').hide();
        if (res.errorcode == 0) {
            console.log(res.data);
            myChart.hideLoading();
            var res_data = res.data;
            var bubble_data = [];
            for (var i = 0; i < res_data.length; i++) {
                bubble_data.push([i, res_data[i]['id'], res_data[i]['num'], res_data[i]['name']]);
            }

            // 获取主题数量
            var company_name = [];
            var t_count = [];
            var c_id = [];
            for (var i = 0; i < bubble_data.length; i++) {
                company_name.push(bubble_data[i][3]);
                t_count.push(bubble_data[i][2]);
                c_id.push(bubble_data[i][0]);
            }

            var itemStyle = {
                normal: {
                    opacity: 0.8,
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowOffsetY: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            };

            var option = {
                backgroundColor: '#fff',
                color: [
                    '#dd4444', '#fec42c', '#80F1BE'
                ],
                legend: {
                    y: 'top',
                    data: ['企业主题数气泡图'],
                    textStyle: {
                        color: 'black',
                        fontSize: 12
                    }
                },
                grid: {
                    x: '10%',
                    x2: 10,
                    y: '18%',
                    y2: '10%'
                },
                tooltip: {
                    padding: 0,
                    backgroundColor: '#222',
                    borderColor: '#777',
                    borderWidth: 1,
                    formatter: function (obj) {
                        var value = obj.value;
                        return '<div style="font-size: 10px;padding-bottom: 7px;margin-bottom: 7px">'
                            + '企业名称：' + value[3] + '  主题数量：' + value[2]
                            + '</div>'
                    }
                },
                xAxis: {
                    type: 'category',
                    name: '企业名称',
                    nameRotate: 0,
                    nameTextStyle: {
                        color: 'black',
                        fontSize: 12
                    },
                    max: bubble_data.length,
                    splitLine: {
                        show: false
                    },
                    axisLine: {
                        lineStyle: {
                            color: 'black'
                        }
                    },
                    axisLabel: {
                        rotate: 0
                    },
                    data: company_name
                },
                yAxis: {
                    type: 'value',
                    name: '主题数量',
                    nameLocation: 'end',
                    nameTextStyle: {
                        color: 'black',
                        fontSize: 12
                    },
                    axisLine: {
                        lineStyle: {
                            color: 'black'
                        }
                    },
                    splitLine: {
                        show: false
                    }
                },
                visualMap: [
                    {
                        show: false,
                        left: 'right',
                        top: '1%',
                        dimension: 2,
                        min: 0,
                        max: Math.max.apply(null, t_count),
                        itemWidth: 16,
                        itemHeight: 100,
                        calculable: true,
                        precision: 0,
                        text: ['圆形大小：记录数量'],
                        textGap: 10,
                        textStyle: {
                            color: 'black'
                        },
                        inRange: {
                            symbolSize: [10, 50]
                        },
                        outOfRange: {
                            symbolSize: [50, 55],
                            color: ['rgba(255,255,255,.2)']
                        },
                        controller: {
                            inRange: {
                                color: ['#c23531']
                            },
                            outOfRange: {
                                color: ['#444']
                            }
                        }
                    },
                    {
                        show: false,
                        left: 'right',
                        bottom: '2%',
                        dimension: 0,
                        min: 0,
                        max: Math.max.apply(null, c_id),
                        itemHeight: 100,
                        calculable: true,
                        precision: 0,
                        text: ['颜色深浅：主题类别'],
                        textGap: 10,
                        textStyle: {
                            color: 'black'
                        },
                        inRange: {
                            colorLightness: [1, 0.5]
                        },
                        outOfRange: {
                            color: ['rgba(255,255,255,.2)']
                        },
                        controller: {
                            inRange: {
                                color: ['#c23531']
                            },
                            outOfRange: {
                                color: ['#444']
                            }
                        }
                    }
                ],
                dataZoom: [
                    {
                        type: 'slider',
                        show: true,
                        bottom: '1%',
                        height: 10,
                        start: 0,
                        end: 100,
                        handleSize: 5,
                        textStyle: {
                            color: 'black'
                        }
                    },
                    {
                        type: 'inside',
                        start: 94,
                        end: 100
                    },
                    {
                        type: 'slider',
                        show: true,
                        yAxisIndex: 0,
                        filterMode: 'empty',
                        width: 10,
                        height: '70%',
                        handleSize: 5,
                        showDataShadow: false,
                        left: '1%',
                        textStyle: {
                            color: 'black'
                        }
                    }
                ],
                series: [
                    {
                        name: '企业主题数气泡图',
                        type: 'scatter',
                        itemStyle: itemStyle,
                        data: bubble_data
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
            myChart.on('click', function (param) {
                var company_id =  param.value[1];
                console.log(JSON.stringify(param.value));
                window.location.href = '/Company/info?id='+company_id;
            });
        } else {
            popWarn(res.msg);
        }
    });
}

// 显示企业主题数量
function showThemeBar(params) {
    params = params || [];
    console.log(JSON.stringify(params));
    var chartDom = document.getElementById('theme_company_count_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    params['obj'] = 'theme';
    $.get('/DataMonitor/getBarData', params, function (res) {
        $('#searching_icon').hide();
        var chart_title = '所有主题数据';
        if (params['bar_theme_limit'] != -1) {
            chart_title = $('#bar_theme_name').text();
        }
        if (res.errorcode == 0) {
            myChart.hideLoading();
            var res_data = res.data;
            var bar_data = [];
            for (var i = 0; i < res_data.length; i++) {
                bar_data.push([res_data[i]['id'], res_data[i]['num'], res_data[i]['name']]);
            }

            // 获取主题数量
            var company_name = [];
            var t_count = [];
            var t1_id = [];
            for (var i = 0; i < bar_data.length; i++) {
                company_name.push(bar_data[i][2]);
                t_count.push(bar_data[i][1]);
                t1_id.push(bar_data[i][5])
            }

            var option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow',
                        label: {
                            show: true
                        }
                    }
                },
                toolbox: {
                    show: true,
                    feature: {
                        magicType: {show: true, type: ['line', 'bar']},
                        restore: {show: true}
                    }
                },
                calculable: true,
                legend: {
                    data: [chart_title],
                    itemGap: 5
                },
                grid: {
                    x: '10%',
                    x2: 10,
                    y: '18%',
                    y2: '10%'
                },
                xAxis: [
                    {
                        type: 'category',
                        data: company_name
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '数据量'
                    }
                ],

                dataZoom: [
                    {
                        show: true,
                        start: 0,
                        end: 100,
                        bottom: '1%',
                        height: 10,
                        handleSize: 5,
                        textStyle: {
                            color: 'white'
                        }
                    },
                    {
                        type: 'inside',
                        start: 0,
                        end: 100
                    },
                    {
                        show: true,
                        yAxisIndex: 0,
                        filterMode: 'empty',
                        width: 10,
                        height: '70%',
                        showDataShadow: false,
                        left: '1%'
                    }
                ],
                series: [
                    {
                        name: chart_title,
                        type: 'bar',
                        data: t_count
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
        } else {
            popWarn(res.msg);
        }
    });
}

// 显示企业数量柱状图
function showCompanyBar(params) {
    params = params || [];
    console.log(JSON.stringify(params));
    var chartDom = document.getElementById('theme_company_count_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    $.get('/DataMonitor/getBarData', params, function (res) {
        $('#searching_icon').hide();
        var chart_title = '所有主题数据';
        if (params['bar_theme_limit'] != -1) {
            // alert(params['bar_theme_limit']);
            chart_title = $('#company_bar_theme_name').text();
        }
        console.log(chart_title);
        if (res.errorcode == 0) {
            myChart.hideLoading();
            var res_data = res.data;
            var bar_data = [];
            for (var i = 0; i < res_data.length; i++) {
                bar_data.push([res_data[i]['id'], res_data[i]['num'], res_data[i]['name']]);
            }

            // 获取主题数量
            var company_name = [];
            var t_count = [];
            var t1_id = [];
            for (var i = 0; i < bar_data.length; i++) {
                company_name.push(bar_data[i][2]);
                t_count.push(bar_data[i][1]);
                t1_id.push(bar_data[i][5])
            }

            var option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow',
                        label: {
                            show: true
                        }
                    }
                },
                toolbox: {
                    show: true,
                    feature: {
                        magicType: {show: true, type: ['line', 'bar']},
                        restore: {show: true}
                    }
                },
                calculable: true,
                legend: {
                    data: [chart_title],
                    itemGap: 5
                },
                grid: {
                    x: '10%',
                    x2: 10,
                    y: '18%',
                    y2: '10%'
                },
                xAxis: [
                    {
                        type: 'category',
                        data: company_name
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '数据量'
                    }
                ],

                dataZoom: [
                    {
                        show: true,
                        start: 0,
                        end: 100,
                        bottom: '1%',
                        height: 10,
                        handleSize: 5,
                        textStyle: {
                            color: 'white'
                        }
                    },
                    {
                        type: 'inside',
                        start: 0,
                        end: 100
                    },
                    {
                        show: true,
                        yAxisIndex: 0,
                        filterMode: 'empty',
                        width: 10,
                        height: '70%',
                        showDataShadow: false,
                        left: '1%'
                    }
                ],
                series: [
                    {
                        name: chart_title,
                        type: 'bar',
                        data: t_count
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
        } else {
            popWarn(res.msg);
        }
    });
}

// 显示爬取企业排名信息
function showCompanyRankPie(params) {
    params = params || [];
    var chartDom = document.getElementById('company_rank_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    $.get('/Company/companyRankInfo', params, function (res) {
        if (res.errorcode == 0) {
            myChart.hideLoading();
            var res_data = res.data;
            var data = [];
            var items = [];
            for (var i = 0; i < res_data.length; i++) {
                data.push({value: res_data[i]['c_count'], name: res_data[i]['item']});
                items.push(res_data[i]['item']);
            }
            var option = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    x: 'center',
                    y: 'bottom',
                    data: items
                },
                calculable: true,
                series: [
                    {
                        name: '企业属性',
                        type: 'pie',
                        radius: [10, 110],
                        center: ['50%', '40%'],
                        roseType: 'area',
                        data: data
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
        } else {
            popWarn(res.msg);
        }
    });
}

// 显示主题企业分布气泡图
function showThemeBubble(params) {
    params = params || [];
    var chartDom = document.getElementById('theme_company_bubble_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    console.log(JSON.stringify(params));
    $.get('/Theme/bubbleList', params, function (res) {
        if (res.errorcode == 0) {
            myChart.hideLoading();
            console.log(res.data);
            var res_data = res.data;
            var bubble_data = [];
            for (var i = 0; i < res_data.length; i++) {
                bubble_data.push([i, res_data[i]['t3_id'], res_data[i]['c_count'],
                    res_data[i]['t3_name'], res_data[i]['t2_name'], res_data[i]['t1_name'], res_data[i]['t1_id']]);
            }

            // 获取主题数量
            var theme_name = [];
            var c_count = [];
            var t1_id = [];
            for (var i = 0; i < bubble_data.length; i++) {
                theme_name.push(bubble_data[i][3]);
                c_count.push(bubble_data[i][2]);
                t1_id.push(bubble_data[i][6])
            }

            var schema = [
                {name: 'theme_id', index: 0, text: '主题排名'},
                {name: 'theme_name', index: 1, text: ''},
                {name: 'theme_1', index: 2, text: '一级主题'},
                {name: 'theme_2', index: 3, text: '二级主题'},
                {name: 'theme_3', index: 4, text: '三级主题'},
                {name: 'c_count', index: 5, text: '记录数量'}
            ];

            var itemStyle = {
                normal: {
                    opacity: 0.8,
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowOffsetY: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            };

            var option = {
                backgroundColor: '#fff',
                color: [
                    '#dd4444', '#fec42c', '#80F1BE'
                ],
                legend: {
                    y: 'top',
                    data: ['主题分布气泡图'],
                    textStyle: {
                        color: 'black',
                        fontSize: 12
                    }
                },
                grid: {
                    x: '10%',
                    x2: 10,
                    y: '18%',
                    y2: '10%'
                },
                tooltip: {
                    padding: 10,
                    backgroundColor: '#222',
                    borderColor: '#777',
                    borderWidth: 1,
                    formatter: function (obj) {
                        var value = obj.value;
                        return '<div style="border-bottom: 1px solid rgba(255,255,255,.3); font-size: 12px;padding-bottom: 7px;margin-bottom: 7px">'
                            + '主题名：' + value[3] + '  数量：' + value[2]
                            + '</div>'
                            + schema[2].text + '：' + value[4] + '<br>'
                            + schema[3].text + '：' + value[3] + '<br>'
                    }
                },
                xAxis: {
                    type: 'category',
                    name: '主题名称',
                    nameRotate: 0,
                    nameTextStyle: {
                        color: 'black',
                        fontSize: 12
                    },
                    max: bubble_data.length,
                    splitLine: {
                        show: false
                    },
                    axisLine: {
                        lineStyle: {
                            color: 'black'
                        }
                    },
                    axisLabel: {
                        rotate: 0
                    },
                    data: theme_name
                },
                yAxis: {
                    type: 'value',
                    name: '主题数量',
                    nameLocation: 'end',
                    nameTextStyle: {
                        color: 'black',
                        fontSize: 12
                    },
                    axisLine: {
                        lineStyle: {
                            color: 'black'
                        }
                    },
                    splitLine: {
                        show: false
                    }
                },
                visualMap: [
                    {
                        show: false,
                        left: 'right',
                        top: '1%',
                        dimension: 2,
                        min: 0,
                        max: Math.max.apply(null, c_count),
                        itemWidth: 16,
                        itemHeight: 100,
                        calculable: true,
                        precision: 0,
                        text: ['圆形大小：记录数量'],
                        textGap: 10,
                        textStyle: {
                            color: 'black'
                        },
                        inRange: {
                            symbolSize: [10, 50]
                        },
                        outOfRange: {
                            symbolSize: [50, 55],
                            color: ['rgba(255,255,255,.2)']
                        },
                        controller: {
                            inRange: {
                                color: ['#c23531']
                            },
                            outOfRange: {
                                color: ['#444']
                            }
                        }
                    },
                    {
                        show: false,
                        left: 'right',
                        bottom: '2%',
                        dimension: 6,
                        min: 0,
                        max: Math.max.apply(null, t1_id),
                        itemHeight: 100,
                        calculable: true,
                        precision: 0,
                        text: ['颜色深浅：主题类别'],
                        textGap: 10,
                        textStyle: {
                            color: 'black'
                        },
                        inRange: {
                            colorLightness: [1, 0.5]
                        },
                        outOfRange: {
                            color: ['rgba(255,255,255,.2)']
                        },
                        controller: {
                            inRange: {
                                color: ['#c23531']
                            },
                            outOfRange: {
                                color: ['#444']
                            }
                        }
                    }
                ],
                dataZoom: [
                    {
                        type: 'slider',
                        show: true,
                        bottom: '1%',
                        height: 10,
                        start: 0,
                        end: 100,
                        handleSize: 5,
                        textStyle: {
                            color: 'black'
                        }
                    },
                    {
                        type: 'inside',
                        start: 94,
                        end: 100
                    },
                    {
                        type: 'slider',
                        show: true,
                        yAxisIndex: 0,
                        filterMode: 'empty',
                        width: 10,
                        height: '70%',
                        handleSize: 5,
                        showDataShadow: false,
                        left: '1%',
                        textStyle: {
                            color: 'black'
                        }
                    }
                ],
                series: [
                    {
                        name: '主题分布气泡图',
                        type: 'scatter',
                        itemStyle: itemStyle,
                        data: bubble_data
                    }
                ]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
            myChart.on('click', function (param) {
                var theme_id =  param.value[1];
                console.log(JSON.stringify(param.value));
                var params = {};
                params['theme_id'] = theme_id;
                window.location.href = '/DataMonitor/info?theme_3_id='+theme_id;
            });
        } else {
            popWarn(res.msg);
        }
    });
}

// 显示主题流向桑基图
function showThemeSankey(params) {
    params = params || [];
    var chartDom = document.getElementById('theme_company_sankey_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    $.get('/public/themeFlow.json', function (data) {
        data = JSON.parse(data);
        myChart.hideLoading();
        var option = {
            title: {
                text: '主题流向桑基图',
                x: 'center',
                align: 'right'
            },
            tooltip: {
                trigger: 'item',
                triggerOn: 'mousemove'

            },
            grid: {
                x: '10%',
                x2: 10,
                y: '18%',
                y2: '10%'
            },
            series: [
                {
                    type: 'sankey',
                    layout: 'none',
                    data: data.nodes,
                    links: data.links,
                    itemStyle: {
                        normal: {
                            borderWidth: 1,
                            borderColor: '#aaa'
                        }
                    },
                    lineStyle: {
                        normal: {
                            curveness: 0.5
                        }
                    }
                }
            ]
        };
        myChart.setOption(option);
        //用于使chart自适应高度和宽度
        window.onresize = myChart.resize();
    });
}

// 网站类型饼图
function showWebsitePie(params) {
    params = params || [];
    // alert(JSON.stringify(params));
    var theme = "";
    if(params.hasOwnProperty('theme')){
        theme = params['theme'];
    }
    console.log(JSON.stringify(params));
    var chartDom = document.getElementById('webiste_type_pie_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    $.get('/WebSite/typePie', params, function (res) {
        if (res.errorcode == 0) {
            myChart.hideLoading();
            var data = res.data;
            var legendData = [];
            for(var i=0;i<data.length;i++){
                legendData.push(data[i]['name']);
            }
            var option = {
                title: {
                    text: '网站类型',
                    subtext: theme,
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    type: 'scroll',
                    orient: 'horizontal',
                    right: 10,
                    bottom: 10,
                    data: legendData
                },
                series: [
                    {
                        name: '网站类型',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '50%'],
                        data: data,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
            myChart.on('click', function (param) {
                var name = param.name;
                showWebsiteThemeCount({'type': name});
            });
        } else {
            popWarn(res.msg);
        }
    });
}

// 网站类型抓取主题数量关系
function showWebsiteThemeCount(params) {
    params = params || [];
    var type = "";
    // alert(JSON.stringify(params));
    if(params.hasOwnProperty('type')){
        type = params['type'];
    }
    console.log(JSON.stringify(params));
    var chartDom = document.getElementById('webiste_type_theme_pie_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    $.get('/DataMonitor/websiteThemePie', params, function (res) {
        if (res.errorcode == 0) {
            myChart.hideLoading();
            var data = res.data;
            var legendData = [];
            for(var i=0;i<data.length;i++){
                legendData.push(data[i]['name']);
            }
            var option = {
                title: {
                    text: '相应主题分布',
                    subtext: type,
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    type: 'scroll',
                    orient: 'horizontal',
                    right: 10,
                    bottom: 10,
                    data: legendData
                },
                series: [
                    {
                        name: '相应主题分布',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '50%'],
                        data: data,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();
            myChart.on('click', function (param) {
                showWebsitePie({'theme': param.name});
            });
        } else {
            popWarn(res.msg);
        }
    });
}


// 显示媒体覆盖饼形图
function showMedia(params) {
    params = params || [];
    var type = "";
    // alert(JSON.stringify(params));
    if(params.hasOwnProperty('type')){
        type = params['type'];
    }
    console.log(JSON.stringify(params));
    var chartDom = document.getElementById('meida_distribution_pie_chart');
    var myChart = echarts.init(chartDom);
    myChart.showLoading();
    // $.get('/DataMonitor/websiteThemePie', params, function (res) {
    //     if (res.errorcode == 0) {
            myChart.hideLoading();
            // var data = res.data;
            // var legendData = [];
            // for(var i=0;i<data.length;i++){
            //     legendData.push(data[i]['name']);
            // }
            var option = {
                title : {
                    text: '今日媒体类型覆盖情况',
                    x:'center'
                },
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c}万+ ({d}%)"
                },
                legend: {
                    x : 'center',
                    y : 'bottom',
                    data:['rose1','rose2','rose3','rose4','rose5','rose6','rose7','rose8']
                },
                calculable : true,
                series : [
                    {
                        name:'媒体类型覆盖情况',
                        type:'pie',
                        radius : [20, 110],
                        center : ['50%', '50%'],
                        roseType : 'area',
                        data:[
                            {value:3.2, name:'微博'},
                            {value:2.5, name:'论坛'},
                            {value:1.5, name:'新闻'},
                            {value:2.5, name:'贴吧'},
                            {value:2.0, name:'政府'},
                            {value:3.5, name:'博客'},
                            {value:1.3, name:'其他'}
                        ]
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
            //用于使chart自适应高度和宽度
            window.onresize = myChart.resize();

        // } else {
        //     popWarn(res.msg);
        // }
    // });
// }
}