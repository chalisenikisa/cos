-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2026 at 03:02 AM
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
-- Database: `database.sql`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$YourHashedPasswordHereUsePHPpassword_hash', '2026-04-10 00:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Rice Meals', 'bowl-rice'),
(2, 'Noodles', 'soup'),
(3, 'Snacks', 'cookie-bite'),
(4, 'Beverages', 'mug-hot'),
(5, 'Desserts', 'ice-cream'),
(6, 'Sandwiches', 'bread-slice');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT '',
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT 'default-food.jpg',
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `created_at`) VALUES
(1, 1, 'Chicken Adobo Rice', 'Classic Filipino adobo served with steamed rice', 75.00, 'chicken-adobo.jpg', 1, '2026-04-10 00:59:54'),
(2, 1, 'Pork Sinigang Rice', 'Sour and savory pork soup with rice', 85.00, 'sinigang.jpg', 1, '2026-04-10 00:59:54'),
(3, 1, 'Bistek Rice', 'Beef steak with onion rings and rice', 90.00, 'bistek.jpg', 1, '2026-04-10 00:59:54'),
(4, 1, 'Tapsilog', 'Marinated beef, fried egg, and garlic rice', 70.00, 'tapsilog.jpg', 1, '2026-04-10 00:59:54'),
(5, 1, 'Longsilog', 'Longanisa, fried egg, and garlic rice', 65.00, 'longsilog.jpg', 1, '2026-04-10 00:59:54'),
(6, 2, 'Pancit Canton', 'Stir-fried egg noodles with vegetables and shrimp', 60.00, 'pancit.jpg', 1, '2026-04-10 00:59:54'),
(7, 2, 'Lomi', 'Thick egg noodle soup with meat and vegetables', 55.00, 'lomi.jpg', 1, '2026-04-10 00:59:54'),
(8, 2, 'Batchoy', 'Ilonggo noodle soup with pork organs and crushed crackers', 60.00, 'batchoy.jpg', 1, '2026-04-10 00:59:54'),
(9, 3, 'Lumpia Shanghai', 'Crispy fried spring rolls filled with pork', 50.00, 'lumpia.jpg', 1, '2026-04-10 00:59:54'),
(10, 3, 'French Fries', 'Crispy golden fries with ketchup', 45.00, 'fries.jpg', 1, '2026-04-10 00:59:54'),
(11, 3, 'Chicken Wings', '6 pieces of seasoned fried chicken wings', 80.00, 'wings.jpg', 1, '2026-04-10 00:59:54'),
(12, 3, 'Fishballs', 'Street-style fishballs with spicy sauce', 30.00, 'fishballs.jpg', 1, '2026-04-10 00:59:54'),
(13, 4, 'Iced Tea', 'Refreshing cold tea with lemon', 25.00, 'iced-tea.jpg', 1, '2026-04-10 00:59:54'),
(14, 4, 'Buko Juice', 'Fresh coconut juice served cold', 30.00, 'buko.jpg', 1, '2026-04-10 00:59:54'),
(15, 4, 'Coffee', 'Hot brewed coffee', 20.00, 'coffee.jpg', 1, '2026-04-10 00:59:54'),
(16, 4, 'Milk Tea', 'Creamy milk tea with tapioca pearls', 40.00, 'milktea.jpg', 1, '2026-04-10 00:59:54'),
(17, 5, 'Leche Flan', 'Creamy caramel custard', 40.00, 'leche-flan.jpg', 1, '2026-04-10 00:59:54'),
(18, 5, 'Halo-Halo', 'Mixed Filipino dessert with shaved ice and milk', 55.00, 'halo-halo.jpg', 1, '2026-04-10 00:59:54'),
(19, 6, 'Ham & Cheese Sandwich', 'Toasted sandwich with ham and melted cheese', 50.00, 'ham-cheese.jpg', 1, '2026-04-10 00:59:54'),
(20, 6, 'Egg Sandwich', 'Fluffy egg salad sandwich', 40.00, 'egg-sandwich.jpg', 1, '2026-04-10 00:59:54'),
(21, 6, 'Tuna Sandwich', 'Tuna mayo sandwich with lettuce', 45.00, 'tuna-sandwich.jpg', 1, '2026-04-10 00:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `payment_method` varchar(30) DEFAULT 'cash',
  `notes` text DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
