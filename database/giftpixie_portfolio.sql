-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2026 at 05:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `giftpixie_portfolio`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Birthday', 'Perfect gifts for birthday celebrations'),
(2, 'Anniversary', 'Romantic gifts for anniversaries'),
(3, 'Other', 'Gifts for other special occasions');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `display_order`, `created_at`) VALUES
(1, 'How long does shipping take?', 'Standard shipping takes 3-5 business days. Express shipping (2-3 business days) is available at checkout for an additional fee.', 1, '2025-12-18 18:44:19'),
(2, 'Can I cancel or modify my order?', 'You can cancel or modify your order within 2 hours of placing it. Contact customer support immediately for assistance.', 2, '2025-12-18 18:44:19'),
(3, 'Do you offer gift wrapping?', 'Yes! We offer complimentary gift wrapping for all orders. You can add a gift message during checkout.', 3, '2025-12-18 18:44:19'),
(4, 'What is your return policy?', 'We accept returns within 30 days of delivery for unused items in original packaging. Refunds are issued to the original payment method.', 4, '2025-12-18 18:44:19'),
(5, 'Do you ship internationally?', 'Currently, we only ship within the Sri Lanka. We plan to expand internationally soon!', 5, '2025-12-18 18:44:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'placeholder.jpg',
  `stock_quantity` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `image`, `stock_quantity`, `created_at`) VALUES
(1, 'Balloon Bouquet', 'A beautiful bouquet of colorful balloons perfect for birthday celebrations. Each balloon is filled with helium for maximum float time.', 2500.00, 1, 'balloon_bouquet.jpg', 15, '2025-12-18 18:44:19'),
(2, 'Chocolate Hamper', 'Delicious chocolate collection with assorted flavors and types. Perfect for chocolate lovers.', 3200.00, 1, 'chocolate_hamper.jpg', 12, '2025-12-18 18:44:19'),
(3, 'Specially For Him Gift Set', 'Thoughtful collection of gifts for men including grooming products and accessories.', 4500.00, 1, 'for_him_giftset.jpg', 10, '2025-12-18 18:44:19'),
(4, 'Specially For Her Gift Set', 'Beautiful collection of gifts for women including beauty products and accessories.', 4800.00, 1, 'for_her_giftset.jpg', 10, '2025-12-18 18:44:19'),
(5, 'Happy Anniversary Wine Gift', 'Celebrate love with a fine wine selection paired with gourmet chocolates.', 5800.00, 2, 'anniversary_wine.jpg', 8, '2025-12-18 18:44:19'),
(6, 'Classic Love Rose Bouquet', 'Timeless romantic bouquet of red roses, perfect for expressing love.', 3500.00, 2, 'rose_bouquet.jpg', 19, '2025-12-18 18:44:19'),
(7, 'Gentleman\'s Signature Style Giftset', 'Premium grooming and style products for the modern gentleman.', 6500.00, 2, 'gentleman_giftset.jpg', 6, '2025-12-18 18:44:19'),
(8, 'Forever My Girl Gift Set', 'Elegant collection of jewelry and pampering products for her.', 5200.00, 2, 'forever_girl_giftset.jpg', 6, '2025-12-18 18:44:19'),
(9, 'Graduation Gift Box', 'Celebrate academic achievements with this thoughtful graduation gift collection.', 4800.00, 3, 'graduation_giftbox.jpg', 10, '2025-12-18 18:44:19'),
(10, 'Self Care Gifts', 'Pampering and relaxation products for self-care and wellness.', 4200.00, 3, 'self_care_gifts.jpg', 15, '2025-12-18 18:44:19'),
(11, 'Christmas Special Gift Basket', 'Festive collection of holiday treats and decor for Christmas celebrations.', 6200.00, 3, 'christmas_basket.jpg', 8, '2025-12-18 18:44:19'),
(12, 'Thank You Gift Box', 'Express gratitude with this beautifully curated thank you gift collection.', 3200.00, 3, 'thankyou_giftbox.jpg', 12, '2025-12-18 18:44:19'),
(14, 'Classic Teddy Bear', 'A soft teddy bear gift suitable for birthdays, anniversaries, and other special occasions.', 2000.00, 3, 'teddy.jpg', 15, '2025-12-19 06:34:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Demo administrator account for local development
-- Username: admin
-- Password: admin123
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `address`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@giftpixie.test', '$2y$12$ZwFkSs3eOUdmw72NX6jFa.st/RQ9zlzz7uFf4ExVjbMWFVgT.Eshy', 'GiftPixie', 'Admin', NULL, NULL, 'admin', CURRENT_TIMESTAMP);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_cart_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
