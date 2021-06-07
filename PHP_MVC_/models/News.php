<?php




class News
{
    public static function getNewsList()
    {
        //Запрос к БД
        // $host = 'localhost';
        // $dbname = 'mvc_site';
        // $user = 'admin';
        // $password = 'пароль';
        // $charset = 'utf8';
        
        // $db = new PDO("mysql:host=$host;dbname=$dbname",$user,$password);
       
        // $opt = [
        //     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //     PDO::ATTR_EMULATE_PREPARES   => false,
        // ];
        // $db = new PDO("mysql:host=$host;dbname=$dbname;charset=UTF8", $user, $password, $opt);
        
        $db = Db::getConnection();
        
        $newsList = array();

        $result = $db->query('SELECT id, title, date, content FROM каталог ORDER BY id ASC LIMIT 10');

        $i = 0;
        while ($row = $result->fetch()){
            $newsList[$i]['id'] = $row['id'];
            $newsList[$i]['title'] = $row['title'];
            $newsList[$i]['date'] = $row['date'];
            $newsList[$i]['content'] = $row['content'];
            $i++;
        }
        return  $newsList;

    }

    public static function getNewsItemById($id)
    {
        //Запрос к БД
        $id = intval($id);

        if ($id){
            // $host = 'localhost';
            // $dbname = 'mvc_site';
            // $user = 'admin';
            // $password = 'пароль';
            // $opt = [
            //     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            //     PDO::ATTR_EMULATE_PREPARES   => false,
            // ];

            // $db = new PDO ("mysql:host=$host;dbname=$dbname;charset=UTF8", $user, $password, $opt);
            $db = Db::getConnection();
            $result = $db->query('SELECT * FROM каталог WHERE id = '.$id);
           

            $result->setFetchMode(PDO::FETCH_NUM);
            $result->setFetchMode(PDO::FETCH_ASSOC);

            
            $newItem = $result->fetch();

            return $newItem;

        }
        
    }
}