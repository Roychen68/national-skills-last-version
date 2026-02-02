-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2026-02-02 07:41:15
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `web01`
--

-- --------------------------------------------------------

--
-- 資料表結構 `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '1234');

-- --------------------------------------------------------

--
-- 資料表結構 `basic`
--

CREATE TABLE `basic` (
  `id` int(11) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `basic`
--

INSERT INTO `basic` (`id`, `start`, `end`) VALUES
(1, '2025-11-13 11:40:00', '2026-02-19 16:40:00');

-- --------------------------------------------------------

--
-- 資料表結構 `bus`
--

CREATE TABLE `bus` (
  `id` int(11) NOT NULL,
  `plate` text NOT NULL,
  `time` int(11) NOT NULL,
  `route` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `bus`
--

INSERT INTO `bus` (`id`, `plate`, `time`, `route`) VALUES
(12, 'test', 5, '43'),
(13, 'A1234', 1, '49');

-- --------------------------------------------------------

--
-- 資料表結構 `form`
--

CREATE TABLE `form` (
  `form` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `form`
--

INSERT INTO `form` (`form`) VALUES
(1);

-- --------------------------------------------------------

--
-- 資料表結構 `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `mail` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `participants`
--

INSERT INTO `participants` (`id`, `mail`) VALUES
(2, 'user01@test.come'),
(3, 'user02@test.come'),
(4, 'roychen68@test.com');

-- --------------------------------------------------------

--
-- 資料表結構 `response`
--

CREATE TABLE `response` (
  `id` int(11) NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `mail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `rate` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `response`
--

INSERT INTO `response` (`id`, `name`, `mail`, `route`, `note`, `rate`) VALUES
(21, 'adf', 'test@test.com', 'ddd', 'test', 'Very Dissatisfied'),
(22, 'test', 'idk@idk.com', 'ddd', '55', 'Very Dissatisfied'),
(23, 'blabkasdfadf', 'asdfasdfsadfk@idk.com', 'ddd', 'idk', 'Very Dissatisfied'),
(24, 'blabkasdfadf', 'asdfasdfsadfk@idk.com', 'ddd', 'asdfasdf', 'Very Dissatisfied'),
(25, 'test', 'test@test.com', 'ddd', 'test', 'Neutral');

-- --------------------------------------------------------

--
-- 資料表結構 `route`
--

CREATE TABLE `route` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `row` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `route`
--

INSERT INTO `route` (`id`, `name`, `row`) VALUES
(43, 'ddd', 2),
(47, 'test', 3),
(49, 'aaa', 2);

-- --------------------------------------------------------

--
-- 資料表結構 `route-station`
--

CREATE TABLE `route-station` (
  `id` int(11) NOT NULL,
  `station` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `need` int(11) NOT NULL,
  `stop` int(11) NOT NULL,
  `route` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `route-station`
--

INSERT INTO `route-station` (`id`, `station`, `rank`, `need`, `stop`, `route`) VALUES
(195, 3, 1, 3, 2, 47),
(196, 4, 1, 2, 3, 47),
(197, 5, 1, 3, 2, 47),
(198, 7, 1, 2, 3, 47),
(199, 6, 1, 3, 2, 47),
(200, 8, 1, 2, 3, 47),
(201, 13, 1, 3, 2, 47),
(204, 3, 1, 2, 3, 49),
(205, 4, 1, 3, 2, 49),
(206, 5, 1, 3, 2, 49),
(207, 6, 1, 2, 3, 49),
(208, 3, 0, 3, 2, 43),
(209, 4, 1, 2, 3, 43),
(210, 5, 2, 3, 2, 43),
(211, 7, 3, 2, 3, 43);

-- --------------------------------------------------------

--
-- 資料表結構 `station`
--

CREATE TABLE `station` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `station`
--

INSERT INTO `station` (`id`, `name`) VALUES
(3, 'test'),
(4, '中正紀念堂'),
(5, '東門'),
(6, '大安森林公園'),
(7, '大安'),
(8, '信義安和'),
(13, '臺大'),
(14, 'idk'),
(16, '台北車站');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `basic`
--
ALTER TABLE `basic`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `bus`
--
ALTER TABLE `bus`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`form`);

--
-- 資料表索引 `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `response`
--
ALTER TABLE `response`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `route`
--
ALTER TABLE `route`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `route-station`
--
ALTER TABLE `route-station`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `station`
--
ALTER TABLE `station`
  ADD PRIMARY KEY (`id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `basic`
--
ALTER TABLE `basic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `bus`
--
ALTER TABLE `bus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `response`
--
ALTER TABLE `response`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `route`
--
ALTER TABLE `route`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `route-station`
--
ALTER TABLE `route-station`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `station`
--
ALTER TABLE `station`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
