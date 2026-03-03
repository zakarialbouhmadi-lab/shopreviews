<?php

class ShopReviewsAllModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        $page = (int)Tools::getValue('page', 1);
        $limit = 10; // Reviews per page
        $offset = ($page - 1) * $limit;
        
        // Get all approved reviews with pagination
        $reviews = $this->getAllApprovedReviews($limit, $offset);
        $totalReviews = $this->getTotalApprovedReviews();
        $totalPages = ceil($totalReviews / $limit);
        
        $this->context->smarty->assign([
            'reviews' => $reviews,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_reviews' => $totalReviews,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'previous_page' => $page - 1,
            'next_page' => $page + 1,
            'all_reviews_url' => $this->context->link->getModuleLink('shopreviews', 'all')
        ]);
        
        $this->setTemplate('module:shopreviews/views/templates/front/all.tpl');
    }
    
    private function getAllApprovedReviews($limit = 10, $offset = 0)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'shop_reviews` 
                WHERE status = "approved" 
                ORDER BY date_add DESC 
                LIMIT ' . (int)$offset . ', ' . (int)$limit;
        
        return Db::getInstance()->executeS($sql);
    }
    
    private function getTotalApprovedReviews()
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'shop_reviews` 
                WHERE status = "approved"';
        
        return (int)Db::getInstance()->getValue($sql);
    }
    
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        
        $breadcrumb['links'][] = [
            'title' => $this->module->l('Shop Reviews', 'all'),
            'url' => $this->context->link->getModuleLink('shopreviews', 'all')
        ];
        
        return $breadcrumb;
    }
    
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        $page['meta']['title'] = $this->module->l('All Shop Reviews', 'all');
        $page['meta']['description'] = $this->module->l('Read all customer reviews about our shop', 'all');
        
        return $page;
    }
}
