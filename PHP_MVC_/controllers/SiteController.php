<?php 

// include_once ROOT.'/models/Category.php';
// include_once ROOT.'/models/Product.php';
// include_once ROOT.'/components/Pagination.php';

class SiteController
{

    public function actionIndex()
    {
        $categories = array();
        $categories = Category::getCategories();

        $latestProducts = array();
        $latestProducts = Product::getProducts(10);

        require_once(ROOT.'/views/site/index.php');

        return true;
    }
}