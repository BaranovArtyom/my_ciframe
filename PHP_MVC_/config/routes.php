<?php

return array (
    'products/([0-9]+)'=>'products/view/$1',
    'catalog'=>'catalog/index',
    'category/([0-9]+)/page-([0-9]+)' => 'catalog/category/$1/$2',//actionCategory в CatalogController
    'category/([0-9]+)'=>'catalog/category/$1',
    
    'user/register' => 'user/register',
    'user/login' => 'user/login',
    'user/logout' => 'user/logout',

    'cabinet/edit' => 'cabinet/edit',
    'cabinet' => 'cabinet/index',

    ''=>'site/index',

    
    //routes for example
    // 'news/([0-9]+)'=>'new/view/$1',
    // 'news'=>'new/index', //actionIndex в NewController
    // 'products'=>'product/list'//actionList в ProductController
);

?>