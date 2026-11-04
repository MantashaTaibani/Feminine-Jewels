# Feminine Jewells — PHP + MySQL (XAMPP) Setup Guide

## A. Folder structure
```
FeminieJewels/
├── index.php, login.php, signup.php, logout.php
├── dashboard.php, products.php, product_details.php
├── cart.php, checkout.php, order_confirmation.php, orders.php
├── admin/
│   ├── dashboard.php, products.php, orders.php
│   ├── add_product.php, edit_product.php, delete_product.php
│   └── includes_header.php, includes_footer.php
├── includes/
│   ├── db.php      (DB connection settings)
│   ├── auth.php     (sessions, CSRF, helper functions)
│   ├── header.php, footer.php
├── assets/
│   ├── css/style.css, login.css
│   └── images/products/*.jpg  (17 generated jewellery photos)
└── database/
    └── feminine_jewels.sql
```

## B. Install into XAMPP
1. Copy the whole `FeminieJewels` folder into `C:\xampp\htdocs\`.
   Final path should be: `C:\xampp\htdocs\FeminieJewels\index.php`
2. Open **XAMPP Control Panel** → click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

## C. Import the database
1. Open `http://localhost/phpmyadmin` in your browser.
2. Click **Import** (top menu).
3. Click **Choose file**, select `FeminieJewels\database\feminine_jewels.sql`.
4. Click **Go**. This creates the `feminine_jewels` database with all
   tables and 16 sample jewellery products already loaded, plus one
   working admin account and one working customer account.

`includes/db.php` is already set for XAMPP defaults (host `localhost`,
user `root`, empty password, database `feminine_jewels`). If your MySQL
uses a different username/password, edit only that one file.

## D. Open the website
Go to:
```
http://localhost/FeminieJewels/
```

## E. Test accounts
**Admin login**
- URL: `http://localhost/FeminieJewels/login.php`
- Email: `admin@femininejewels.com`
- Password: `admin123`
- Logs you into `admin/dashboard.php` automatically.

**Customer login**
- Email: `customer@femininejewels.com`
- Password: `customer123`
- Logs you into `dashboard.php` automatically.

You can also create a brand-new customer account any time from
`signup.php`.

## F. How the site works

**Login → Dashboard flow**
`login.php` checks the email/password against the `users` table with
`password_verify()`. On success it stores `user_id`, `name`, `email`
and `role` in the PHP session and redirects: admins go to
`admin/dashboard.php`, everyone else goes to `dashboard.php`. Wrong
credentials show "Invalid email or password" — never a raw database
error or blank page.

**Signup → Login flow**
`signup.php` validates the name, email format and password length,
checks the `users` table for a duplicate email, hashes the password
with `password_hash()`, inserts the row, then redirects to `login.php`
with a "Account created successfully" message.

**Products from MySQL**
`products.php` reads categories and products live from the database on
every page load. The search box, category chips, price slider and sort
dropdown all resubmit as a GET request, which `products.php` turns
into a parameterised SQL query (PDO prepared statements — never string
concatenation) — nothing is hardcoded in the HTML.

**Cart & checkout**
Cart rows live in the `cart` table, one per logged-in user, and are
updated with prepared statements from `cart.php`. `checkout.php`
re-checks stock, then in a single database transaction inserts one row
into `orders`, one row per line item into `order_items`, decrements
`products.stock`, and empties the cart — so an order is never split
between old and new stock.

**Adding new products / images**
Log in as admin → **Products** → **+ Add product**. Fill in name,
category, description, price and stock, and optionally upload a JPG/
PNG/WEBP image. Uploaded images are saved into
`assets/images/products/` and the filename is stored in the `products`
table; if no image is uploaded, a neutral placeholder is shown instead.

## G. Before you go live
- Change the admin and customer passwords (or add your own admin user)
  once you're on your live database.
- The 17 product photos in `assets/images/products/` are original
  studio-style renders (bust/velvet-box/display-card compositions)
  created specifically for this project — not stock photography.
