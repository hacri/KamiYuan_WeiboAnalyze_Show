angular.module('app', []).
    controller('choseUrl', function ($http) {

        var chose = this;
        $http.get('/api/weibo/list')
            .success(function (data, status, header, config) {
                chose.weibo_list = data;
            }).error(function (data, status, headers, config) {

            });

        chose.mid = null;
        chose.weibo_list = [];
        chose.origin_weibo = {};
        chose.origin_user = {};

        chose.forward_top_list = [];
        chose.word_like_list = [];

        var get_word_weibo = function () {
            var word = $(this).text();
            var url = '/api/weibo/' + chose.mid + '/word/' + word + '/10';
            $http.get(url).success(function (data) {
                chose.word_like_list = data;
            })
        };

        chose.chose_mid = function (mid) {
            chose.mid = mid;

            $http.get('/api/weibo/' + mid).success(function (data) {
                chose.origin_weibo = data;

                $http.get('/api/user/' + data.user_id).success(function (data) {
                    chose.origin_user = data;
                });
            });

            $http.get('/api/weibo/' + mid + '/top/15').success(function (data) {
                chose.forward_top_list = data;
            });

            $http.get('/api/weibo/' + mid + '/stat/100').success(function (data) {

                for (var i in data) {
                    data[i].handlers = {click: get_word_weibo};
                }

                $('.forward-chart').jQCloud(data, {
                    width: 340,
                    height: 400
                });
                //var myChart = echarts.init($('.forward-chart')[0]);
                //var option = {
                //    title: {
                //        x: 'center',
                //        text: '转发内容中关键词出现次数'
                //    },
                //    tooltip: {
                //        trigger: 'item'
                //    },
                //    toolbox: {
                //        show: false
                //    },
                //    calculable: true,
                //    grid: {
                //        borderWidth: 0,
                //        y: 80,
                //        y2: 60
                //    },
                //    xAxis: [
                //        {
                //            type: 'category',
                //            show: false,
                //            data: data.key
                //        }
                //    ],
                //    yAxis: [
                //        {
                //            type: 'value',
                //            show: true
                //        }
                //    ],
                //    series: [
                //        {
                //            name: '次数',
                //            type: 'bar',
                //            itemStyle: {
                //                normal: {
                //                    label: {
                //                        show: true,
                //                        position: 'top',
                //                        formatter: '{b}\n{c}'
                //                    }
                //                }
                //            },
                //            data: data.value
                //        }
                //    ]
                //};
                //myChart.setOption(option);


            });


        };

        chose.mid = 0;
    });
