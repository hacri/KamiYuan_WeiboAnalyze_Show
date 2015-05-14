angular.module('app', []).
    controller('choseUrl', function ($http) {

        var chose = this;
        $http.get('/api/weibo/list')
            .success(function (data, status, header, config) {
                chose.weibo_list = data;
            }).error(function (data, status, headers, config) {

            });
        chose.weibo_list = [];
        chose.origin_weibo = {};
        chose.origin_user = {};

        chose.forward_top_list = [];

        chose.chose_mid = function (mid) {
            chose.mid = mid;

            $http.get('/api/weibo/' + mid).success(function (data) {
                chose.origin_weibo = data;

                $http.get('/api/user/' + data.user_id).success(function (data) {
                    chose.origin_user = data;
                });
            });

            $http.get('/api/weibo/' + mid + '/top/10').success(function (data) {
                chose.forward_top_list = data;
            });

            var myChart = echarts.init($('.forward-chart')[0]);
            var option = {
                title: {
                    x: 'center',
                    text: '转发内容中关键词出现次数'
                },
                tooltip: {
                    trigger: 'item'
                },
                toolbox: {
                    show: false
                },
                calculable: true,
                grid: {
                    borderWidth: 0,
                    y: 80,
                    y2: 60
                },
                xAxis: [
                    {
                        type: 'category',
                        show: false,
                        data: ['贵', '傻', '浇灌', '标记']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        show: true
                    }
                ],
                series: [
                    {
                        name: '次数',
                        type: 'bar',
                        itemStyle: {
                            normal: {
                                label: {
                                    show: true,
                                    position: 'top',
                                    formatter: '{b}\n{c}'
                                }
                            }
                        },
                        data: [342, 127, 142, 49]
                    }
                ]
            };
            myChart.setOption(option);
        };

        chose.mid = 0;
    });
