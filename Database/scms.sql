-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2024 at 10:50 AM
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
-- Database: `scms`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CATEGORY_ID` int(11) NOT NULL,
  `CNAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CATEGORY_ID`, `CNAME`) VALUES
(0, 'coffee'),
(1, 'non-coffee'),
(9, 'Others'),
(10, 'Liquids'),
(11, 'DAIRY'),
(12, 'BEANS'),
(13, 'SYRUP');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CUST_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `PHONE_NUMBER` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CUST_ID`, `FIRST_NAME`, `LAST_NAME`, `PHONE_NUMBER`) VALUES
(9, 'Num', '5', '09394566543'),
(11, 'Number', '1', '0000000000'),
(14, 'Num', '4', '09781633451'),
(15, 'Number', '3', '09956288467'),
(16, 'Num', '2', '09891344576');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EMPLOYEE_ID` int(11) NOT NULL,
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `GENDER` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(100) DEFAULT NULL,
  `PHONE_NUMBER` varchar(11) DEFAULT NULL,
  `JOB_ID` int(11) DEFAULT NULL,
  `HIRED_DATE` varchar(50) NOT NULL,
  `LOCATION_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EMPLOYEE_ID`, `FIRST_NAME`, `LAST_NAME`, `GENDER`, `EMAIL`, `PHONE_NUMBER`, `JOB_ID`, `HIRED_DATE`, `LOCATION_ID`) VALUES
(1, 'Lance', 'Vidallon', 'Male', 'admin@gmail.com', '01004321347', 1, '0000-00-00', 113),
(2, 'Lance', 'Vidallon', 'Male', 'lanceg@gmail.com', '09094341516', 2, '2024-06-30', 156),
(4, 'Lance', 'Vidallon', 'Male', 'endcruz@gmail.com', '08736621516', 1, '2024-07-21', 158);

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `icode` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `expenses` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`icode`, `name`, `quantity`, `unit`, `category`, `expenses`) VALUES
(1, 'Milk Tea Powder', 50.00, 'kg', 'Ingredient', 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `JOB_ID` int(11) NOT NULL,
  `JOB_TITLE` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `job`
--

INSERT INTO `job` (`JOB_ID`, `JOB_TITLE`) VALUES
(1, 'Manager'),
(2, 'Cashier');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `LOCATION_ID` int(11) NOT NULL,
  `PROVINCE` varchar(100) DEFAULT NULL,
  `CITY` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`LOCATION_ID`, `PROVINCE`, `CITY`) VALUES
(111, 'Metro Manila', 'Valenzuela '),
(113, 'Metro Manila', 'Caloocan'),
(114, 'Metro Manila', 'Caloocan'),
(115, 'Metro Manila', 'Caloocan'),
(116, 'Metro Manila', 'Quezon City'),
(155, 'Metro Manila', 'Quezon City'),
(156, 'Metro Manila', 'Caloocan'),
(158, 'Metro Manila', 'Quezon City'),
(159, 'Metro Manila', 'Caloocan');

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

CREATE TABLE `manager` (
  `FIRST_NAME` varchar(50) DEFAULT NULL,
  `LAST_NAME` varchar(50) DEFAULT NULL,
  `LOCATION_ID` int(11) NOT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `PHONE_NUMBER` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `manager`
--

INSERT INTO `manager` (`FIRST_NAME`, `LAST_NAME`, `LOCATION_ID`, `EMAIL`, `PHONE_NUMBER`) VALUES
('Lance', 'Vidallon', 113, 'admin@gmail.com', '0123456789');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `PRODUCT_ID` int(11) NOT NULL,
  `PRODUCT_CODE` varchar(20) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `ing_name` varchar(255) DEFAULT NULL,
  `recipe_name` varchar(255) DEFAULT NULL,
  `DESCRIPTION` varchar(250) NOT NULL,
  `QTY_STOCK` int(50) DEFAULT NULL,
  `ON_HAND` int(250) NOT NULL,
  `PRICE` int(50) DEFAULT NULL,
  `expenses` decimal(10,2) DEFAULT NULL,
  `CATEGORY_ID` int(11) DEFAULT NULL,
  `SUPPLIER_ID` int(11) DEFAULT NULL,
  `DATE_STOCK_IN` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`PRODUCT_ID`, `PRODUCT_CODE`, `NAME`, `quantity`, `unit`, `ing_name`, `recipe_name`, `DESCRIPTION`, `QTY_STOCK`, `ON_HAND`, `PRICE`, `expenses`, `CATEGORY_ID`, `SUPPLIER_ID`, `DATE_STOCK_IN`) VALUES
(34, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(35, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(36, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(37, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(38, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(39, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(40, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(41, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(42, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(43, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(44, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(45, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(46, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(47, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(48, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(49, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(50, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(51, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(52, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(53, '3940', 'mocha', NULL, NULL, NULL, 'watersss', 'ingredients and units: ', 1, 1, 51, NULL, 0, 12, '2024-11-15'),
(54, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(55, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(56, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(57, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(58, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(59, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(60, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(61, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(62, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(63, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(64, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 23, 23, 200, NULL, 1, 16, '2024-11-18'),
(65, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(66, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(67, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(68, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(69, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(70, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(71, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(72, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(73, '01', 'Americano', NULL, NULL, NULL, 'waters', 'j', 1, 1, 200, NULL, 1, 16, '2024-11-18'),
(76, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(77, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(78, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(79, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(80, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(81, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(82, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(83, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(84, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(85, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(86, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(87, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(88, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(89, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(90, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(91, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(92, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(93, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(94, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(95, '05', 'Americano', NULL, NULL, NULL, 'Americano Recipe', 'test 1', 1, 1, 200, NULL, 0, 16, '2024-11-20'),
(96, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(97, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(98, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(99, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(100, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(101, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(102, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(103, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(104, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(105, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(106, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(107, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(108, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(109, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(110, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(111, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(112, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(113, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(114, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(115, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(116, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(117, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(118, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(119, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 16, '2024-11-22'),
(120, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(121, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(122, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(123, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(124, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(125, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(126, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(127, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(128, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(129, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(130, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(131, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(132, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(133, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(134, '02', 'Espresso Ingredients', NULL, NULL, NULL, 'Americano Recipe', 'test', 1, 1, 200, NULL, 0, 12, '2024-11-22'),
(135, '07', 'Chocolate Milk Drink', NULL, NULL, NULL, 'Milk', 'test', 1, 1, 95, NULL, 11, 16, '2024-11-22'),
(136, '07', 'Chocolate Milk Drink', NULL, NULL, NULL, 'Milk', 'test', 1, 1, 95, NULL, 11, 16, '2024-11-22'),
(137, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(138, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(139, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(140, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(141, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(142, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(143, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(144, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(145, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20'),
(146, '08', 'Latte', NULL, NULL, NULL, 'Latte Recipe', 'Date: test', 1, 1, 180, NULL, 0, 16, '2024-11-20');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `SUPPLIER_ID` int(11) NOT NULL,
  `COMPANY_NAME` varchar(50) DEFAULT NULL,
  `LOCATION_ID` int(11) NOT NULL,
  `PHONE_NUMBER` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`SUPPLIER_ID`, `COMPANY_NAME`, `LOCATION_ID`, `PHONE_NUMBER`) VALUES
(11, 'CoffeeShop3', 114, '09167821234'),
(12, 'CoffeeShop2', 115, '09871234567'),
(13, 'CoffeeShop4', 111, '09221008912'),
(15, 'CoffeeShop5', 116, '09118923451'),
(16, 'CoffeeShop1', 155, '09122334621'),
(17, 'CS10', 159, '09236617234');

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `TRANS_ID` int(50) NOT NULL,
  `CUST_ID` int(11) DEFAULT NULL,
  `NUMOFITEMS` varchar(250) NOT NULL,
  `SUBTOTAL` varchar(50) NOT NULL,
  `LESSVAT` varchar(50) NOT NULL,
  `NETVAT` varchar(50) NOT NULL,
  `ADDVAT` varchar(50) NOT NULL,
  `GRANDTOTAL` varchar(250) NOT NULL,
  `CASH` varchar(250) NOT NULL,
  `DATE` varchar(50) NOT NULL,
  `TRANS_D_ID` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaction`
