<?php
    // mysqlDBに接続
    function connect($dbc){
        global $db;

        $param = 'mysql:host='.$db['host'].';dbname='.$db['dbname'];
        $dbc = new PDO(
            $param,
            $db['user'],
            $db['pass']
        );
        return $dbc;
    }

    function disconnect($dbc){
        $dbc = null;
    }

    function getData($dbc){
        global $db;
        $sql = "select * from ".$db['table']." order by date desc limit 10"; // 10件表示
        $stmt = $dbc -> query($sql);
        $rss_data;
        $count = 0;
        foreach ($stmt -> fetchAll(PDO::FETCH_ASSOC) as $data) {
            $date = date('Y年m月d日 G:i',strtotime($data['date']));
            $title = ($data['title']);
            $link = ($data['link']);
            $site_title = ($data['site_title']);
            $site_link = ($data['site_link']);
            $description = ($data['description']);
 
            $rss_data[$count] = array(
                $title,
                $link,
                $site_title,
                $site_link,
                $date,
                $description
            );

            $count++;
        }
        return $rss_data;
    }
?>