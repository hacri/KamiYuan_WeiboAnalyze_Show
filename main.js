angular.module('app', []).
    controller('choseUrl', function ($http, $sce) {

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

        chose.weibo_news = [];
        chose.other_naws = [];

        chose.search = [];

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
            });

            $http.get('/api/weibo/' + mid + '/news/0').success(function (data) {
                chose.weibo_news = data;
            });
            $http.get('/api/weibo/' + mid + '/news/1').success(function (data) {
                chose.other_naws = data;
            });

            $http.get('/api/weibo/' + mid + '/search').success(function (data) {
                chose.search = data;
            });

        };

        chose.mid = 0;

        chose.theHtml = function (raw_html) {
            return $sce.trustAsHtml(raw_html);
        }
    });