--

INSERT INTO `transaction` (`TRANS_ID`, `CUST_ID`, `NUMOFITEMS`, `SUBTOTAL`, `LESSVAT`, `NETVAT`, `ADDVAT`, `GRANDTOTAL`, `CASH`, `DATE`, `TRANS_D_ID`) VALUES
(18, 9, '1', '225.00', '24.11', '200.89', '24.11', '225.00', '225.00', '2024-11-03 20:48 pm', '1103135008'),
(19, 9, '1', '4,500.00', '482.14', '4,017.86', '482.14', '4,500.00', '4500.00', '2024-11-03 21:12 pm', '1103141719'),
(20, 9, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '225.00', '2024-11-04 10:46 am', '110434719'),
(21, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-17 13:37 pm', '111763810'),
(22, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-17 14:38 pm', '111773954'),
(23, 11, '1', '560.00', '60.00', '500.00', '60.00', '560.00', '560.00', '2024-11-18 08:38 am', '111814634'),
(24, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-18 11:21 am', '111842147'),
(25, 11, '3', '12,320.00', '1,320.00', '11,000.00', '1,320.00', '12,320.00', ' 12,320.00', '2024-11-18 13:15 pm', '111861527'),
(26, 11, '2', '450.00', '48.21', '401.79', '48.21', '450.00', '450.00', '2024-11-18 13:19 pm', '111861935'),
(27, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-18 23:41 pm', '1118164115'),
(28, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-19 13:01 pm', '111960457'),
(29, 11, '1', '2,000.00', '214.29', '1,785.71', '214.29', '2,000.00', '2000.00', '2024-11-19 13:06 pm', '111960713'),
(30, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-19 15:46 pm', '111984641'),
(31, 11, '1', '2,800.00', '300.00', '2,500.00', '300.00', '2,800.00', '2800.00', '2024-11-19 15:50 pm', '111985056'),
(32, 14, '1', '2,800.00', '300.00', '2,500.00', '300.00', '2,800.00', '2800', '2024-11-19 15:53 pm', '111985349'),
(33, 16, '1', '285.00', '30.54', '254.46', '30.54', '285.00', '2000', '2024-11-19 16:23 pm', '111992358'),
(34, 11, '1', '5,000.00', '535.71', '4,464.29', '535.71', '5,000.00', '5000', '2024-11-19 16:25 pm', '111992518'),
(35, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-19 22:06 pm', '1119150635'),
(36, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-19 22:19 pm', '1119152011'),
(37, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-19 23:09 pm', '1119160935'),
(38, 11, '1', '280.00', '30.00', '250.00', '30.00', '280.00', '280.00', '2024-11-19 23:09 pm', '1119161004'),
(39, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-20 12:34 pm', '112053447'),
(40, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-20 12:35 pm', '112053525'),
(41, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-20 12:35 pm', '112053540'),
(42, 11, '1', '200.00', '21.43', '178.57', '21.43', '200.00', '200.00', '2024-11-20 12:35 pm', '112053554'),
(43, 11, '2', '400.00', '42.86', '357.14', '42.86', '400.00', '400', '2024-11-20 14:45 pm', '112074512'),
(44, 11, '1', '51.00', '5.46', '45.54', '5.46', '51.00', '51', '2024-11-20 14:45 pm', '112074543'),
(45, 14, '1', '204.00', '21.86', '182.14', '21.86', '204.00', '204', '2024-11-20 14:46 pm', '112074622'),
(46, 11, '1', '204.00', '21.86', '182.14', '21.86', '204.00', '204', '2024-11-20 14:46 pm', '112074641');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_details`
--

CREATE TABLE `transaction_details` (
  `ID` int(11) NOT NULL,
  `TRANS_D_ID` varchar(250) NOT NULL,
  `PRODUCTS` varchar(250) NOT NULL,
  `QTY` varchar(250) NOT NULL,
  `PRICE` varchar(250) NOT NULL,
  `EMPLOYEE` varchar(250) NOT NULL,
  `ROLE` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaction_details`
--

INSERT INTO `transaction_details` (`ID`, `TRANS_D_ID`, `PRODUCTS`, `QTY`, `PRICE`, `EMPLOYEE`, `ROLE`) VALUES
(27, '1103135008', 'Americano', '1', '225', 'En', 'Manager'),
(28, '1103141719', 'Americano', '20', '225', 'Lance', 'Manager'),
(29, '110434719', 'chocolate', '1', '280', 'Lance', 'Manager'),
(30, '111763810', 'Espresso', '1', '280', 'Lance', 'Manager'),
(31, '111773954', 'Espresso', '1', '280', 'Lance', 'Manager'),
(32, '111814634', 'Espresso', '2', '280', 'Lance', 'Manager'),
(33, '111842147', 'Americano', '1', '280', 'Lance', 'Manager'),
(34, '111861527', 'Mocha', '1', '200', 'Lance', 'Manager'),
(35, '111861527', 'Americano', '24', '280', 'Lance', 'Manager'),
(36, '111861527', 'ameriCaNo', '24', '225', 'Lance', 'Manager'),
(37, '111861935', 'Mocha', '1', '250', 'Lance', 'Manager'),
(38, '111861935', 'Mocha', '1', '200', 'Lance', 'Manager'),
(39, '1118164115', 'Americano', '1', '200', 'Lance', 'Manager'),
(40, '111960457', 'Americano', '1', '200', 'Lance', 'Manager'),
(41, '111960713', 'Americano', '10', '200', 'Lance', 'Manager'),
(42, '111984641', 'Americano', '1', '200', 'Lance', 'Manager'),
(43, '111985056', 'Americano', '10', '280', 'Lance', 'Manager'),
(44, '111985349', 'Americano', '10', '280', 'Lance', 'Manager'),
(45, '111992358', 'chocolate', '1', '285', 'Lance', 'Manager'),
(46, '111992518', 'Espresso Ingredients', '25', '200', 'Lance', 'Manager'),
(47, '1119150635', 'Americano', '1', '280', 'Lance', 'Manager'),
(48, '1119152011', 'Americano', '1', '280', 'Lance', 'Manager'),
(49, '1119160935', 'Americano', '1', '280', 'Lance', 'Manager'),
(50, '1119161004', 'Americano', '1', '280', 'Lance', 'Manager'),
(51, '112053447', 'Americano', '1', '200', 'Lance', 'Manager'),
(52, '112053525', 'Espresso Ingredients', '1', '200', 'Lance', 'Manager'),
(53, '112053540', 'Espresso Ingredients', '1', '200', 'Lance', 'Manager'),
(54, '112053554', 'Espresso Ingredients', '1', '200', 'Lance', 'Manager'),
(55, '112074512', 'Espresso Ingredients', '1', '200', 'Lance', 'Manager'),
(56, '112074512', 'Americano', '1', '200', 'Lance', 'Manager'),
(57, '112074543', 'mocha', '1', '51', 'Lance', 'Manager'),
(58, '112074622', 'mocha', '4', '51', 'Lance', 'Manager'),
(59, '112074641', 'mocha', '4', '51', 'Lance', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

CREATE TABLE `type` (
  `TYPE_ID` int(11) NOT NULL,
  `TYPE` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `type`
--

INSERT INTO `type` (`TYPE_ID`, `TYPE`) VALUES
(1, 'Admin'),
(2, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `EMPLOYEE_ID` int(11) DEFAULT NULL,
  `USERNAME` varchar(50) DEFAULT NULL,
  `PASSWORD` varchar(50) DEFAULT NULL,
  `TYPE_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `EMPLOYEE_ID`, `USERNAME`, `PASSWORD`, `TYPE_ID`) VALUES
(1, 1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 1),
(7, 2, 'user', '12dea96fec20593566ab75692c9949596833adc9', 2),
(9, 4, 'user1', 'b3daa77b4c04a9551b8781d03191fe098f325e67', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CATEGORY_ID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CUST_ID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EMPLOYEE_ID`),
  ADD UNIQUE KEY `EMPLOYEE_ID` (`EMPLOYEE_ID`),
  ADD UNIQUE KEY `PHONE_NUMBER` (`PHONE_NUMBER`),
  ADD KEY `LOCATION_ID` (`LOCATION_ID`),
  ADD KEY `JOB_ID` (`JOB_ID`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`icode`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`JOB_ID`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`LOCATION_ID`);

--
-- Indexes for table `manager`
--
ALTER TABLE `manager`
  ADD UNIQUE KEY `PHONE_NUMBER` (`PHONE_NUMBER`),
  ADD KEY `LOCATION_ID` (`LOCATION_ID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`PRODUCT_ID`),
  ADD KEY `CATEGORY_ID` (`CATEGORY_ID`),
  ADD KEY `SUPPLIER_ID` (`SUPPLIER_ID`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`SUPPLIER_ID`),
  ADD KEY `LOCATION_ID` (`LOCATION_ID`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`TRANS_ID`),
  ADD KEY `TRANS_DETAIL_ID` (`TRANS_D_ID`),
  ADD KEY `CUST_ID` (`CUST_ID`);

--
-- Indexes for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TRANS_D_ID` (`TRANS_D_ID`) USING BTREE;

--
-- Indexes for table `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`TYPE_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TYPE_ID` (`TYPE_ID`),
  ADD KEY `EMPLOYEE_ID` (`EMPLOYEE_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CUST_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `EMPLOYEE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `icode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `LOCATION_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `PRODUCT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `SUPPLIER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `TRANS_ID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`LOCATION_ID`) REFERENCES `location` (`LOCATION_ID`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`JOB_ID`) REFERENCES `job` (`JOB_ID`);

--
-- Constraints for table `manager`
--
ALTER TABLE `manager`
  ADD CONSTRAINT `manager_ibfk_1` FOREIGN KEY (`LOCATION_ID`) REFERENCES `location` (`LOCATION_ID`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`CATEGORY_ID`) REFERENCES `category` (`CATEGORY_ID`),
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`SUPPLIER_ID`) REFERENCES `supplier` (`SUPPLIER_ID`);

--
-- Constraints for table `supplier`
--
ALTER TABLE `supplier`
  ADD CONSTRAINT `supplier_ibfk_1` FOREIGN KEY (`LOCATION_ID`) REFERENCES `location` (`LOCATION_ID`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_3` FOREIGN KEY (`CUST_ID`) REFERENCES `customer` (`CUST_ID`),
  ADD CONSTRAINT `transaction_ibfk_4` FOREIGN KEY (`TRANS_D_ID`) REFERENCES `transaction_details` (`TRANS_D_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`TYPE_ID`) REFERENCES `type` (`TYPE_ID`),
  ADD CONSTRAINT `users_ibfk_4` FOREIGN KEY (`EMPLOYEE_ID`) REFERENCES `employee` (`EMPLOYEE_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
