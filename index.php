<?php
    require_once 'mysql.php';
    require_once 'config.php';
    global  $siteUrl, $siteTitle;

    // connect db
    $dbc = null;
    try {
        $dbc = connect($dbc);
    } catch(PDOException $e) {
        var_dump($e->getMessage());
        exit;
    }

    $rss_data = getData($dbc);
    // 以下、ページ出力部分
    echo "<div class=antenna>";
    for ($i1 = 0; $i1 < count($siteUrl); $i1++) {

        // サイトタイトル
        echo "<div id=content>";
        echo "<h3>".$siteTitle[$i1]."</h3>\n";
        // 記事
        echo "<ul>\n";
        for ($i2 = 0; $i2 < count($rss_data); $i2++) {
            if($siteUrl[$i1] === $rss_data[$i2][3]){ // サイトURLが同一ならecho

                    // 各種代入
                    $title = $rss_data[$i2][0];
                    $link = $rss_data[$i2][1];
                    $site_title = $rss_data[$i2][2];
                    $site_link = $rss_data[$i2][3];
                    $date = $rss_data[$i2][4];
                    $description = $rss_data[$i2][5];

                    // 記事出力
                    echo "<div class=post><li>".$date.'<br /><h5><a href="'
                        .$link.'" target="_blank">'.$title.'</a></h5><br /><div class=entry>'.mb_strimwidth($description, 0, 600, "...")."</div></li></div>\n";
            }
        }
        echo "</ul>\n";
        echo "</div>";
    }
    echo "</div>";
    // disconnect db
    disconnect($dbc);
?>