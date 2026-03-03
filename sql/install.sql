CREATE TABLE IF NOT EXISTS `PREFIX_shop_reviews` (
    `id_review` int(11) NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) NOT NULL,
    `customer_name` varchar(255) NOT NULL,
    `review_text` text NOT NULL,
    `rating` int(1) NOT NULL DEFAULT 5,
    `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `date_add` datetime NOT NULL,
    PRIMARY KEY (`id_review`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;
