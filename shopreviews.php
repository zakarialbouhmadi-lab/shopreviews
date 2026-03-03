<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShopReviews extends Module
{
    public function __construct()
    {
        $this->name = 'shopreviews';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Zaki-LB';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.0',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Shop Reviews');
        $this->description = $this->l('Allow customers to leave reviews about your shop');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHome') &&
            $this->registerHook('header') &&
            $this->installSQL() &&
            $this->installTab() &&
            Configuration::updateValue('SHOPREVIEWS_DISPLAY_LIMIT', 3);
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallSQL() &&
            $this->uninstallTab() &&
            Configuration::deleteByName('SHOPREVIEWS_DISPLAY_LIMIT');
    }

    public function installSQL()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'shop_reviews` (
            `id_review` int(11) NOT NULL AUTO_INCREMENT,
            `id_customer` int(11) NOT NULL,
            `customer_name` varchar(255) NOT NULL,
            `review_text` text NOT NULL,
            `rating` int(1) NOT NULL DEFAULT 5,
            `status` enum("pending","approved","rejected") NOT NULL DEFAULT "pending",
            `date_add` datetime NOT NULL,
            PRIMARY KEY (`id_review`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    public function uninstallSQL()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'shop_reviews`';
        return Db::getInstance()->execute($sql);
    }

    public function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminShopReviews';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Shop Reviews';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('IMPROVE'); // Try IMPROVE instead
        $tab->module = $this->name;
        $result = $tab->add();
        
        // Debug: log the result
        PrestaShopLogger::addLog('Tab installation result: ' . ($result ? 'success' : 'failed'));
        
        return $result;
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminShopReviews');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function hookDisplayHome($params)
    {
        // Debug: Always show something to test if hook is working
        error_log('ShopReviews: hookDisplayHome called');
        
        if (!$this->context->customer->isLogged()) {
            // Show message for non-logged users too for testing
            $this->context->smarty->assign([
                'reviews' => $this->getApprovedReviews(),
                'customer_logged' => false,
                'form_action' => $this->context->link->getPageLink('index')
            ]);
            return $this->display(__FILE__, 'views/templates/hook/displayHome.tpl');
        }

        // Handle form submission
        if (Tools::isSubmit('submitShopReview')) {
            $this->processReviewSubmission();
        }

        // Get approved reviews (use configured limit)
        $limit = (int)Configuration::get('SHOPREVIEWS_DISPLAY_LIMIT', 3);
        $reviews = $this->getApprovedReviews($limit);
        
        $this->context->smarty->assign([
            'reviews' => $reviews,
            'customer_logged' => $this->context->customer->isLogged(),
            'form_action' => $this->context->link->getPageLink('index'),
            'see_all_link' => $this->context->link->getModuleLink('shopreviews', 'all'),
            'show_see_all' => count($reviews) >= $limit // Only show if we have the maximum number
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayHome.tpl');
    }

    public function hookHeader()
    {
        if ($this->context->controller->php_self == 'index') {
            $this->context->controller->addCSS($this->_path . 'views/css/shopreviews.css');
        }
    }

    private function processReviewSubmission()
    {
        $customer_id = (int)$this->context->customer->id;
        $customer_name = pSQL($this->context->customer->firstname . ' ' . $this->context->customer->lastname);
        $review_text = pSQL(Tools::getValue('review_text'));
        $rating = (int)Tools::getValue('rating');

        if (empty($review_text) || $rating < 1 || $rating > 5) {
            return false;
        }

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'shop_reviews` 
                (id_customer, customer_name, review_text, rating, date_add) 
                VALUES (' . $customer_id . ', "' . $customer_name . '", "' . $review_text . '", ' . $rating . ', NOW())';

        if (Db::getInstance()->execute($sql)) {
            $this->context->controller->success[] = $this->l('Thank you! Your review is pending approval.');
        }
    }

    private function getApprovedReviews($limit = 3)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'shop_reviews` 
                WHERE status = "approved" 
                ORDER BY date_add DESC 
                LIMIT ' . (int)$limit;

        return Db::getInstance()->executeS($sql);
    }

    public function getContent()
    {
        $output = '';
        
        if (Tools::isSubmit('submit' . $this->name)) {
            $display_limit = (int)Tools::getValue('SHOPREVIEWS_DISPLAY_LIMIT');
            
            if ($display_limit < 1 || $display_limit > 50) {
                $output .= $this->displayError($this->l('Number of reviews must be between 1 and 50.'));
            } else {
                Configuration::updateValue('SHOPREVIEWS_DISPLAY_LIMIT', $display_limit);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Get current configuration value
        $current_limit = (int)Configuration::get('SHOPREVIEWS_DISPLAY_LIMIT', 3);
        
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Number of reviews to display'),
                        'name' => 'SHOPREVIEWS_DISPLAY_LIMIT',
                        'size' => 4,
                        'required' => true,
                        'desc' => $this->l('How many reviews to show on the homepage (1-50)')
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => [
                'SHOPREVIEWS_DISPLAY_LIMIT' => $current_limit
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        $info_panel = '<div class="panel">
                        <h3><i class="icon-info"></i> ' . $this->l('Information') . '</h3>
                        <p>' . $this->l('Manage shop reviews in the "Shop Reviews" tab under Customers menu.') . '</p>
                        <p>' . $this->l('Only approved reviews will be displayed on the homepage.') . '</p>
                      </div>';

        return $info_panel . $helper->generateForm([$fields_form]);
    }
}
