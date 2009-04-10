-- phpMyAdmin SQL Dump
-- version 2.11.8.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 10, 2009 at 05:58 下午
-- Server version: 5.0.45
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ebaybo`
--

-- --------------------------------------------------------

--
-- Table structure for table `qo_dependencies`
--

CREATE TABLE IF NOT EXISTS `qo_dependencies` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `directory` varchar(255) default '' COMMENT 'The directory within the modules directory stated in the system/os/config.php',
  `file` varchar(255) default NULL COMMENT 'The file that contains the dependency',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `qo_dependencies`
--


-- --------------------------------------------------------

--
-- Table structure for table `qo_domains`
--

CREATE TABLE IF NOT EXISTS `qo_domains` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(35) default NULL,
  `description` text,
  `is_singular` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `qo_domains`
--

INSERT INTO `qo_domains` (`id`, `name`, `description`, `is_singular`) VALUES
(1, 'All Modules', 'All the modules', 0),
(2, 'QoPreferences', '配置模块', 1),
(3, '系统管理模块', '包括用户管理，用户组管理，用户组权限管理，eBay帐号管理，eBay代理管理', 1),
(4, '订单管理模块', 'The QoOrders module', 1),
(5, '付款管理模块', 'The QoTransactions module', 1),
(6, '货运管理模块', 'The Shipments module', 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_domains_has_modules`
--

CREATE TABLE IF NOT EXISTS `qo_domains_has_modules` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `qo_domains_id` int(11) unsigned default NULL,
  `qo_modules_id` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `qo_domains_has_modules`
--

