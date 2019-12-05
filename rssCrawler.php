<?php
    /**
     * 定義
     */
    define('MAGPIE_OUTPUT_ENCODING','UTF-8'); //encode
    define('MAGPIE_CACHE_AGE','30'); //cache

    /**
     * ロード
     */
    require_once 'mysql.php';
    require_once 'config.php';
    require_once '../magpierss/rss_fetch.inc';
    require_once '../magpierss/rss_utils.inc';

    // setting
    date_default_timezone_set('Asia/Tokyo');

    // connect db
    $dbc = null;
    try {
        $dbc = connect($dbc);
    } catch(PDOException $e) {
        var_dump($e->getMessage());
        exit;
    }

    // get rss
    echo $no;

    foreach ($rssUrl as $no => $rss_url) {
        if ($rss_url != '') {
            // URLからRSSを取得
            $rss   = fetch_rss($rss_url);
            if ($rss != NULL) {
                for ($i = 0; $i < count($rss -> items); $i++) {
                    $rss -> items[$i]["site_title"] = $rss -> channel["title"];
                    $rss -> items[$i]["site_link"] = $rss -> channel["link"];
                }
                // itemsを格納
                $rssItemsArray[] = $rss->items;
            }
        }
    }

    // push rss
    $concatArray = array();
    if (is_array($rssItemsArray)) {
        for($i = 0; $i < count($rssItemsArray); $i++){
            $concatArray = array_merge($concatArray, $rssItemsArray[$i]); // 配列を統合する
        }

        foreach ($concatArray as $no => $values) {
            // RSSの種類によって日付を取得
            if($values['published']){
                $date = $values['published'];
            }elseif($values['created']){
                $date = $values['created'];
            }elseif($values['pubdate']){
                $date = $values['pubdate'];
            }elseif($values['dc']['date']){
                $date = $values['dc']['date'];
            }
            $date = date(
                "Y-m-d H:i:s", strtotime($date)
            );

            // Filter
            // 現在時刻の取得
            $nowtime = date(
                "Y-m-d H:i:s",
                strtotime( "now" )
            );
            if($date > $nowtime){ // 未来記事の排除
            }elseif(preg_match("/AD/", $values["title"])){ // 広告記事の排除
            }elseif(preg_match("/PR/", $values["title"])){ // 広告記事の排除
            }else{

                // 値の定義
                $title = $values["title"];
                $link = $values["link"];
                $site_title = $values["site_title"];
                $site_link = $values["site_link"];
                $description = $values["description"];

                // 記事ごとに必要な項目を抽出
                $rssArray[] = array(
                    $date,
                    $title,
                    $link,
                    $site_title,
                    $site_link,
                    $description
                );
            }
        }

        // ソート
        function cmp($a, $b) {
            if ($a[0] == $b[0]) return 0;
            return ($a[0] > $b[0]) ? -1 : 1;
        }
        if($rssArray) {
            usort($rssArray, 'cmp');
        }
        if(count($rssArray) > $num){
            $count = $num;
        }else{
            $count = count($rssArray);
        }

        // 必要な件数分だけHTML整形
        for ($i = 0; $i < $count; $i++) {
            $date = date("Y-m-d H:i:s",strtotime($rssArray[$i][0]));
            $title = $rssArray[$i][1];
            $link = $rssArray[$i][2];
            $site_title = $rssArray[$i][3];
            $site_link = $rssArray[$i][4];
            $datelink = "<div>$date";
            $titlelink = "<a href='$link'>$title</a>";
            $description = $rssArray[$i][5];
            $site_titlelink = "<a href='$site_link'>[$site_title]</a></div>";
            //echo "$datelink$titlelink$site_titlelink</div>"; // (確認用)

            // 格納
            $stmt = $dbc -> prepare("insert into rss (title,link,site_title,site_link,date,description) values (?,?,?,?,?,?)");
            $stmt -> execute(
                array(
                    $title,
                    $link,
                    $site_title,
                    $site_link,
                    $date,
                    $description
                )
            );
        }
    }

    // disconnect db
    disconnect($dbc);
?>