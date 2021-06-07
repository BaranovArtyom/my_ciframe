<?php

include_once ROOT. '/models/News.php';

Class NewController {

    public function actionIndex()
    {
        $newsList = array();
        $newsList = News::getNewsList();

        // echo '<pre>';
        // print_r($newsList);
        // echo '</pre>';
        require_once(ROOT.'/views/news/index.php');

        return true;
    }

    public function actionView($id)
    {
        if($id) {
            $newsItem = News::getNewsItemById($id);

            echo '<pre>';
            print_r($newsItem);
            echo '</pre>';
            echo 'actionview';
        }
        return true;
    }

}
