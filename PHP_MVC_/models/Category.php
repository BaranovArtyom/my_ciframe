<?php

class Category
{
/**
 * Return categories array
 */
    public static function getCategories()
    {
        $db = Db::getConnection();

        $categoryList = array();

        $result = $db->query('SELECT id, name FROM categories ORDER BY sort_order ASC');

        $i = 0;
        while($row = $result->fetch()){
            $categoryList[$i]['id'] = $row['id'];
            $categoryList[$i]['name'] = $row['name'];
            $i++;
        }

        return $categoryList;
    }

}