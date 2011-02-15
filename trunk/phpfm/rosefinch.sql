-- phpMyAdmin SQL Dump
-- version 3.0.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2011 年 02 月 15 日 12:31
-- 服务器版本: 5.0.67
-- PHP 版本: 5.2.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `rosefinch`
--

-- --------------------------------------------------------

--
-- 表的结构 `fileindex`
--

CREATE TABLE IF NOT EXISTS `fileindex` (
  `path_hash` varchar(32) NOT NULL,
  `path` text NOT NULL,
  `name` varchar(255) NOT NULL,
  `size` int(10) unsigned default NULL,
  `type` varchar(50) NOT NULL,
  `modified` datetime NOT NULL,
  `refreshed` tinyint(1) NOT NULL,
  PRIMARY KEY  (`path_hash`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `size` (`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(128) NOT NULL,
  `password` varchar(32) NOT NULL,
  `privilege` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
