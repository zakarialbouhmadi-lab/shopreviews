<?php

class AdminShopReviewsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'shop_reviews';
        $this->identifier = 'id_review';
        $this->bootstrap = true;
        $this->lang = false;
        $this->allow_export = true;
        
        // Remove className to avoid ObjectModel requirements
        $this->explicitSelect = false;
        
        parent::__construct();
        
        $this->fields_list = [
            'id_review' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'customer_name' => [
                'title' => $this->l('Customer'),
                'width' => 'auto'
            ],
            'review_text' => [
                'title' => $this->l('Review'),
                'width' => 'auto',
                'maxlength' => 100,
                'callback' => 'truncateReview'
            ],
            'rating' => [
                'title' => $this->l('Rating'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'callback' => 'displayStars'
            ],
            'status' => [
                'title' => $this->l('Status'),
                'align' => 'center',
                'type' => 'select',
                'list' => [
                    'pending' => $this->l('Pending'),
                    'approved' => $this->l('Approved'),
                    'rejected' => $this->l('Rejected')
                ],
                'filter_key' => 'status',
                'class' => 'fixed-width-sm',
                'callback' => 'displayStatus'
            ],
            'date_add' => [
                'title' => $this->l('Date'),
                'align' => 'right',
                'type' => 'datetime',
                'class' => 'fixed-width-lg'
            ]
        ];
        
        $this->bulk_actions = [
            'approve' => [
                'text' => $this->l('Approve'),
                'icon' => 'icon-check',
                'confirm' => $this->l('Approve selected items?')
            ],
            'reject' => [
                'text' => $this->l('Reject'),
                'icon' => 'icon-remove',
                'confirm' => $this->l('Reject selected items?')
            ],
            'delete' => [
                'text' => $this->l('Delete'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            ]
        ];
        
        $this->actions = ['edit', 'delete'];
    }
    
    public function truncateReview($value, $row)
    {
        return Tools::strlen($value) > 100 ? Tools::substr($value, 0, 100) . '...' : $value;
    }
    
    public function displayStars($value, $row)
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= ($i <= $value) ? '★' : '☆';
        }
        return $stars;
    }
    
    public function displayStatus($value, $row)
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success', 
            'rejected' => 'danger'
        ];
        $color = isset($colors[$value]) ? $colors[$value] : 'default';
        return '<span class="label label-' . $color . '">' . ucfirst($value) . '</span>';
    }
    
    public function renderForm()
    {
        if (Tools::getValue('id_review')) {
            // Edit mode
            $id_review = (int)Tools::getValue('id_review');
            $review = Db::getInstance()->getRow('
                SELECT * FROM `' . _DB_PREFIX_ . 'shop_reviews` 
                WHERE id_review = ' . $id_review
            );
            
            if (!$review) {
                $this->errors[] = $this->l('Review not found.');
                return parent::renderList();
            }
        } else {
            // Add mode - initialize empty review
            $review = [
                'id_review' => 0,
                'customer_name' => '',
                'review_text' => '',
                'rating' => 5,
                'status' => 'pending'
            ];
        }
        
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Review Details'),
            ],
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'id_review'
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Customer Name'),
                    'name' => 'customer_name',
                    'required' => true,
                    'col' => 6
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Review Text'),
                    'name' => 'review_text',
                    'required' => true,
                    'rows' => 5,
                    'col' => 9
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Rating'),
                    'name' => 'rating',
                    'options' => [
                        'query' => [
                            ['id' => 5, 'name' => '5 ★★★★★'],
                            ['id' => 4, 'name' => '4 ★★★★☆'],
                            ['id' => 3, 'name' => '3 ★★★☆☆'],
                            ['id' => 2, 'name' => '2 ★★☆☆☆'],
                            ['id' => 1, 'name' => '1 ★☆☆☆☆']
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ],
                    'col' => 3
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Status'),
                    'name' => 'status',
                    'options' => [
                        'query' => [
                            ['id' => 'pending', 'name' => $this->l('Pending')],
                            ['id' => 'approved', 'name' => $this->l('Approved')],
                            ['id' => 'rejected', 'name' => $this->l('Rejected')]
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ],
                    'col' => 3
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ]
        ];
        
        $this->fields_value = $review;
        
        return parent::renderForm();
    }
    
    public function processBulkApprove()
    {
        return $this->processBulkStatus('approved');
    }
    
    public function processBulkReject()
    {
        return $this->processBulkStatus('rejected');
    }
    
    protected function processBulkStatus($status)
    {
        $reviews = Tools::getValue($this->table . 'Box');
        if (is_array($reviews) && !empty($reviews)) {
            $ids = implode(',', array_map('intval', $reviews));
            $sql = 'UPDATE `' . _DB_PREFIX_ . $this->table . '` 
                    SET status = "' . pSQL($status) . '" 
                    WHERE id_review IN (' . $ids . ')';
            
            if (Db::getInstance()->execute($sql)) {
                $this->confirmations[] = sprintf(
                    $this->l('%d review(s) have been %s.'),
                    count($reviews),
                    ($status == 'approved') ? $this->l('approved') : $this->l('rejected')
                );
            } else {
                $this->errors[] = $this->l('An error occurred while updating reviews.');
            }
        }
        
        return true;
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $this->processForm();
        } else {
            return parent::postProcess();
        }
    }
    
    protected function processForm()
    {
        $id_review = (int)Tools::getValue('id_review');
        $customer_name = pSQL(Tools::getValue('customer_name'));
        $review_text = pSQL(Tools::getValue('review_text'));
        $rating = (int)Tools::getValue('rating');
        $status = pSQL(Tools::getValue('status'));
        
        // Validation
        if (empty($customer_name) || empty($review_text)) {
            $this->errors[] = $this->l('Customer name and review text are required.');
            return false;
        }
        
        if ($rating < 1 || $rating > 5) {
            $this->errors[] = $this->l('Rating must be between 1 and 5.');
            return false;
        }
        
        if ($id_review > 0) {
            // Update existing review
            $sql = 'UPDATE `' . _DB_PREFIX_ . $this->table . '` 
                    SET customer_name = "' . $customer_name . '",
                        review_text = "' . $review_text . '", 
                        rating = ' . $rating . ',
                        status = "' . $status . '"
                    WHERE id_review = ' . $id_review;
        } else {
            // Insert new review
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . $this->table . '` 
                    (customer_name, review_text, rating, status, date_add, id_customer) 
                    VALUES ("' . $customer_name . '", "' . $review_text . '", ' . $rating . ', "' . $status . '", NOW(), 0)';
        }
        
        if (Db::getInstance()->execute($sql)) {
            $this->confirmations[] = $this->l('Review saved successfully.');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminShopReviews'));
        } else {
            $this->errors[] = $this->l('An error occurred while saving the review.');
        }
        
        return true;
    }
    
    public function processDelete()
    {
        $id_review = (int)Tools::getValue('id_review');
        
        if ($id_review) {
            $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->table . '` WHERE id_review = ' . $id_review;
            
            if (Db::getInstance()->execute($sql)) {
                $this->confirmations[] = $this->l('Review deleted successfully.');
            } else {
                $this->errors[] = $this->l('An error occurred while deleting the review.');
            }
        }
        
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminShopReviews'));
    }
}
