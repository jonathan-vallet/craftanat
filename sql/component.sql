/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50523
Source Host           : localhost:3306
Source Database       : osteo

Target Server Type    : MYSQL
Target Server Version : 50523
File Encoding         : 65001

Date: 2014-05-08 18:54:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for component
-- ----------------------------
DROP TABLE IF EXISTS `component`;
CREATE TABLE `component` (
  `component_id` int(11) NOT NULL AUTO_INCREMENT,
  `component_name` varchar(255) NOT NULL,
  PRIMARY KEY (`component_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of component
-- ----------------------------
INSERT INTO `component` VALUES ('1', 'Composant osseux');
INSERT INTO `component` VALUES ('2', 'Composant ligamentaire');
INSERT INTO `component` VALUES ('3', 'Composant artériel');
INSERT INTO `component` VALUES ('4', 'Composant veineux');
INSERT INTO `component` VALUES ('5', 'Composant musculaire');
INSERT INTO `component` VALUES ('6', 'Composant nerveux');
