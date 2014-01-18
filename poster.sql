-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 30, 2013 at 07:25 AM
-- Server version: 5.1.44
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `poster`
--

-- --------------------------------------------------------

--
-- Table structure for table `header_key`
--

CREATE TABLE IF NOT EXISTS `header_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_type` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `header_key`
--

INSERT INTO `header_key` (`id`, `header_type`) VALUES
(1, 'Accept'),
(2, 'Accept-Language'),
(3, 'Accept-Encoding'),
(4, 'Cache-Control'),
(5, 'Cookie'),
(6, 'Connection'),
(7, 'Content-Length'),
(8, 'Content-Type'),
(9, 'From'),
(10, 'Host'),
(11, 'If-Match'),
(12, 'If-Modified-Since'),
(13, 'If-None-Match'),
(14, 'If-Range'),
(15, 'If-Unmodified-Since'),
(16, 'Pragma'),
(17, 'User-Agent');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE IF NOT EXISTS `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(200) NOT NULL,
  `method` varchar(10) NOT NULL,
  `params` text NOT NULL,
  `response` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `keys`
--

CREATE TABLE IF NOT EXISTS `keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(200) NOT NULL,
  `domain` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `urls`
--

CREATE TABLE IF NOT EXISTS `urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `urls`
--
