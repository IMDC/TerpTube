-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 19, 2012 at 02:54 PM
-- Server version: 5.1.61-log
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `terptube_mockup`
--

-- --------------------------------------------------------

--
-- Table structure for table `video_caption`
--

CREATE TABLE IF NOT EXISTS `video_caption` (
  `caption_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `start_time` decimal(10,0) NOT NULL,
  `end_time` int(11) NOT NULL,
  `text` varchar(250) NOT NULL,
  PRIMARY KEY (`caption_id`),
  KEY `source_id` (`source_id`),
  KEY `source_id_2` (`source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=905 ;

--
-- Dumping data for table `video_caption`
--

INSERT INTO `video_caption` (`caption_id`, `source_id`, `start_time`, `end_time`, `text`) VALUES
(897, 1, '2', 13, '<b>Hello and Welcome to IDI at OCAD</b>'),
(898, 1, '15', 26, '<b>We are showing inclusive design research</b>'),
(899, 1, '27', 36, '<b>We are showing our software</b>'),
(900, 1, '37', 47, '<b>for video accessiblity for Deaf signers online</b>'),
(901, 1, '50', 60, '<b>This uses visual interface for navigation which is accessible for       Deaf signers.</b>'),
(902, 1, '62', 76, '<b>If any questions please ask us!</b>'),
(903, 1, '76', 88, '<b>My name is Ellen Hibbard, PhD Communications and Culture</b>'),
(904, 1, '88', 93, '<b>with focus in Technology. Thank you!!</b>');

-- --------------------------------------------------------

--
-- Table structure for table `video_comment`
--

CREATE TABLE IF NOT EXISTS `video_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `author` varchar(50) NOT NULL,
  `text_comments` varchar(200) NOT NULL,
  `comment_start_time` decimal(10,0) NOT NULL,
  `comment_end_time` decimal(10,0) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`comment_id`),
  KEY `source_id` (`source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=332 ;

--
-- Dumping data for table `video_comment`
--

INSERT INTO `video_comment` (`comment_id`, `source_id`, `parent_id`, `author`, `text_comments`, `comment_start_time`, `comment_end_time`, `date`) VALUES
(330, 1, 0, 'joe', 'huuuurrrrrrrr', '20', '23', '2012-09-07 15:10:32'),
(331, 1, 0, 'joe', 'jrjssghk', '9', '22', '2012-09-17 12:50:31');

-- --------------------------------------------------------

--
-- Table structure for table `video_signlink`
--

CREATE TABLE IF NOT EXISTS `video_signlink` (
  `signlink_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `start_time` decimal(10,0) NOT NULL,
  `end_time` decimal(10,0) NOT NULL,
  `frame_time` decimal(10,0) NOT NULL,
  `url` varchar(300) NOT NULL,
  `label` varchar(300) NOT NULL,
  PRIMARY KEY (`signlink_id`),
  KEY `source_id` (`source_id`),
  KEY `source_id_2` (`source_id`),
  KEY `source_id_3` (`source_id`),
  KEY `source_id_4` (`source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=596 ;

--
-- Dumping data for table `video_signlink`
--

INSERT INTO `video_signlink` (`signlink_id`, `source_id`, `start_time`, `end_time`, `frame_time`, `url`, `label`) VALUES
(593, 1, '5', '7', '6839', 'http://www.ocadu.ca', 'OCAD'),
(594, 1, '26', '39', '26381', 'http://www.ryerson.ca', ' Ryerson'),
(595, 1, '12', '15', '78529', 'http://ryerson.ca/clt', 'LAB');

-- --------------------------------------------------------

--
-- Table structure for table `video_source`
--

CREATE TABLE IF NOT EXISTS `video_source` (
  `source_id` int(11) NOT NULL,
  `title` varchar(300) NOT NULL,
  `duration` decimal(10,0) NOT NULL,
  `comment` varchar(500) NOT NULL,
  PRIMARY KEY (`source_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `video_source`
--

INSERT INTO `video_source` (`source_id`, `title`, `duration`, `comment`) VALUES
(2, 'Tessa_Language_Background.mp4', '93867', 'comment video'),
(1, 'Tessa_Language_Background.mp4', '93867', 'comment video');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
