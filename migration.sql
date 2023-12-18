-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Dic 08, 2023 alle 19:26
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kreas`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `products`
--

CREATE TABLE `products` (
  `product_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `saved_kg_co2` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `products`
--

INSERT INTO `products` (`product_code`, `name`, `saved_kg_co2`) VALUES
('0100', 'pork meat', 3.2),
('0234', 'chicken breast', 4),
('1023', 'boar meat', 7),
('1345', 'calf meat', 10),
('4141', 'hamburger chicken', 2.3),
('5520', 'rabbit meat', 2),
('6476', 'hamburger', 11);

-- --------------------------------------------------------

--
-- Struttura della tabella `sales`
--

CREATE TABLE `sales` (
  `sales_code` varchar(50) NOT NULL,
  `sales_date` datetime NOT NULL,
  `destination` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `sales`
--

INSERT INTO `sales` (`sales_code`, `sales_date`, `destination`) VALUES
('AA1015', '2023-10-20 15:20:00', 'China'),
('AA4141', '2023-12-07 15:40:00', 'Italy'),
('ABC001', '2023-12-01 13:20:00', 'Greece'),
('AF2310', '2023-11-01 08:15:20', 'Ireland'),
('BF0071', '2023-11-05 16:54:28', 'Italy'),
('CB1156', '2023-11-06 21:36:00', 'Romania'),
('ZZ0001', '2023-11-25 16:00:00', 'Greece');

-- --------------------------------------------------------

--
-- Struttura della tabella `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` int(11) NOT NULL,
  `product_id` varchar(50) NOT NULL,
  `n_products` int(11) NOT NULL,
  `sales_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `sales_orders`
--

INSERT INTO `sales_orders` (`id`, `product_id`, `n_products`, `sales_id`) VALUES
(2, '0100', 2, 'AA1015'),
(3, '0234', 4, 'AA1015'),
(4, '1345', 4, 'BF0071'),
(5, '6476', 2, 'AF2310'),
(6, '5520', 1, 'CB1156'),
(7, '1023', 5, 'CB1156'),
(8, '0100', 4, 'AF2310'),
(9, '0100', 2, 'AA4141'),
(10, '6476', 5, 'ZZ0001'),
(11, '0234', 11, 'ZZ0001'),
(12, '6476', 2, 'ABC001');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_code`);

--
-- Indici per le tabelle `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sales_code`);

--
-- Indici per le tabelle `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_ind` (`product_id`),
  ADD KEY `sales_ind` (`sales_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_code`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`sales_code`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