INSERT INTO `qo_domains_has_modules` (`id`, `qo_domains_id`, `qo_modules_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 8),
(7, 2, 1),
(8, 3, 9),
(9, 4, 10),
(10, 5, 11),
(11, 6, 12);

-- --------------------------------------------------------

--
-- Table structure for table `qo_ebay_proxy`
--

CREATE TABLE IF NOT EXISTS `qo_ebay_proxy` (
  `id` int(11) NOT NULL auto_increment,
  `ebay_seller_id` varchar(60) NOT NULL,
  `proxy_host` varchar(200) NOT NULL,
  `proxy_port` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `qo_ebay_proxy`
--

INSERT INTO `qo_ebay_proxy` (`id`, `ebay_seller_id`, `proxy_host`, `proxy_port`) VALUES
(1, 'TESTUSER_heshuai04', '127.0.0.1', 8118),
(2, 'TESTUSER_heshuai05', 'http://192.168.5.193', 8081);

-- --------------------------------------------------------

--
-- Table structure for table `qo_ebay_seller`
--

CREATE TABLE IF NOT EXISTS `qo_ebay_seller` (
  `id` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `status` char(1) NOT NULL,
  `devId` text NOT NULL,
  `appId` text NOT NULL,
  `cert` text NOT NULL,
  `token` text NOT NULL,
  `tokenExpiry` datetime NOT NULL,
  `currency` varchar(3) NOT NULL,
  `site` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_ebay_seller`
--

INSERT INTO `qo_ebay_seller` (`id`, `email`, `status`, `devId`, `appId`, `cert`, `token`, `tokenExpiry`, `currency`, `site`) VALUES
('TESTUSER_heshuai04', 'heshuai04@gmail.com', 'A', '81d79ca0-c641-4b26-ab37-6ca5bbf6fd34', 'Creasion-2b89-4331-9d17-ed62ce38b7b6', '07278e04-4e23-465a-ab45-4fb6ad08af7b', 'AgAAAA**AQAAAA**aAAAAA**pGy/SQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4CoDZODqAqdj6x9nY+seQ**ExoBAA**AAMAAA**QMb6da3xBI2I7XFfWjQQQRuo3mjdnwpr6szDQpG9ttp76UDsKMU6bgBtdDdCWsqm3OxAu9p8yOa2+qM9nTIvc/LAEA8+XyzyXtLSm2uWXYAPPmdqdFsWPLGIoLwXXdG9dd59b5/QDac6PBEhJviAUZrkENqOcU+F/lT41Xf1u91U5vllhtCtsguJhcS84XhAYDJ5rlk4X4LWeV1YLMnwkIsMEvH71wMxK1kh6WjAFa5MmAkKnq2DAEHp+838ammQU9poa7e95wNLTufGygyW0HWNJJ4m1PgjrpdboZD4uWq9LCXm5XAw6vcXo980wM8nlnk8S3nyD2/WNKBZ6Wqf09QlfwKBT4HI3eiDotzjdVUlIu22jb0AudD+7mEhFbmL6kSL0VZp65WyHXaNf4emzrHL8c0ilTiirSm7aEXF34fFmuAdyvRhVwX+bqcVqYItpXvxjNimBF3F/bGMXQcI2Lzsk98GxAOLnRnXP/PXnaHtJpwtP3jrjPP4zUCd/PJSvUvwBSMYGtfNjd4YrXhbILXeaGLZrKr8QAkgzqpd/b4nAUFJw3PrHpMocmstIY8Y1LtDsq3P4NUYpYSoRUvi/hSYMUM++j7ZqTbvu/T9so6IRWKtRYPsYPtNS8tqhmby/1LnWLb3AZYYte2n1/30/5SJIvyybxQ/nMt9fOnp0Yw9OY737u59p5TsSA28sqvVGCGerohwoo3TUmmJgVN1q+gr7YwmtzPEZ1TrAmhxkOwH6G9DB/vsx9b6wuJk5hib', '2009-03-27 20:45:26', 'USD', 'US');

-- --------------------------------------------------------

--
-- Table structure for table `qo_error_log`
--

CREATE TABLE IF NOT EXISTS `qo_error_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `text` text,
  `timestamp` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `qo_error_log`
--

INSERT INTO `qo_error_log` (`id`, `text`, `timestamp`) VALUES
(1, 'Script: module.php, Method: find_files, Missing file: system/modules/users/users.css', '2009-03-22 04:27:35'),
(2, 'Script: module.php, Method: find_files, Missing file: system/modules/users/users.php', '2009-03-22 04:27:35'),
(3, 'Could not successfully run query (insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,\n        insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,\n        ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values\n        (''ORD200903A0001'',''W'',''NotSpecified'',''PayPal'',''testuser_heshuai04'',''testuser_heshuai05'',''USD'',''1'',\n        '''','''',''USD'',''25.99'',''Test User'',\n        ''heshuai05@gmail.com'',''address'','''',\n        ''city'',''WA'',''98102'',\n        ''None'',''(180) 011-1111 ext.: 1'',''eBay'',''2009-03-27 19:15:39'')) from DB: Duplicate entry ''ORD200903A0001'' for key 1', '2009-03-27 15:03:12'),
(4, 'creteOrderFromEbayOrder: query error (insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,\n            insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,\n            ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values\n            (''ORD200903A0001'',''W'',''NotSpecified'',''PayPal'',''testuser_heshuai04'',''testuser_heshuai05'',''USD'',''0'',\n            ''USD'',''0'',''USD'',''24.99'',''Test User'',\n            ''heshuai05@gmail.com'',''address'','''',\n            ''city'',''WA'',''98102'',\n            ''None'',''(180) 011-1111 ext.: 1'',''eBay'',''2009-03-26 22:22:03'')) from DB: Duplicate entry ''ORD200903A0001'' for key 1', '2009-03-27 16:19:35'),
(5, 'createOrderFromEbayTransaction: query error (insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,\n        insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,\n        ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values\n        (''ORD200903A0001'',''W'',''NotSpecified'',''PayPal'',''testuser_heshuai04'',''testuser_heshuai05'',''USD'',''2.5'',\n        '''','''',''USD'',''27.49'',''Test User'',\n        ''heshuai05@gmail.com'',''address'','''',\n        ''city'',''WA'',''98102'',\n        ''None'',''(180) 011-1111 ext.: 1'',''eBay'',''2009-03-26 23:16:30'')) from DB: Duplicate entry ''ORD200903A0001'' for key 1', '2009-03-27 16:19:35'),
(6, 'createOrderFromEbayTransaction: query error (insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,\n        insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,\n        ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values\n        (''ORD200903A0001'',''W'',''NotSpecified'',''PayPal'',''testuser_heshuai04'',''testuser_heshuai05'',''USD'',''2.5'',\n        '''','''',''USD'',''27.49'',''Test User'',\n        ''heshuai05@gmail.com'',''address'','''',\n        ''city'',''WA'',''98102'',\n        ''None'',''(180) 011-1111 ext.: 1'',''eBay'',''2009-03-26 23:32:56'')) from DB: Duplicate entry ''ORD200903A0001'' for key 1', '2009-03-27 16:19:35'),
(7, 'createOrderFromEbayTransaction: query error (insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,\n        insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,\n        ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values\n        (''ORD200903A0001'',''W'',''NotSpecified'',''PayPal'',''testuser_heshuai04'',''testuser_heshuai05'',''USD'',''2.5'',\n        '''','''',''USD'',''27.49'',''Test User'',\n        ''heshuai05@gmail.com'',''address'','''',\n        ''city'',''WA'',''98102'',\n        ''None'',''(180) 011-1111 ext.: 1'',''eBay'',''2009-03-26 23:44:58'')) from DB: Duplicate entry ''ORD200903A0001'' for key 1', '2009-03-27 16:19:35'),
(8, 'createOrderFromEbayTransaction: query error (insert into qo_orders (id,status,shippingMethod,paymentMethod,sellerId,buyerId,shippingFeeCurrency,shippingFeeValue,\n        insuranceCurrency,insuranceValue,grandTotalCurrency,grandTotalValue,ebayName,ebayEmail,ebayAddress1,ebayAddress2,\n        ebayCity,ebayStateOrProvince,ebayPostalCode,ebayCountry,ebayPhone,createdBy,createdOn) values\n        (''ORD200903A0001'',''W'',''NotSpecified'',''PayPal'',''testuser_heshuai04'',''testuser_heshuai05'',''USD'',''2.5'',\n        '''','''',''USD'',''27.49'',''Test User'',\n        ''heshuai05@gmail.com'',''address'','''',\n        ''city'',''WA'',''98102'',\n        ''None'',''(180) 011-1111 ext.: 1'',''eBay'',''2009-03-26 23:52:49'')) from DB: Duplicate entry ''ORD200903A0001'' for key 1', '2009-03-27 16:19:35'),
(9, 'AddOrderDetailBySameBuy: sql error (update qo_orders set grandTotalValue = grandTotalValue + 49.98,shippingFeeValue = shippingFeeValue +  where id = ''ORD200903A0008'') from DB: You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''where id = ''ORD200903A0008'''' at line 1', '2009-03-27 16:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `qo_groups`
--

CREATE TABLE IF NOT EXISTS `qo_groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(35) default NULL,
  `description` text,
  `importance` int(3) unsigned default '1',
  `active` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `qo_groups`
--

INSERT INTO `qo_groups` (`id`, `name`, `description`, `importance`, `active`) VALUES
(1, 'administrator', 'System administrator', 100, 1),
(2, 'user', 'General user', 50, 1),
(3, 'demo', 'Demo user', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_groups_has_domain_privileges`
--

CREATE TABLE IF NOT EXISTS `qo_groups_has_domain_privileges` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `qo_groups_id` int(11) unsigned default '0',
  `qo_domains_id` int(11) unsigned default '0',
  `qo_privileges_id` int(11) unsigned default '0',
  `is_allowed` tinyint(1) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `qo_groups_has_domain_privileges`
--

INSERT INTO `qo_groups_has_domain_privileges` (`id`, `qo_groups_id`, `qo_domains_id`, `qo_privileges_id`, `is_allowed`) VALUES
(1, 3, 1, 1, 1),
(2, 3, 2, 2, 1),
(3, 1, 3, 3, 1),
(8, 1, 6, 1, 1),
(9, 1, 4, 4, 1),
(10, 1, 5, 5, 1),
(11, 1, 6, 6, 1),
(12, 3, 0, 3, 0),
(13, 3, 0, 4, 0),
(14, 3, 0, 5, 0),
(15, 3, 0, 6, 0);

-- --------------------------------------------------------

--
-- Table structure for table `qo_groups_has_members`
--

CREATE TABLE IF NOT EXISTS `qo_groups_has_members` (
  `qo_groups_id` int(11) unsigned NOT NULL default '0',
  `qo_members_id` int(11) unsigned NOT NULL default '0',
  `active` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is the member currently active in this group',
  `admin` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is the member the administrator of this group',
  PRIMARY KEY  (`qo_members_id`,`qo_groups_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_groups_has_members`
--

INSERT INTO `qo_groups_has_members` (`qo_groups_id`, `qo_members_id`, `active`, `admin`) VALUES
(3, 3, 1, 1),
(1, 4, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_launchers`
--

CREATE TABLE IF NOT EXISTS `qo_launchers` (
  `id` int(2) unsigned NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `qo_launchers`
--

INSERT INTO `qo_launchers` (`id`, `name`) VALUES
(1, 'autorun'),
(2, 'contextmenu'),
(3, 'quickstart'),
(4, 'shortcut');

-- --------------------------------------------------------

--
-- Table structure for table `qo_members`
--

CREATE TABLE IF NOT EXISTS `qo_members` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `first_name` varchar(25) default NULL,
  `last_name` varchar(35) default NULL,
  `email_address` varchar(55) default NULL,
  `password` varchar(15) default NULL,
  `language` varchar(5) default 'en',
  `active` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is the member currently active',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `qo_members`
--

INSERT INTO `qo_members` (`id`, `first_name`, `last_name`, `email_address`, `password`, `language`, `active`) VALUES
(3, 'Todd', 'Murdock', 'demo', 'demo', 'en', 1),
(4, 'Todd', 'Murdock', 'admin', 'admin', 'en', 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_members_has_module_launchers`
--

CREATE TABLE IF NOT EXISTS `qo_members_has_module_launchers` (
  `qo_members_id` int(11) unsigned NOT NULL default '0',
  `qo_groups_id` int(11) unsigned NOT NULL default '0',
  `qo_modules_id` int(11) unsigned NOT NULL default '0',
  `qo_launchers_id` int(10) unsigned NOT NULL default '0',
  `sort_order` int(5) unsigned NOT NULL default '0' COMMENT 'sort within each launcher',
  PRIMARY KEY  (`qo_members_id`,`qo_groups_id`,`qo_modules_id`,`qo_launchers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_members_has_module_launchers`
--

INSERT INTO `qo_members_has_module_launchers` (`qo_members_id`, `qo_groups_id`, `qo_modules_id`, `qo_launchers_id`, `sort_order`) VALUES
(0, 0, 1, 2, 0),
(3, 3, 1, 3, 1),
(3, 3, 4, 3, 0),
(4, 1, 10, 4, 7),
(3, 3, 2, 4, 4),
(3, 3, 8, 4, 3),
(3, 3, 5, 4, 2),
(3, 3, 4, 4, 1),
(3, 3, 1, 4, 0),
(3, 3, 4, 1, 0),
(3, 3, 8, 1, 1),
(4, 1, 9, 4, 6),
(4, 1, 11, 4, 8),
(4, 1, 12, 4, 9);

-- --------------------------------------------------------

--
-- Table structure for table `qo_modules`
--

CREATE TABLE IF NOT EXISTS `qo_modules` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `author` varchar(35) default NULL,
  `version` varchar(15) default NULL,
  `url` varchar(255) default NULL COMMENT 'Url which provides information',
  `description` text,
  `module_type` varchar(35) default NULL COMMENT 'The ''moduleType'' property of the client module',
  `module_id` varchar(35) default NULL COMMENT 'The ''moduleId'' property of the client module',
  `active` tinyint(1) unsigned NOT NULL default '0' COMMENT 'Is the module currently active',
  `load_on_demand` tinyint(1) unsigned NOT NULL default '1' COMMENT 'Preload this module at start up?',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `qo_modules`
--

INSERT INTO `qo_modules` (`id`, `author`, `version`, `url`, `description`, `module_type`, `module_id`, `active`, `load_on_demand`) VALUES
(1, 'Todd Murdock', '1.0', 'http://www.qwikioffice.com', 'A system application.  Allows users to set, and save their desktop preferences to the database.', 'system/preferences', 'qo-preferences', 1, 1),
(2, 'Jack Slocum', '1.0', 'http://www.qwikioffice.com', 'Demo of window with grid.', 'demo', 'demo-grid', 1, 1),
(3, 'Jack Slocum', '1.0', 'http://www.qwikioffice.com', 'Demo of window with tabs.', 'demo', 'demo-tabs', 1, 1),
(4, 'Jack Slocum', '1.0', 'http://www.qwikioffice.com', 'Demo of window with accordion.', 'demo', 'demo-acc', 1, 1),
(5, 'Jack Slocum', '1.0', 'http://www.qwikioffice.com', 'Demo of window with layout.', 'demo', 'demo-layout', 1, 1),
(8, 'Jack Slocum', '1.0', 'http://www.qwikioffice.com', 'Demo of bogus window.', 'demo', 'demo-bogus', 1, 1),
(9, 'heshuai', '1.0', 'http://heshuai64.gnway.net', 'system manager', 'system/manage', 'qo-manage', 1, 1),
(10, 'heshuai', '1.0', 'http://heshuai64.gnway.net', 'orders manager', 'orders', 'qo-orders', 1, 1),
(11, 'heshuai', '1.0', 'http://heshuai64.gnway.net', 'Transactions manager', 'transactions', 'qo-transactions', 1, 1),
(12, 'heshuai', '1.0', 'http://heshuai64.gnway.net', 'shipments manager', 'shipments', 'qo-shipments', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_modules_actions`
--

CREATE TABLE IF NOT EXISTS `qo_modules_actions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `qo_modules_id` int(11) unsigned default NULL,
  `name` varchar(35) default NULL,
  `description` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

--
-- Dumping data for table `qo_modules_actions`
--

INSERT INTO `qo_modules_actions` (`id`, `qo_modules_id`, `name`, `description`) VALUES
(1, 0, 'loadModule', 'Allow the user to load the module.  Give them access to it.  Does not belong to any particular module'),
(2, 1, 'saveAppearance', 'Save appearance'),
(3, 1, 'saveAutorun', 'Save autorun'),
(4, 1, 'saveBackground', 'Save background'),
(5, 1, 'saveQuickstart', 'Save quickstart'),
(6, 1, 'saveShortcut', 'Save shortcut'),
(7, 1, 'viewThemes', 'View themes'),
(8, 1, 'viewWallpapers', 'View wallpapers'),
(9, 9, 'getAllMember', 'Get all member'),
(10, 9, 'getAllGroup', 'Get all group'),
(11, 9, 'updateMember', 'update memeber information'),
(12, 9, 'getGroupDomainPrivilege', 'get group domain privilege'),
(13, 10, 'searchOrder', '查询订单'),
(14, 10, 'getOrderInfo', '获取订单信息'),
(15, 11, 'searchTransaction', '查询付款'),
(16, 11, 'getTransactionInfo', '获取付款信息'),
(17, 10, 'getOrderDetail', '获取订单描述信息'),
(18, 10, 'getOrderTransaction', '获取订单付款信息'),
(19, 10, 'getOrderShipment', '获取订单货运信息'),
(20, 10, 'addOrderDetail', '添加订单描述'),
(21, 10, 'deleteOrderDetail', '删除订单描述'),
(22, 10, 'addOrderTransaction', '添加订单付款'),
(23, 10, 'deleteOrderTransaction', '删除订单付款'),
(24, 10, 'readMapOrderTransaction', '读取和订单关联的付款'),
(25, 10, 'mapOrderTransaction', '匹配订单与付款'),
(26, 10, 'saveOrderInfo', '保存订单信息'),
(27, 11, 'getTransactionOrder', '获取付款订单信息'),
(28, 11, 'readMapTransactionOrder', '获取付款订单信息'),
(29, 11, 'mapTransactionOrder', '匹配付款与订单'),
(30, 11, 'saveTransactionInfo', '保存付款信息'),
(31, 12, 'searchShipment', '查询货运信息'),
(32, 12, 'getShipmentInfo', '获取货运信息'),
(33, 12, 'getShipmentDetail', '获取货运描述信息'),
(34, 12, 'addShipmentDetail', '添加货运描述'),
(35, 12, 'saveShipmentInfo', '保存货运信息'),
(36, 12, 'verifyShipment', '验证货运信息'),
(37, 12, 'packShipment', '包裹货物'),
(38, 9, 'getAllEbaySeller', '获取所有eBay账户信息'),
(39, 9, 'updateEbaySeller', '更新eBay账户信息'),
(40, 9, 'deleteEbaySeller', '删除eBay账户'),
(41, 9, 'addEbaySeller', '添加eBay帐号'),
(43, 9, 'getAllEbayProxy', '获取所有eBay代理'),
(44, 9, 'addEbayProxy', '添加eBay代理'),
(45, 9, 'updateEbayProxy', '更新eBay代理'),
(46, 9, 'deleteEbayProxy', '删除eBay代理');

-- --------------------------------------------------------

--
-- Table structure for table `qo_modules_files`
--

CREATE TABLE IF NOT EXISTS `qo_modules_files` (
  `qo_modules_id` int(11) unsigned NOT NULL default '0',
  `directory` varchar(255) default '' COMMENT 'The directory within the modules directory stated in the system/os/config.php',
  `file` varchar(255) NOT NULL default '' COMMENT 'The file that contains the dependency',
  `is_stylesheet` tinyint(1) unsigned default '0',
  `is_server_module` tinyint(1) unsigned default '0',
  `is_client_module` tinyint(1) unsigned default '0',
  `class_name` varchar(55) default '',
  PRIMARY KEY  (`qo_modules_id`,`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_modules_files`
--

INSERT INTO `qo_modules_files` (`qo_modules_id`, `directory`, `file`, `is_stylesheet`, `is_server_module`, `is_client_module`, `class_name`) VALUES
(1, 'qo-preferences/', 'qo-preferences-override.js', 0, 0, 0, ''),
(4, 'acc-win/', 'acc-win-override.js', 0, 0, 0, ''),
(5, 'layout-win/', 'layout-win-override.js', 0, 0, 0, ''),
(8, 'bogus/bogus-win/', 'bogus-win-override.js', 0, 0, 0, ''),
(2, 'grid-win/', 'grid-win-override.js', 0, 0, 0, ''),
(3, 'tab-win/', 'tab-win-override.js', 0, 0, 0, ''),
(1, 'qo-preferences/', 'qo-preferences.js', 0, 0, 1, 'QoDesk.QoPreferences'),
(1, 'qo-preferences/', 'qo-preferences.php', 0, 1, 0, 'QoPreferences'),
(2, 'grid-win/', 'grid-win.js', 0, 0, 1, 'QoDesk.GridWindow'),
(3, 'tab-win/', 'tab-win.js', 0, 0, 1, 'QoDesk.TabWindow'),
(4, 'acc-win/', 'acc-win.js', 0, 0, 1, 'QoDesk.AccordionWindow'),
(5, 'layout-win/', 'layout-win.js', 0, 0, 1, 'QoDesk.LayoutWindow'),
(8, 'bogus/bogus-win/', 'bogus-win.js', 0, 0, 1, 'QoDesk.BogusWindow'),
(1, 'qo-preferences/', 'qo-preferences.css', 1, 0, 0, ''),
(2, 'grid-win/', 'grid-win.css', 1, 0, 0, ''),
(3, 'tab-win/', 'tab-win.css', 1, 0, 0, ''),
(4, 'acc-win/', 'acc-win.css', 1, 0, 0, ''),
(5, 'layout-win/', 'layout-win.css', 1, 0, 0, ''),
(8, 'bogus/bogus-win/', 'bogus-win.css', 1, 0, 0, ''),
(9, 'manage/', 'manage.css', 1, 0, 0, ''),
(9, 'manage/', 'manage.js', 0, 0, 1, 'QoDesk.Manage'),
(9, 'manage/', 'manage-override.js', 0, 0, 0, ''),
(9, 'manage/', 'manage.php', 0, 1, 0, 'QoManage'),
(10, 'orders/', 'orders.css', 1, 0, 0, ''),
(10, 'orders/', 'orders.js', 0, 0, 1, 'QoDesk.Orders'),
(10, 'orders/', 'orders-override.js', 0, 0, 0, ''),
(10, 'orders/', 'orders.php', 0, 1, 0, 'QoOrders'),
(11, 'transactions/', 'transactions.css', 1, 0, 0, ''),
(11, 'transactions/', 'transactions.js', 0, 0, 1, 'QoDesk.Transactions'),
(11, 'transactions/', 'transactions-override.js', 0, 0, 0, ''),
(11, 'transactions/', 'transactions.php', 0, 1, 0, 'QoTransactions'),
(12, 'shipments/', 'shipments.css', 1, 0, 0, ''),
(12, 'shipments/', 'shipments.js', 0, 0, 1, 'QoDesk.Shipments'),
(12, 'shipments/', 'shipments-override.js', 0, 0, 0, ''),
(12, 'shipments/', 'shipments.php', 0, 1, 0, 'QoShipments');

-- --------------------------------------------------------

--
-- Table structure for table `qo_modules_has_dependencies`
--

CREATE TABLE IF NOT EXISTS `qo_modules_has_dependencies` (
  `qo_modules_id` int(11) unsigned NOT NULL default '0',
  `qo_dependencies_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`qo_modules_id`,`qo_dependencies_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_modules_has_dependencies`
--


-- --------------------------------------------------------

--
-- Table structure for table `qo_orders`
--

CREATE TABLE IF NOT EXISTS `qo_orders` (
  `id` varchar(14) NOT NULL,
  `type` varchar(10) NOT NULL,
  `status` char(1) NOT NULL,
  `shippingMethod` char(1) NOT NULL,
  `paymentMethod` char(1) NOT NULL,
  `remarks` text NOT NULL,
  `sellerId` varchar(50) NOT NULL,
  `buyerId` varchar(50) NOT NULL,
  `shippingFeeCurrency` varchar(3) NOT NULL,
  `shippingFeeValue` decimal(10,2) NOT NULL,
  `insuranceCurrency` varchar(3) NOT NULL,
  `insuranceValue` decimal(10,2) NOT NULL,
  `discountCurrency` varchar(3) NOT NULL,
  `discountValue` decimal(10,2) NOT NULL,
  `grandTotalCurrency` varchar(3) NOT NULL,
  `grandTotalValue` decimal(10,2) NOT NULL,
  `ebayName` varchar(50) NOT NULL,
  `ebayEmail` varchar(150) NOT NULL,
  `ebayAddress1` varchar(250) NOT NULL,
  `ebayAddress2` varchar(50) NOT NULL,
  `ebayCity` varchar(30) NOT NULL,
  `ebayStateOrProvince` varchar(50) NOT NULL,
  `ebayPostalCode` varchar(20) NOT NULL,
  `ebayCountry` varchar(50) NOT NULL,
  `ebayPhone` varchar(50) NOT NULL,
  `paypalName` varchar(50) NOT NULL,
  `paypalEmail` varchar(150) NOT NULL,
  `paypalAddress1` varchar(250) NOT NULL,
  `paypalAddress2` varchar(50) NOT NULL,
  `paypalCity` varchar(30) NOT NULL,
  `paypalStateOrProvince` varchar(50) NOT NULL,
  `paypalPostalCode` varchar(20) NOT NULL,
  `paypalCountry` varchar(50) NOT NULL,
  `paypalPhone` varchar(50) NOT NULL,
  `createdBy` varchar(50) NOT NULL,
  `createdOn` datetime NOT NULL,
  `modifiedBy` varchar(50) NOT NULL,
  `modifiedOn` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sellerId` (`sellerId`),
  KEY `buyerId` (`buyerId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_orders`
--

INSERT INTO `qo_orders` (`id`, `type`, `status`, `shippingMethod`, `paymentMethod`, `remarks`, `sellerId`, `buyerId`, `shippingFeeCurrency`, `shippingFeeValue`, `insuranceCurrency`, `insuranceValue`, `discountCurrency`, `discountValue`, `grandTotalCurrency`, `grandTotalValue`, `ebayName`, `ebayEmail`, `ebayAddress1`, `ebayAddress2`, `ebayCity`, `ebayStateOrProvince`, `ebayPostalCode`, `ebayCountry`, `ebayPhone`, `paypalName`, `paypalEmail`, `paypalAddress1`, `paypalAddress2`, `paypalCity`, `paypalStateOrProvince`, `paypalPostalCode`, `paypalCountry`, `paypalPhone`, `createdBy`, `createdOn`, `modifiedBy`, `modifiedOn`) VALUES
('ORD200903A0052', '', 'P', 'N', 'P', '', 'testuser_heshuai04', 'testuser_heshuai05', 'USD', 5.00, 'USD', 0.00, '', 0.00, 'USD', 49.99, 'Test User', 'heshuai05@gmail.com', 'address', '', 'city', 'WA', '98102', 'None', '(180) 011-1111 ext.: 1', 'John Smith', 'buyer@paypalsandbox.com', 'John Smith', '', 'San Jose', 'CA', '95131', 'United States', '', 'eBay', '2009-03-18 21:10:54', 'Paypal', '2009-04-01 16:30:42'),
('ORD200903A0053', '', 'W', 'N', 'P', '', 'testuser_heshuai04', 'testuser_heshuai05', 'USD', 5.00, 'USD', 0.00, '', 0.00, 'USD', 119.97, 'Test User', 'heshuai05@gmail.com', 'address', '', 'city', 'WA', '98102', 'None', '(180) 011-1111 ext.: 1', '', '', '', '', '', '', '', '', '', 'eBay', '2009-03-19 16:49:02', '', '0000-00-00 00:00:00'),
('ORD200903A0054', '', 'W', 'N', 'P', '', 'testuser_heshuai04', 'testuser_heshuai05', 'USD', 11.00, '', 0.00, '', 0.00, 'USD', 135.95, 'Test User', 'heshuai05@gmail.com', 'address', '', 'city', 'WA', '98102', 'None', '(180) 011-1111 ext.: 1', '', '', '', '', '', '', '', '', '', 'eBay', '2009-03-26 23:16:30', '', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `qo_orders_detail`
--

CREATE TABLE IF NOT EXISTS `qo_orders_detail` (
  `id` bigint(20) NOT NULL auto_increment,
  `ordersId` varchar(14) NOT NULL,
  `skuId` varchar(20) NOT NULL,
  `skuTitle` varchar(120) NOT NULL,
  `itemId` varchar(20) NOT NULL,
  `itemTitle` varchar(120) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitPriceCurrency` varchar(3) NOT NULL,
  `unitPriceValue` decimal(10,2) NOT NULL,
  `ebayTranctionId` varchar(20) NOT NULL,
  `ebayOrderId` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ordersId` (`ordersId`,`skuId`,`itemId`,`ebayTranctionId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=112 ;

--
-- Dumping data for table `qo_orders_detail`
--

INSERT INTO `qo_orders_detail` (`id`, `ordersId`, `skuId`, `skuTitle`, `itemId`, `itemTitle`, `quantity`, `unitPriceCurrency`, `unitPriceValue`, `ebayTranctionId`, `ebayOrderId`) VALUES
(99, 'ORD200903A0054', '', '', '110038587148', 'FixedPriceItem_4', 1, 'USD', 24.99, '23804652001', ''),
(98, 'ORD200903A0053', '', '', '110038586329', 'FixedPriceItem_3', 1, 'USD', 24.99, '23804492001', '1040526'),
(97, 'ORD200903A0053', '', '', '110038458333', 'test_10', 2, 'USD', 24.99, '23782474001', '1040526'),
(96, 'ORD200903A0053', '', '', '110038445870', 'test_10', 1, 'USD', 40.00, '0', '1040526'),
(95, 'ORD200903A0052', '', '', '110038450903', 'test_10', 1, 'USD', 24.99, '23780829001', '1040086'),
(94, 'ORD200903A0052', '', '', '110038436041', 'test_1', 1, 'USD', 20.00, '0', '1040086'),
(100, 'ORD200903A0054', '', '', '110038587274', 'FixedPriceItem_5', 1, 'USD', 24.99, '23804686001', ''),
(101, 'ORD200903A0054', '', '', '110038587365', 'FixedPriceItem_6', 1, 'USD', 24.99, '23804717001', ''),
(102, 'ORD200903A0054', '', '', '110038587418', 'FixedPriceItem_7', 1, 'USD', 24.99, '23804749001', ''),
(103, 'ORD200903A0054', '', '', '110038600875', 'FixedPriceItem_8', 1, 'USD', 24.99, '23807868001', '');

-- --------------------------------------------------------

--
-- Table structure for table `qo_orders_transactions`
--

CREATE TABLE IF NOT EXISTS `qo_orders_transactions` (
  `ordersId` varchar(14) NOT NULL,
  `transactionsId` varchar(14) NOT NULL,
  `status` varchar(1) NOT NULL,
  `amountPayCurrency` varchar(3) NOT NULL,
  `amountPayValue` decimal(10,2) NOT NULL,
  `createdBy` varchar(50) NOT NULL,
  `createdOn` datetime NOT NULL,
  `modifiedBy` varchar(50) NOT NULL,
  `modifiedOn` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_orders_transactions`
--

INSERT INTO `qo_orders_transactions` (`ordersId`, `transactionsId`, `status`, `amountPayCurrency`, `amountPayValue`, `createdBy`, `createdOn`, `modifiedBy`, `modifiedOn`) VALUES
('ORD200903A0052', 'TRA200904A0041', 'A', 'USD', 49.99, 'Paypal', '2009-04-01 16:30:42', 'Paypal', '2009-04-01 16:30:42'),
('ORD200903A0052', 'TRA200904A0050', 'A', 'GBP', 11.00, '', '2009-04-04 18:40:14', '', '0000-00-00 00:00:00'),
('ORD200903A0053', 'TRA200904A0041', 'A', 'USD', 119.97, '', '2009-04-05 03:49:29', '', '0000-00-00 00:00:00'),
('ORD200903A0054', 'TRA200904A0041', 'A', 'USD', 135.95, '', '2009-04-05 03:58:45', '', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `qo_privileges`
--

CREATE TABLE IF NOT EXISTS `qo_privileges` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(35) default NULL,
  `description` text,
  `is_singular` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `qo_privileges`
--

INSERT INTO `qo_privileges` (`id`, `name`, `description`, `is_singular`) VALUES
(1, '加载模块', 'Allows the user access to the loadModule action', 0),
(2, '个性化', 'Allows the user access to all the actions of the QoPreferences mdoule', 1),
(3, '系统模块操作', '超级管理员权限', 1),
(4, '订单模块操作', NULL, 1),
(5, '付款模块操作', NULL, 1),
(6, '货运模块操作', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_privileges_has_module_actions`
--

CREATE TABLE IF NOT EXISTS `qo_privileges_has_module_actions` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `qo_privileges_id` int(11) unsigned default NULL,
  `qo_modules_actions_id` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

--
-- Dumping data for table `qo_privileges_has_module_actions`
--

INSERT INTO `qo_privileges_has_module_actions` (`id`, `qo_privileges_id`, `qo_modules_actions_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(4, 2, 4),
(5, 2, 5),
(6, 2, 6),
(7, 2, 7),
(8, 2, 8),
(9, 3, 9),
(10, 3, 10),
(11, 3, 11),
(12, 3, 12),
(14, 4, 13),
(16, 4, 14),
(17, 5, 15),
(18, 5, 16),
(19, 4, 17),
(20, 4, 18),
(21, 4, 19),
(22, 4, 20),
(23, 4, 21),
(24, 4, 22),
(25, 4, 23),
(26, 4, 24),
(27, 4, 25),
(28, 4, 26),
(29, 5, 27),
(30, 5, 28),
(31, 5, 29),
(32, 5, 30),
(33, 6, 31),
(34, 6, 32),
(36, 6, 33),
(37, 6, 34),
(38, 6, 35),
(39, 6, 36),
(40, 6, 37),
(41, 3, 38),
(42, 3, 39),
(43, 3, 40),
(44, 3, 41),
(45, 3, 42),
(46, 3, 43),
(47, 3, 44),
(48, 3, 45),
(49, 3, 46);

-- --------------------------------------------------------

--
-- Table structure for table `qo_sessions`
--

CREATE TABLE IF NOT EXISTS `qo_sessions` (
  `id` varchar(128) NOT NULL default '' COMMENT 'a randomly generated id',
  `qo_members_id` int(11) unsigned NOT NULL default '0',
  `qo_groups_id` int(11) unsigned default NULL COMMENT 'Group the member signed in under',
  `ip` varchar(16) default NULL,
  `date` datetime default NULL,
  PRIMARY KEY  (`id`,`qo_members_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_sessions`
--

INSERT INTO `qo_sessions` (`id`, `qo_members_id`, `qo_groups_id`, `ip`, `date`) VALUES
('469b11743d1069a25ff27e9970ac86fb', 4, 1, '127.0.0.1', '2009-03-25 13:06:50'),
('219faef5b042b62fcfab15f9c67b5bae', 4, 1, '127.0.0.1', '2009-03-24 17:33:18'),
('38c54ada016a207996ce79649ee7712d', 3, 3, '127.0.0.1', '2009-03-24 18:07:49'),
('7a0239c78384f7617b4daa282bd09034', 4, 1, '127.0.0.1', '2009-03-25 11:29:12'),
('6fe8911d9f22c9dbc47d239eb505c04b', 4, 1, '127.0.0.1', '2009-03-25 13:32:34'),
('56519ea1120ca4f5c94823f119ddb377', 4, 1, '127.0.0.1', '2009-03-25 13:59:01'),
('5dac665c0223cc01b002c9e60b966243', 4, 1, '127.0.0.1', '2009-03-28 09:14:19'),
('d3d958f7405b9dd39509f1ccdb2bc79b', 4, 1, '127.0.0.1', '2009-03-29 03:12:58'),
('6ed1c57095004df5ef64320e4bad3fb2', 4, 1, '192.168.2.101', '2009-03-29 09:36:40'),
('77db07bce1a24e293b1832e0423057f6', 4, 1, '127.0.0.1', '2009-03-31 12:27:26'),
('8a2be20b0c55ec3cb3a2ad841008a7a5', 4, 1, '127.0.0.1', '2009-04-01 17:51:49'),
('0b5367a88ebacd34035cf3a83f38e141', 4, 1, '127.0.0.1', '2009-04-02 14:10:46'),
('1c2da3523b6f92505b0f820c777f3a3a', 4, 1, '127.0.0.1', '2009-04-03 02:32:04'),
('fea05b2d87827ea6874f3fcebed2d295', 4, 1, '127.0.0.1', '2009-04-04 03:07:45'),
('e952cc6f31535e374ae68826f3173083', 4, 1, '127.0.0.1', '2009-04-04 16:09:06'),
('057624e5f0f1fbac09618d6d636d8258', 4, 1, '127.0.0.1', '2009-04-04 19:05:27'),
('a814a4c868d36d0dd652aeb9b5ae8e7f', 4, 1, '127.0.0.1', '2009-04-05 02:08:39'),
('5b8e8556c8d4e940038916c8e7eca03a', 4, 1, '127.0.0.1', '2009-04-08 19:25:06'),
('4f5997c5475feb3161faac35779946fe', 4, 1, '127.0.0.1', '2009-04-08 19:41:22'),
('f431405ed31fa4f69cc0e6d1134e308e', 3, 3, '127.0.0.1', '2009-04-08 20:58:04'),
('7e4f0c23f8112a5b27ee1bc98e6d5aca', 3, 3, '127.0.0.1', '2009-04-08 20:58:37'),
('2013036a6d95b175009bc2af1d959318', 3, 3, '127.0.0.1', '2009-04-08 20:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `qo_shipments`
--

CREATE TABLE IF NOT EXISTS `qo_shipments` (
  `id` varchar(14) NOT NULL,
  `ordersId` varchar(14) NOT NULL,
  `status` char(1) NOT NULL,
  `shipmentMethod` char(1) NOT NULL,
  `packedBy` varchar(50) NOT NULL,
  `packedOn` datetime NOT NULL,
  `shippedBy` varchar(50) NOT NULL,
  `shippedOn` datetime NOT NULL,
  `remarks` text NOT NULL,
  `postalReferenceNo` varchar(50) NOT NULL,
  `shippingFeeCurrency` varchar(3) NOT NULL,
  `shippingFeeValue` decimal(10,2) NOT NULL,
  `shipToName` varchar(50) NOT NULL,
  `shipToEmail` varchar(150) NOT NULL,
  `shipToAddressLine1` varchar(250) NOT NULL,
  `shipToAddressLine2` varchar(50) NOT NULL,
  `shipToCity` varchar(30) NOT NULL,
  `shipToStateOrProvince` varchar(50) NOT NULL,
  `shipToPostalCode` varchar(9) NOT NULL,
  `shipToCountry` varchar(50) NOT NULL,
  `shipToPhoneNo` varchar(50) NOT NULL,
  `createdBy` varchar(50) NOT NULL,
  `createdOn` datetime NOT NULL,
  `modifiedBy` varchar(50) NOT NULL,
  `modifiedOn` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ordersId` (`ordersId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_shipments`
--

INSERT INTO `qo_shipments` (`id`, `ordersId`, `status`, `shipmentMethod`, `packedBy`, `packedOn`, `shippedBy`, `shippedOn`, `remarks`, `postalReferenceNo`, `shippingFeeCurrency`, `shippingFeeValue`, `shipToName`, `shipToEmail`, `shipToAddressLine1`, `shipToAddressLine2`, `shipToCity`, `shipToStateOrProvince`, `shipToPostalCode`, `shipToCountry`, `shipToPhoneNo`, `createdBy`, `createdOn`, `modifiedBy`, `modifiedOn`) VALUES
('SHI200903A0001', 'ORD200903A0052', 'K', 'S', '', '2009-04-05 14:05:56', '', '2009-04-05 14:05:53', 'fsf', 'sfsdf', 'dsf', 0.00, 'fs', 'ffdsf', 'fdsf', 'dsfds', 'fsdfs', 'ffsdf', 'fdsf', 'sfs', 'fsdfs', 'fsfsd', '2009-04-05 14:05:37', 'fsdfsdfsf', '2009-04-05 14:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `qo_shipments_detail`
--

CREATE TABLE IF NOT EXISTS `qo_shipments_detail` (
  `id` bigint(20) NOT NULL auto_increment,
  `shipmentsId` varchar(14) NOT NULL,
  `skuId` varchar(50) NOT NULL,
  `skuTitle` varchar(100) NOT NULL,
  `itemId` varchar(150) NOT NULL,
  `itemTitle` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `shipmentsId` (`shipmentsId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `qo_shipments_detail`
--

INSERT INTO `qo_shipments_detail` (`id`, `shipmentsId`, `skuId`, `skuTitle`, `itemId`, `itemTitle`, `quantity`) VALUES
(1, 'SHI200903A0001', 'a09030600ux0052', '1', '2', '2', 1),
(2, 'SHI200903A0001', 'a09032500ux0136', 'a09032500ux0136', 'a09032500ux0136', 'a09032500ux0136', 1);

-- --------------------------------------------------------

--
-- Table structure for table `qo_styles`
--

CREATE TABLE IF NOT EXISTS `qo_styles` (
  `qo_members_id` int(11) unsigned NOT NULL default '0',
  `qo_groups_id` int(11) unsigned NOT NULL default '0',
  `qo_themes_id` int(11) unsigned NOT NULL default '1',
  `qo_wallpapers_id` int(11) unsigned NOT NULL default '1',
  `backgroundcolor` varchar(6) NOT NULL default 'ffffff',
  `fontcolor` varchar(6) default NULL,
  `transparency` int(3) NOT NULL default '100',
  `wallpaperposition` varchar(6) NOT NULL default 'center',
  PRIMARY KEY  (`qo_members_id`,`qo_groups_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_styles`
--

INSERT INTO `qo_styles` (`qo_members_id`, `qo_groups_id`, `qo_themes_id`, `qo_wallpapers_id`, `backgroundcolor`, `fontcolor`, `transparency`, `wallpaperposition`) VALUES
(0, 0, 2, 1, 'f9f9f9', '000000', 100, 'center'),
(3, 3, 1, 2, '390A0A', 'FFFFFF', 100, 'tile');

-- --------------------------------------------------------

--
-- Table structure for table `qo_themes`
--

CREATE TABLE IF NOT EXISTS `qo_themes` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(25) default NULL COMMENT 'The display name',
  `author` varchar(55) default NULL,
  `version` varchar(25) default NULL,
  `url` varchar(255) default NULL COMMENT 'Url which provides additional information',
  `path_to_thumbnail` varchar(255) default NULL,
  `path_to_file` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `qo_themes`
--

INSERT INTO `qo_themes` (`id`, `name`, `author`, `version`, `url`, `path_to_thumbnail`, `path_to_file`) VALUES
(1, 'Vista Blue', 'Todd Murdock', '0.8', NULL, 'xtheme-vistablue/xtheme-vistablue.png', 'xtheme-vistablue/css/xtheme-vistablue.css'),
(2, 'Vista Black', 'Todd Murdock', '0.8', NULL, 'xtheme-vistablack/xtheme-vistablack.png', 'xtheme-vistablack/css/xtheme-vistablack.css'),
(3, 'Vista Glass', 'Todd Murdock', '0.8', NULL, 'xtheme-vistaglass/xtheme-vistaglass.png', 'xtheme-vistaglass/css/xtheme-vistaglass.css');

-- --------------------------------------------------------

--
-- Table structure for table `qo_transactions`
--

CREATE TABLE IF NOT EXISTS `qo_transactions` (
  `id` varchar(14) NOT NULL,
  `txnId` varchar(50) NOT NULL,
  `transactionTime` datetime NOT NULL,
  `amountCurrency` varchar(3) NOT NULL,
  `amountValue` decimal(10,2) NOT NULL,
  `status` char(1) NOT NULL,
  `remarks` text NOT NULL,
  `createdBy` varchar(50) NOT NULL,
  `createdOn` datetime NOT NULL,
  `modifiedBy` varchar(50) NOT NULL,
  `modifiedOn` datetime NOT NULL,
  `payeeId` varchar(50) NOT NULL,
  `payerId` varchar(50) NOT NULL,
  `payerName` varchar(50) NOT NULL,
  `payerEmail` varchar(120) NOT NULL,
  `payerAddressLine1` varchar(250) NOT NULL,
  `payerAddressLine2` varchar(50) NOT NULL,
  `payerCity` varchar(30) NOT NULL,
  `payerStateOrProvince` varchar(50) NOT NULL,
  `payerPostalCode` varchar(9) NOT NULL,
  `payerCountry` varchar(50) NOT NULL,
  `itemId` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `qo_transactions`
--

INSERT INTO `qo_transactions` (`id`, `txnId`, `transactionTime`, `amountCurrency`, `amountValue`, `status`, `remarks`, `createdBy`, `createdOn`, `modifiedBy`, `modifiedOn`, `payeeId`, `payerId`, `payerName`, `payerEmail`, `payerAddressLine1`, `payerAddressLine2`, `payerCity`, `payerStateOrProvince`, `payerPostalCode`, `payerCountry`, `itemId`) VALUES
('TRA200904A0041', '2541169', '2009-04-01 16:09:25', 'USD', 49.99, 'P', '', '', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'testuser_heshuai04', 'testuser_heshuai05', 'John Smith', 'buyer@paypalsandbox.com', '123, any street', '', 'San Jose', 'CA', '95131', 'United States', '110038450903,110038436041'),
('TRA200904A0051', '222', '2009-04-05 00:00:00', 'EUR', 222.00, 'D', '', '', '2009-04-04 19:06:23', '', '0000-00-00 00:00:00', '22', '222', '2', '22', '2', '22', '22', '2222', '22', '', ''),
('TRA200904A0050', '1', '2009-04-05 00:00:00', 'GBP', 11.00, 'N', '', '', '2009-04-04 18:40:14', '', '0000-00-00 00:00:00', 'testuser_heshuai04', 'testuser_heshuai05', '1111111111', '11111111111', '111111111111', '1111111111', '11111111111', '11111111111111111', '111111', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `qo_wallpapers`
--

CREATE TABLE IF NOT EXISTS `qo_wallpapers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(25) default NULL COMMENT 'Display name',
  `author` varchar(55) default NULL,
  `url` varchar(255) default NULL COMMENT 'Url which provides information',
  `path_to_thumbnail` varchar(255) default NULL,
  `path_to_file` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `qo_wallpapers`
--

INSERT INTO `qo_wallpapers` (`id`, `name`, `author`, `url`, `path_to_thumbnail`, `path_to_file`) VALUES
(1, 'qWikiOffice', 'Todd Murdock', NULL, 'thumbnails/qwikioffice.jpg', 'qwikioffice.jpg'),
(2, 'Colorado Farm', NULL, NULL, 'thumbnails/colorado-farm.jpg', 'colorado-farm.jpg'),
(3, 'Curls On Green', NULL, NULL, 'thumbnails/curls-on-green.jpg', 'curls-on-green.jpg'),
(4, 'Emotion', NULL, NULL, 'thumbnails/emotion.jpg', 'emotion.jpg'),
(5, 'Eos', NULL, NULL, 'thumbnails/eos.jpg', 'eos.jpg'),
(6, 'Fields of Peace', NULL, NULL, 'thumbnails/fields-of-peace.jpg', 'fields-of-peace.jpg'),
(7, 'Fresh Morning', NULL, NULL, 'thumbnails/fresh-morning.jpg', 'fresh-morning.jpg'),
(8, 'Ladybuggin', NULL, NULL, 'thumbnails/ladybuggin.jpg', 'ladybuggin.jpg'),
(9, 'Summer', NULL, NULL, 'thumbnails/summer.jpg', 'summer.jpg'),
(10, 'Blue Swirl', NULL, NULL, 'thumbnails/blue-swirl.jpg', 'blue-swirl.jpg'),
(11, 'Blue Psychedelic', NULL, NULL, 'thumbnails/blue-psychedelic.jpg', 'blue-psychedelic.jpg'),
(12, 'Blue Curtain', NULL, NULL, 'thumbnails/blue-curtain.jpg', 'blue-curtain.jpg'),
(13, 'Blank', NULL, NULL, 'thumbnails/blank.gif', 'blank.gif');

-- --------------------------------------------------------

--
-- Table structure for table `sequence`
--

CREATE TABLE IF NOT EXISTS `sequence` (
  `type` varchar(3) NOT NULL,
  `curType` char(1) NOT NULL,
  `curDate` varchar(6) NOT NULL,
  `curId` int(11) NOT NULL,
  PRIMARY KEY  (`curType`,`curDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sequence`
--

INSERT INTO `sequence` (`type`, `curType`, `curDate`, `curId`) VALUES
('ORD', 'A', '200903', 54),
('TRA', 'A', '200904', 51);
