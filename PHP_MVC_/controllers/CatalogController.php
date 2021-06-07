<?php 

// include_once ROOT.'/models/Category.php';
// include_once ROOT.'/models/Product.php';
// include_once ROOT.'/components/Pagination.php';

class CatalogController
{

    public function actionIndex()
    {
        $categories = array();
        $categories = Category::getCategories();

        $latestProducts = array();
        $latestProducts = Product::getProducts(2);

        require_once(ROOT.'/views/catalog/index.php');

        return true;
    }

    public function actionCategory($categoryId, $page = 1)
    {
        // echo 'category: '.$categoryId;
        // echo '<br>page: '.$page;

        $categories = array();
        $categories = Category::getCategories();
       
        $latestProducts = array();
        $latestProducts = Product::getProducts(2);

        $categoryProducts = array();
        $categoryProducts = Product::getProductsByCategory($categoryId, $page);
        
        $total = Product::getTotalProductsInCategory($categoryId);
        
        $pagination = new Pagination($total, $page, Product::SHOW_BY_DEFAULT, 'page-' );

        require_once(ROOT.'/views/catalog/category.php');

        return true;
    }

}