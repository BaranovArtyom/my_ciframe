<?php
 

class Hi
{
    public static function hello(){
        return 'hello anybody';
    }
    public function by(){
        return 'by';

    }
}

// $obj = new Hi;
// echo $obj->hello();
// echo $obj->by();

// echo Hi::hello();
// echo Hi::by();

//ключевое слово self

class Page {
    static $main = 'content<br>';
    
    public static function header(){
        return 'header<br>';
    }
    public static function footer(){
        return 'footer<br>';
    }
    public static function getPage(){
        echo self::header().
             self::$main.
             self::footer();
    }
}

Page::getPage();

