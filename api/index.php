<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/database.config.php';

$pdo = new PDO($dsn, $usr, $pwd);


$app = new \Slim\Slim();
$app->response->header('Content-type', 'application/json');

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$app->get('/weibo/list', function () {
    global $pdo;
    $sql = <<<SQL
    SELECT * FROM `weibo_info`
SQL;
    $result = $pdo->prepare($sql);
    $result->execute();

    echo json_encode($result->fetchAll(PDO::FETCH_ASSOC));
});

$app->get('/weibo/:mid', function ($mid) {
    global $pdo;
    $sql = <<<SQL
    SELECT * FROM `weibo_info`
    WHERE mid = :mid
SQL;
    $result = $pdo->prepare($sql);
    $result->bindValue(':mid', $mid);
    $result->execute();

    echo json_encode($result->fetch(PDO::FETCH_ASSOC));
});

$app->get('/weibo/:mid/count', function ($mid) {
    echo $mid;
    global $pdo;

    $q = $pdo->prepare("SELECT COUNT(*) FROM forward_info WHERE origin_mid = :mid");
    $q->bindParam(':mid', $mid);
    $q->execute();

    var_dump($q->fetch());
});

$app->get('/weibo/:mid/stat/:num', function ($mid, $num) {
    global $pdo;

    $q = $pdo->prepare('SELECT * FROM forward_stat WHERE origin_mid = :mid ORDER BY `count` DESC LIMIT :num');
    $q->bindValue(':mid', $mid);
    $q->bindValue(':num', (int)$num, PDO::PARAM_INT);
    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    $max = 0;
    $min = 0;
    foreach ($data as &$item) {
        ($item['count'] > $max) ? $max = $item['count'] : null;
        ($item['count'] < $min || $min == 0) ? $min = $item['count'] : null;
    }

    $range = ($max > $min) ? $max - $min : 1;

    $result = [];
    foreach ($data as &$item) {
        $result[] = [
            'text' => $item['word'],
            'weight' => sqrt(($item['count'] - $min) / $range * 100) * 10,
        ];
    }
    echo json_encode($result);
//    $result = [
//        'key' => [],
//        'value' => [],
//    ];
//
//    foreach ($data as &$item) {
//        $result['key'][] = $item['word'];
//        $result['value'][] = $item['count'];
//    }
//    echo json_encode($result);
});

$app->get('/weibo/:mid/top/:num', function ($mid, $num) {
    global $pdo;
    $sql = <<<SQL
SELECT forward_info.*, user_info.*, user_tag.tag FROM forward_info
JOIN `user_info` ON `user_info`.`user_id` = `forward_info`.`user_id`
LEFT JOIN `user_tag` ON `user_tag`.`user_id` = `forward_info`.`user_id`
WHERE origin_mid = :mid
AND (`user_info`.`is_blue_v` = 1 OR `user_info`.`is_yellow_v` = 1)
ORDER BY forward_count DESC
LIMIT :num
SQL;
    $q = $pdo->prepare($sql);
    $q->bindParam(':mid', $mid);
    $q->bindValue(':num', (int)$num, PDO::PARAM_INT);
    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    usort($data, function (&$a, &$b) {
        return $a['date'] < $b['date'];
    });

    $max = 0;
    $min = 0;
    foreach ($data as &$item) {
        ($item['forward_count'] > $max) ? $max = $item['forward_count'] : null;
        ($item['forward_count'] < $min || $min == 0) ? $min = $item['forward_count'] : null;
    }
    $range = ($max > $min) ? $max - $min : 1;

    foreach ($data as &$item) {
        $item['date_str'] = date('Y-m-d H:i:s', $item['date']);
        $item['weight'] = sqrt(($item['forward_count'] - $min) / $range);
    }

    echo json_encode($data);
});

$app->get('/weibo/:mid/news/:type', function ($mid, $type) {
    global $pdo;

    if ($type == 0) {
        $db = 'weibo_news';
    } else {
        $db = 'other_news';
    }

    $sql = <<<SQL
SELECT * FROM `{$db}`
WHERE `origin_mid` = :mid
SQL;

    $q = $pdo->prepare($sql);
    $q->bindValue(':mid', $mid);

    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    if ($type != 0) {
        foreach ($data as &$item) {
            $item['body'] = nl2br(htmlspecialchars($item['body']));
        }
    }

    echo json_encode($data);
});

$app->get('/weibo/:mid/search', function ($mid) {
    global $pdo;

    $sql = <<<SQL
SELECT * FROM `search`
WHERE `origin_mid` = :mid
SQL;

    $q = $pdo->prepare($sql);
    $q->bindValue(':mid', $mid);

    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
});

$app->get('/weibo/:mid/word/:word/:num', function ($mid, $word, $num) {
    global $pdo;

    $sql = <<<SQL
SELECT `forward_info`.*, `user_info`.*, `user_tag`.tag, (forward_count + like_count  * 0.5 + LENGTH(wb_content_main) / 100) as forward_mark FROM `forward_info`
JOIN `user_info` ON `user_info`.`user_id` = `forward_info`.`user_id`
LEFT JOIN `user_tag` ON `user_tag`.`user_id` = `forward_info`.`user_id`
WHERE origin_mid = :mid
AND `forward_info`.`wb_content_main` LIKE :word
ORDER BY forward_mark DESC
LIMIT :num
SQL;

    $q = $pdo->prepare($sql);
    $q->bindValue(':mid', $mid);
    $q->bindValue(':word', "%$word%");
    $q->bindValue(':num', (int)$num, PDO::PARAM_INT);
    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

//    usort($data, function (&$a, &$b) {
//        return $a['date'] < $b['date'];
//    });

    foreach ($data as &$item) {
        $item['date_str'] = date('Y-m-d H:i:s', $item['date']);

        $item['wb_content'] = str_replace($word, "<span class='text-red'>{$word}</span>", $item['wb_content']);
    }

    echo json_encode($data);
});

$app->get('/weibo/:mid/keyword', function ($mid) {
    global $pdo;

    $sql = <<<SQL
SELECT * FROM `keyword`
WHERE `mid` = :mid
SQL;

    $q = $pdo->prepare($sql);
    $q->bindValue(':mid', $mid);
    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    $keywords = [];
    foreach ($data as &$row) {
        $keywords[] = $row['keyword'];
    }

    echo json_encode($keywords);
});

$app->get('/weibo/:mid/more_news', function ($mid) {
    global $pdo;

    $sql = <<<SQL
SELECT * FROM `more_news`
WHERE `mid` = :mid
SQL;

    $q = $pdo->prepare($sql);
    $q->bindValue(':mid', $mid);
    $q->execute();

    $data = $q->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($data as &$row) {
        $result[$row['weibo_or_baidu_news']] = $row['URL'];
    }
    echo json_encode($result);
});

$app->get('/user/:uid', function ($uid) {
    global $pdo;
    $sql = <<<SQL
SELECT `user_info`.*, `user_tag`.tag FROM `user_info`
LEFT JOIN `user_tag` ON `user_tag`.`user_id` = `user_info`.`user_id`
WHERE `user_info`.user_id = :uid
SQL;
    $q = $pdo->prepare($sql);
    $q->bindValue(':uid', $uid);
    $q->execute();

    echo json_encode($q->fetch(PDO::FETCH_ASSOC));
});

$app->run();
