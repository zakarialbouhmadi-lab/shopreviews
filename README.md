# Shop Reviews - PrestaShop Module

## Overview

**Shop Reviews** is a specialized module designed to collect and display authentic customer feedback directly on your store. Unlike standard product reviews, this module focuses on the **overall shopping experience**, helping to build brand trust and improve SEO through fresh, user-generated content.


## Technical Stack

* **PrestaShop Compatibility:** 1.7.x / 8.x
* **Language Support:** Multi-language ready (Translations included for English, German, Polish, Spanish and Czech).
* **Database:** Custom relational table for review management.

## Key Features

* **Dedicated Review Page:** A clean, standalone front-end page where customers can browse all store ratings.
* **Rating System:** 1 to 5-star visual rating system.
* **Moderation Queue:** Full Back Office integration to approve, edit, or delete reviews before they go live.
* **Automated Integration:** Hooks into the footer or sidebar to display the latest average rating.
* **SEO Optimized:** Uses clean URLs for the review list to help search engines index your customer testimonials.

---

## Installation

1. Upload the `shopreviews` folder to your `/modules/` directory.
2. Go to **Module Manager** in your PrestaShop Back Office.
3. Search for "Shop Reviews" and click **Install**.
4. The module will automatically initialize the SQL table `ps_shop_reviews`.

---

## Configuration

### Back Office Management

Manage your reputation directly from the **Shop Reviews** tab in the admin panel:

* **Status Toggle:** Quickly enable/disable specific reviews.
* **Edit Content:** Fix typos or moderate inappropriate language in customer comments.
* **Date Tracking:** View exactly when each review was submitted.

<img width="1710" height="858" alt="image" src="https://github.com/user-attachments/assets/9f440ae1-a3da-442c-9052-54f8c1d3be8d" />

<img width="1710" height="592" alt="image" src="https://github.com/user-attachments/assets/2717656a-6dca-446e-a36c-eed96427bd49" />


### Front Office Display

Customers can access the review submission form and the global review list. The module utilizes a custom Front Controller for high performance and compatibility with custom themes.

<img width="1310" height="477" alt="image" src="https://github.com/user-attachments/assets/de38475d-4713-4116-9277-6a8db70a6d25" />


<img width="1267" height="870" alt="image" src="https://github.com/user-attachments/assets/4c496e0d-48a5-41b4-a553-441468e64fef" />

---

## Technical Architecture

### Database Schema

The module manages its own data persistence. Upon installation, it executes the following schema logic:

* **Table:** `ps_shop_reviews`
* **Fields:** `id_shop_review` (PK), `customer_name`, `rating` (int), `comment` (text), `active` (tinyint), `date_add` (datetime).

### Hooks & Controllers

* **`displayFooter` / `displayLeftColumn**`: Used to display the "Average Rating" widget.
* **`ModuleFrontController`**: Handles the submission logic, including validation of the rating scale and sanitization of the comment text to prevent XSS.

### Security

* **Input Validation:** All ratings are validated as integers between 1-5.
* **SQL Safety:** All queries use PrestaShop’s `Db` class abstraction to prevent SQL injection.
* **Access Control:** The submission form includes basic bot protection logic.

---

## 📄 License

All Rights Reserved. This repository is available for portfolio and educational viewing purposes only. Commercial or non-commercial use, distribution, or modification is strictly prohibited without explicit written permission.
