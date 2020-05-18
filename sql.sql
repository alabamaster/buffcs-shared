-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Май 18 2020 г., 08:58
-- Версия сервера: 10.1.41-MariaDB-0+deb9u1
-- Версия PHP: 7.3.13-1+0~20191218.50+debian9~1.gbp23c2da

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `buffcs`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ez_buy_logs`
--

CREATE TABLE `ez_buy_logs` (
  `table_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `web_id` int(11) DEFAULT NULL,
  `steamid` varchar(64) DEFAULT NULL,
  `nickname` varchar(64) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `access` varchar(64) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL COMMENT 'server id',
  `pid` int(11) DEFAULT NULL COMMENT 'privilege id',
  `days` int(11) DEFAULT NULL,
  `shop` varchar(64) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `vk` varchar(64) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ez_editor`
--

CREATE TABLE `ez_editor` (
  `id` int(11) NOT NULL,
  `content` text,
  `created` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL COMMENT 'privilege id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ez_privileges`
--

CREATE TABLE `ez_privileges` (
  `id` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `access` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `icon_img` varchar(64) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ez_privileges_times`
--

CREATE TABLE `ez_privileges_times` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ez_promo_codes`
--

CREATE TABLE `ez_promo_codes` (
  `id` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL COMMENT 'privilege id',
  `sid` int(11) DEFAULT NULL COMMENT 'server id',
  `code` varchar(255) DEFAULT NULL,
  `discount` int(11) NOT NULL,
  `dateCreated` int(11) NOT NULL,
  `dateExpired` int(11) NOT NULL,
  `count_use` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ez_promo_logs`
--

CREATE TABLE `ez_promo_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `was_used` tinyint(4) NOT NULL DEFAULT '0',
  `browser` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `discount` int(11) NOT NULL,
  `sid` int(11) DEFAULT NULL COMMENT 'server id',
  `pid` int(11) DEFAULT NULL COMMENT 'privilege id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `ez_buy_logs`
--
ALTER TABLE `ez_buy_logs`
  ADD PRIMARY KEY (`table_id`);

--
-- Индексы таблицы `ez_editor`
--
ALTER TABLE `ez_editor`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ez_privileges`
--
ALTER TABLE `ez_privileges`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ez_privileges_times`
--
ALTER TABLE `ez_privileges_times`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ez_promo_codes`
--
ALTER TABLE `ez_promo_codes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ez_promo_logs`
--
ALTER TABLE `ez_promo_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `ez_buy_logs`
--
ALTER TABLE `ez_buy_logs`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ez_editor`
--
ALTER TABLE `ez_editor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ez_privileges`
--
ALTER TABLE `ez_privileges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ez_privileges_times`
--
ALTER TABLE `ez_privileges_times`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ez_promo_codes`
--
ALTER TABLE `ez_promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `ez_promo_logs`
--
ALTER TABLE `ez_promo_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
