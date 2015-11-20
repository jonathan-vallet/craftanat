/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50523
Source Host           : localhost:3306
Source Database       : osteo

Target Server Type    : MYSQL
Target Server Version : 50523
File Encoding         : 65001

Date: 2014-05-08 18:26:12
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for structure
-- ----------------------------
DROP TABLE IF EXISTS `structure`;
CREATE TABLE `structure` (
  `structure_id` int(11) NOT NULL AUTO_INCREMENT,
  `structure_name` varchar(255) NOT NULL,
  `structure_order` mediumint(9) NOT NULL,
  `structure_question_number` tinyint(2) NOT NULL DEFAULT '1',
  `structure_image` varchar(255) DEFAULT NULL,
  `structure_category_id` int(11) NOT NULL,
  PRIMARY KEY (`structure_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for structure_category
-- ----------------------------
DROP TABLE IF EXISTS `structure_category`;
CREATE TABLE `structure_category` (
  `structure_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `structure_category_name` varchar(255) NOT NULL,
  `structure_category_component_name` varchar(255) NOT NULL,
  PRIMARY KEY (`structure_category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of structure_category
-- ----------------------------
INSERT INTO `structure_category` VALUES ('1', 'Os', 'Composant osseux');
INSERT INTO `structure_category` VALUES ('2', 'Ligament', 'Composant ligamentaire');
INSERT INTO `structure_category` VALUES ('3', 'Nerf', 'Composant nerveux');
INSERT INTO `structure_category` VALUES ('4', 'Muscle', 'Composant musculaire');
INSERT INTO `structure_category` VALUES ('5', 'Veine', 'Composant veineux');
INSERT INTO `structure_category` VALUES ('6', 'Artère', 'Composant artériel');

-- ----------------------------
-- Table structure for structure_required_structure
-- ----------------------------
DROP TABLE IF EXISTS `structure_required_structure`;
CREATE TABLE `structure_required_structure` (
  `structure_required_structure_id` int(11) NOT NULL AUTO_INCREMENT,
  `structure_id` int(11) NOT NULL,
  `required_structure_id` int(11) NOT NULL,
  PRIMARY KEY (`structure_required_structure_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
