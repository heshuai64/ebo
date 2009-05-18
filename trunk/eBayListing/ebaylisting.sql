-- phpMyAdmin SQL Dump
-- version 2.11.9.2
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2009 年 05 月 19 日 07:41
-- 服务器版本: 5.0.67
-- PHP 版本: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `ebaylisting`
--

-- --------------------------------------------------------

--
-- 表的结构 `site`
--

CREATE TABLE IF NOT EXISTS `site` (
  `id` int(3) NOT NULL,
  `abbreviation` varchar(8) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `abbreviation` (`abbreviation`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 导出表中的数据 `site`
--

INSERT INTO `site` (`id`, `abbreviation`, `name`) VALUES
(0, 'US', 'United States'),
(2, 'CA', 'Canada'),
(3, 'UK', 'United Kingdom'),
(15, 'AU', 'Australia'),
(16, 'AT', 'Austria'),
(23, 'BEFR', 'Belgium (French)'),
(71, 'FR', 'France'),
(77, 'DE', 'Germany'),
(100, 'Motors', 'US eBay Motors'),
(101, 'IT', 'Italy'),
(123, 'BENL', 'Belgium (Dutch)'),
(146, 'NL', 'Netherlands '),
(186, 'ES', 'Spain'),
(193, 'CH', 'Switzerland'),
(201, 'HK', 'Hong Kong'),
(203, 'IN', 'India'),
(205, 'IE', 'Ireland'),
(207, 'MY', 'Malaysia'),
(210, 'CAFR', 'Canada (French)'),
(211, 'PH', 'Philippines'),
(212, 'PL', 'Poland'),
(216, 'SG', 'Singapore');
