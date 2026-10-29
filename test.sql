-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2026 at 04:51 AM
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
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `description`, `price`, `stock`, `image`, `created_at`) VALUES
(1, 'Wireless Headphones', 'Electronics', 'Comfortable wireless audio for everyday use.', 1299.00, 15, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600', '2026-10-29 02:34:03'),
(2, 'Classic Sneakers', 'Fashion', 'Everyday sneakers with a clean, modern look.', 1899.00, 12, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600', '2026-10-29 02:34:03'),
(3, 'Smart Watch', 'Electronics', 'A stylish watch for your daily routine.', 2499.00, 8, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600', '2026-10-29 02:34:03'),
(4, 'Minimalist Backpack', 'Accessories', 'A practical bag for school, work, and travel.', 999.00, 20, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600', '2026-10-29 02:34:03'),
(5, 'Classic Sunglasses', 'Accessories', 'Versatile sunglasses with a timeless design.', 499.00, 25, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600', '2026-10-29 02:34:03'),
(6, 'Ceramic Table Lamp', 'Home', 'A warm accent lamp for your room.', 899.00, 10, 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=600', '2026-10-29 02:34:03'),
(7, 'Mechanical Keyboard', 'Electronics', 'A keyboard for study and everyday computing.', 1599.00, 9, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600', '2026-10-29 02:34:03'),
(8, 'Everyday Wristwatch', 'Fashion', 'A simple timepiece that pairs with any outfit.', 1199.00, 14, 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=600', '2026-10-29 02:34:03'),
(9, 'Wireless Earbuds', 'Electronics', NULL, 599.00, 25, 'https://placehold.co/400x300/fce4ef/773354?text=Wireless+Earbuds', '2026-10-29 02:53:47'),
(10, 'Portable Bluetooth Speaker', 'Electronics', NULL, 899.00, 18, 'https://placehold.co/400x300/fce4ef/773354?text=Bluetooth+Speaker', '2026-10-29 02:53:47'),
(11, 'Phone Case', 'Accessories', NULL, 149.00, 50, 'https://placehold.co/400x300/fce4ef/773354?text=Phone+Case', '2026-10-29 02:53:47'),
(12, 'Fast Charging Cable', 'Electronics', NULL, 199.00, 35, 'https://placehold.co/400x300/fce4ef/773354?text=Charging+Cable', '2026-10-29 02:53:47'),
(13, 'Cute Mini Backpack', 'Bags', NULL, 499.00, 20, 'https://placehold.co/400x300/fce4ef/773354?text=Mini+Backpack', '2026-10-29 02:53:47'),
(14, 'Canvas Tote Bag', 'Bags', NULL, 249.00, 30, 'https://placehold.co/400x300/fce4ef/773354?text=Tote+Bag', '2026-10-29 02:53:47'),
(15, 'Pastel Hair Clips', 'Accessories', NULL, 99.00, 60, 'https://placehold.co/400x300/fce4ef/773354?text=Hair+Clips', '2026-10-29 02:53:47'),
(16, 'Stainless Water Bottle', 'Lifestyle', NULL, 299.00, 25, 'https://placehold.co/400x300/fce4ef/773354?text=Water+Bottle', '2026-10-29 02:53:47'),
(17, 'Desk Organizer', 'Home', NULL, 199.00, 15, 'https://placehold.co/400x300/fce4ef/773354?text=Desk+Organizer', '2026-10-29 02:53:47'),
(18, 'LED Desk Lamp', 'Home', NULL, 450.00, 12, 'https://placehold.co/400x300/fce4ef/773354?text=Desk+Lamp', '2026-10-29 02:53:47'),
(19, 'Mechanical Mouse', 'Electronics', NULL, 349.00, 20, 'https://placehold.co/400x300/fce4ef/773354?text=Computer+Mouse', '2026-10-29 02:53:47'),
(20, 'Scented Candle', 'Lifestyle', NULL, 179.00, 28, 'https://placehold.co/400x300/fce4ef/773354?text=Scented+Candle', '2026-10-29 02:53:47'),
(21, 'Cute Notebook Set', 'Stationery', NULL, 129.00, 40, 'https://placehold.co/400x300/fce4ef/773354?text=Notebook+Set', '2026-10-29 02:53:47'),
(22, 'Premium Ballpen Set', 'Stationery', NULL, 89.00, 45, 'https://placehold.co/400x300/fce4ef/773354?text=Ballpen+Set', '2026-10-29 02:53:47'),
(23, 'Foldable Phone Stand', 'Accessories', NULL, 159.00, 32, 'https://placehold.co/400x300/fce4ef/773354?text=Phone+Stand', '2026-10-29 02:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

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
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
