# GiftPixie E-Commerce Website

GiftPixie is a PHP and MySQL e-commerce website developed as a university assignment. Customers can browse gift products, create an account, manage a shopping cart, place orders, and track purchases. The project also includes an administrator dashboard for managing products, customers, orders, and FAQs.

## Features

### Customer

- Account registration and secure password login
- Product catalogue with category filtering
- Individual product details
- Shopping cart with quantity and stock validation
- Checkout with transactional order processing
- Order history and tracking
- Profile management
- Contact, About, Privacy, and Terms pages

### Administrator

- Separate administrator login
- Dashboard statistics
- Product management
- Customer management
- Order status and tracking-number management
- FAQ management

## Technologies

- PHP 8
- MySQL / MariaDB
- MySQLi prepared statements
- HTML5
- CSS3
- JavaScript
- XAMPP for local development

## Project Structure

```text
admin/                  Administrator pages
database/               SQL database setup
images/                 Website and product images
add_to_cart.php         Adds products to the cart
cart.php                Displays and updates the cart
checkout.php            Creates orders and updates stock
db.php                  Local database connection
index.php               Home page
login.php               Customer login
orders.php              Customer order history
order_tracking.php      Order tracking
product.php             Product details
products.php            Product catalogue
profile.php             Customer profile
register.php            Customer registration
```

## Local Setup with XAMPP

1. Install XAMPP.
2. Copy the folder to:

```text
C:\xampp\htdocs\giftpixie-php-ecommerce
```

3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin`.
5. Create a database named `giftpixie_portfolio`.
6. Import `database/giftpixie_portfolio.sql`.
7. Open the website:

```text
http://localhost/giftpixie-php-ecommerce/
```

Administrator login:

```text
URL: http://localhost/giftpixie-php-ecommerce/admin/
Username: admin
Password: admin123
```

The administrator password is stored as a secure hash in the sample database. Change the demo password before using the project beyond local testing.

## Database Configuration

The default XAMPP configuration in `db.php` is:

```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'giftpixie_portfolio';
```

Do not commit production credentials, private customer information, or real database backups.

## Notes

- This is an educational project intended for local demonstration.
- Checkout records an order but does not connect to a real payment gateway.
- The contact form is a front-end demonstration and does not send email.
- For production use, add CSRF protection, environment-based configuration, stricter validation, rate limiting, and security testing.

## Author

GitHub: [@AdxMind](https://github.com/AdxMind)
