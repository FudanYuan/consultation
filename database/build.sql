# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.35)
# Database: tp5
# Generation Time: 2017-08-15 13:41:06 +0000
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
# Dump of database consultation
# ------------------------------------------------------------

CREATE DATABASE if NOT EXISTS `consultation`;

USE `consultation`;

# Dump of table consultation_patient
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_patient`;

CREATE TABLE `consultation_patient` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '患者主键',
  `name` VARCHAR(100) NOT NULL COMMENT '患者姓名',
  `ID_number` VARCHAR(20) DEFAULT NULL COMMENT '身份证号',
  `gender` TINYINT NOT NULL COMMENT '性别：1->男；2->女',
  `age` TINYINT UNSIGNED DEFAULT NULL COMMENT '年龄',
  `occupation` VARCHAR(100) DEFAULT NULL COMMENT '职业',
  `phone` VARCHAR(20) NOT NULL COMMENT '联系方式',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `birthplace`VARCHAR(200) DEFAULT NULL COMMENT '出生地',
  `address` VARCHAR(200) DEFAULT NULL COMMENT '现住址',
  `work_unit` VARCHAR(200) DEFAULT NULL COMMENT '工作单位',
  `postcode` VARCHAR(20) DEFAULT NULL COMMENT '邮编',
  `height` SMALLINT UNSIGNED DEFAULT NULL COMMENT '身高(cm)',
  `weight` FLOAT DEFAULT NULL COMMENT '体重(kg)',
  `vision_left` VARCHAR(20) DEFAULT NULL COMMENT '左眼视力',
  `vision_right` VARCHAR(20) DEFAULT NULL COMMENT '右眼视力',
  `pressure_left` VARCHAR(20) DEFAULT NULL COMMENT '左眼眼压',
  `pressure_right` VARCHAR(20) DEFAULT NULL COMMENT '右眼眼压',
  `exam_img` VARCHAR(200) DEFAULT '' COMMENT '辅助检查图地址',
  `exam_img_origin` VARCHAR(200) DEFAULT '' COMMENT '辅助检查图名称',
  `eye_photo_left` VARCHAR(200) DEFAULT '' COMMENT '左眼照图片地址',
  `eye_photo_left_origin` VARCHAR(200) DEFAULT '' COMMENT '左眼照图片名称',
  `eye_photo_right` VARCHAR(200) DEFAULT '' COMMENT '右眼照图片地址',
  `eye_photo_right_origin` VARCHAR(200) DEFAULT '' COMMENT '左眼照图片名称',
  `ill_type` TINYINT DEFAULT NULL COMMENT '眼病类别：1眼表 2眼前节 3眼底 4视光 5其他',
  `other_ill_type`  VARCHAR(500) DEFAULT NULL COMMENT '其他眼病类型',
  `ill_state` TEXT DEFAULT NULL COMMENT '病情描述',
  `diagnose_state` TEXT DEFAULT NULL COMMENT '诊疗情况',
  `files_path` VARCHAR(200) DEFAULT NULL COMMENT '附属文件地址',
  `files_path_origin` VARCHAR(200) DEFAULT NULL COMMENT '附属文件名称',
  `in_hospital_time` int(11) DEFAULT NULL COMMENT '入院时间',
  `narrator` VARCHAR(100) DEFAULT NULL COMMENT '叙述者',
  `main_narrate` TEXT DEFAULT NULL COMMENT '主诉',
  `present_ill_history` TEXT DEFAULT NULL COMMENT '现病史',
  `past_history` TEXT DEFAULT NULL COMMENT '既往史',
  `system_retrospect` TEXT DEFAULT NULL COMMENT '系统回顾',
  `personal_history` TEXT DEFAULT NULL COMMENT '个人史',
  `physical_exam_record` TEXT DEFAULT NULL COMMENT '体检史',
  `status` tinyint(4) DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_patient` (`name`, `ID_number`, `gender`, `age`, `occupation`, `height`, `weight`, `phone`, `birthplace`, `address`, `work_unit`, `postcode`, `ill_type`, `ill_state`, `vision_left`, `vision_right`, `pressure_left`, `pressure_right`, `eye_photo_left`, `eye_photo_left_origin`, `eye_photo_right`, `eye_photo_right_origin`, `diagnose_state`, `files_path`, `files_path_origin`, `in_hospital_time`, `narrator`, `main_narrate`, `present_ill_history`, `past_history`, `system_retrospect`, `personal_history`, `physical_exam_record`, `status`, `create_time`, `update_time`) VALUES
('患者1', '1122', 1, 21, '学生', 178, 62.3, '15116136472', '山东泰安', '湖南长沙', '中南大学', '471500', 1, '放假啊就发了叫拉风姜老师分来发链接啦放假啦垃圾分类叫啦放假啦安拉发了金姐分辣椒粉阿娇弗朗加利飞机爱啦放假啊就发酒疯啦减肥据了解 啊就立即发酵法律界；啊；飞机阿娇发啦； 键；发啊放假啊；发；键啊放假啊；键发福；阿娇发；安静发；阿娇发；安静；阿娇发；键；安抚啊啦键啊发了；', '5.0', '5.0', '130', '120', '', '', '', '', '发福吗金姐分啊；发酵法；库放假啊； 发酵法啊放假我发就发放假阿娇放假啊；飞机；安静发酵法；安静阿肌肤了；阿肌肤； 安静； ', '', '', 1212191291, '放假啊', '发发', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL);


# Dump of table consultation_hospital
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_hospital`;

CREATE TABLE `consultation_hospital` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(100) DEFAULT NULL COMMENT '名称',
  `master` VARCHAR(50) DEFAULT NULL COMMENT '院长',
  `logo` VARCHAR(200) DEFAULT '' COMMENT '医院logo',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '联系方式',
  `url` VARCHAR(200) DEFAULT NULL COMMENT '网址',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `address` VARCHAR(200) DEFAULT NULL COMMENT '地址',
  `postcode` VARCHAR(20) DEFAULT NULL COMMENT '邮编',
  `type` VARCHAR(100) DEFAULT NULL COMMENT '医院类型',
  `level` VARCHAR(100) DEFAULT NULL COMMENT '医院等级',
  `info` TEXT DEFAULT NULL COMMENT '详细信息',
  `honor` TEXT DEFAULT NULL COMMENT '荣誉奖项',
  `role` TINYINT NOT NULL COMMENT '医院角色：1->可会诊医院; 2->不可会诊医院',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `consultation_hospital` (`name`, `master`, `phone`, `url`, `email`, `address`,  `postcode`, `type`, `level`, `info`, `honor`, `role`, `status`, `create_time`, `update_time`) VALUES
('中南大学湘雅二医院', '中南大学湘雅二医院院长', '0731-12345678', 'http://www.baidu.com', '123445@csd.com', '湖南省长沙市天心区', '471500', '综合医院', '三甲', '暂无详细信息', '暂无详细信息', 1, 1, 1503037656, NULL),
('XX医院', 'XX医院院长', '0731-12345628', 'http://www.baidu.com', '123445@csd.com', '湖南省长沙市芙蓉区', '471500', '眼科医院', '一级', '暂无详细信息', '暂无详细信息', 2, 1, 1503037656, NULL);


# Dump of table consultation_office
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_office`;

CREATE TABLE `consultation_office` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(200) DEFAULT NULL COMMENT '科室名称',
  `describe` TEXT DEFAULT NULL COMMENT '科室描述',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_office` (`name`, `describe`, `status`, `create_time`, `update_time`) VALUES
('眼科', '', 1, 1503037656, NULL);


# Dump of table consultation_doctor_patient
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_doctor_patient`;

CREATE TABLE `consultation_doctor_patient` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `doctor_id` INT DEFAULT NULL COMMENT '医生id,外键',
  `patient_id` INT DEFAULT NULL COMMENT '患者id,外键',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (doctor_id) REFERENCES consultation_doctor(id),
  FOREIGN KEY (patient_id) REFERENCES consultation_patient(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table consultation_hospital_office
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_hospital_office`;

CREATE TABLE `consultation_hospital_office` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `hospital_id` INT DEFAULT NULL COMMENT '医院id,外键',
  `office_id` INT DEFAULT NULL COMMENT '科室id,外键',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (hospital_id) REFERENCES consultation_hospital(id),
  FOREIGN KEY (office_id) REFERENCES consultation_office(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_hospital_office` (`hospital_id`, `office_id`, `status`, `create_time`, `update_time`) VALUES
(1, 1, 1, 1503037656, NULL),
(2, 1, 1, 1503037656, NULL);

# Dump of table consultation_doctor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_doctor`;

CREATE TABLE `consultation_doctor` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `hospital_office_id` INT DEFAULT NULL COMMENT '科室id,外键',
  `name` VARCHAR(100) DEFAULT NULL COMMENT '姓名',
  `photo` VARCHAR(200) DEFAULT '' COMMENT '照片',
  `gender` TINYINT NOT NULL COMMENT '性别：1->男；2->女',
  `age` TINYINT UNSIGNED DEFAULT NULL COMMENT '年龄',
  `position` VARCHAR(100) DEFAULT NULL COMMENT '职称',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '手机号',
  `email` VARCHAR(20) DEFAULT NULL COMMENT '邮箱',
  `address` VARCHAR(200) DEFAULT NULL COMMENT '地址',
  `postcode` VARCHAR(20) DEFAULT NULL COMMENT '邮编',
  `info` TEXT DEFAULT NULL COMMENT '详细信息',
  `honor` TEXT DEFAULT NULL COMMENT '荣誉奖项',
  `remark` VARCHAR(1000) DEFAULT NULL COMMENT '备注',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭；3->已注册',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hospital_office_id`) REFERENCES consultation_hospital_office(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_doctor` (`hospital_office_id`, `name`, `photo`, `gender`, `age`, `position`, `phone`, `remark`, `address`, `postcode`, `info`, `honor`, `status`, `create_time`, `update_time`) VALUES
(1, 'Reply1', '', 1, 31, '博导', '13623614251', '暂无', '湖南省长沙市天心区', '417500', '暂无详细信息', '暂无详细信息', 3, 1503037656, NULL),
(1, 'Reply2', '', 1, 31, '博导', '13623614251', '暂无', '湖南省长沙市天心区', '417500', '暂无详细信息', '暂无详细信息', 3, 1503037656, NULL),
(2, 'Apply1', '', 2, 21, '普通医师', '13623614251', '暂无', '湖南省长沙市芙蓉区', '417500', '暂无详细信息', '暂无详细信息', 3, 1503037656, NULL),
(2, 'Apply2', '', 2, 21, '普通医师', '13623614251', '暂无', '湖南省长沙市芙蓉区', '417500', '暂无详细信息', '暂无详细信息', 3, 1503037656, NULL);


# Dump of table consultation_apply
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_apply`;

CREATE TABLE `consultation_apply` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `patient_id` INT DEFAULT NULL COMMENT '患者id,外键',
  `source_user_id` INT DEFAULT NULL COMMENT '送诊用户id,外键',
  `apply_type` TINYINT DEFAULT NULL COMMENT '会诊类型：1->正常会诊；2->紧急会诊',
  `is_definite_purpose` TINYINT DEFAULT NULL COMMENT '是否明确意向：0->不明确；1->明确',
  `consultation_goal` TEXT DEFAULT NULL COMMENT '会诊目的',
  `apply_project` TINYINT DEFAULT NULL COMMENT '申请会诊项目:1->咨询;2->住院;3->手术;4->其他',
  `other_apply_project` VARCHAR(1000) DEFAULT NULL COMMENT '其他申请项目',
  `apply_date` INT(11) DEFAULT NULL COMMENT '申请会诊日期',
  `consultation_result` TEXT DEFAULT '' COMMENT '会诊结果',
  `is_green_channel` TINYINT DEFAULT 0 COMMENT '会诊类型：0->否；1->是',
  `price` FLOAT DEFAULT 0 COMMENT '收费价格',
  `is_charge` TINYINT DEFAULT 0 COMMENT '是否缴费：0->无；1->已缴费',
  `status` TINYINT DEFAULT NULL COMMENT '状态：0 关闭 1 未会诊 2 已会但需病患详细信息 3 得出结果 4 未得出结果 ',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (source_user_id) REFERENCES consultation_user_admin(id),
  FOREIGN KEY (patient_id) REFERENCES consultation_patient(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_apply` (`patient_id`, `source_user_id`, `apply_type`, `is_definite_purpose`, `consultation_goal`, `apply_project`, `other_apply_project`, `apply_date`, `consultation_result`, `price`, `is_charge`, `status`, `create_time`, `update_time`) VALUES
(1, 3, 2, 1, '发不过真的是个复杂的是官方代购', 2, '', 1510372800, '但是发达数电分复旦飒风', 1200, 0, 3, 1510378491, 1510372800),
(1, 4, 2, 1, '浮动工资法国政府多个', 3, '', 1510372800, NULL, 0, 0, 1, 1510379139, NULL);

# Dump of table consultation_apply_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_apply_user`;

CREATE TABLE `consultation_apply_user` (
  `apply_id` INT DEFAULT NULL COMMENT '会诊申请id,外键',
  `target_user_id` INT DEFAULT NULL COMMENT '会诊用户id,外键',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`apply_id`, `target_user_id`),
  FOREIGN KEY (target_user_id) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `consultation_apply_user` (`apply_id`, `target_user_id`, `status`, `create_time`, `update_time`) VALUES
(1, 1, 1, 1510378491, 1510372800),
(2, 1, 1, 1510379139, NULL);


# Dump of table consultation_chat
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_chat`;

CREATE TABLE `consultation_chat` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `apply_id` INT DEFAULT NULL COMMENT '申请id,外键',
  `source_user_id` INT DEFAULT NULL COMMENT '发送方医生id,外键',
  `target_user_id` INT DEFAULT NULL COMMENT '接收方医生id,外键, 私聊用此字段',
  `type` TINYINT DEFAULT NULL COMMENT '信息格式',
  `content` TEXT DEFAULT '' COMMENT '信息内容',
  `content_origin` TEXT DEFAULT '' COMMENT '信息附件地址',
  `status` TINYINT DEFAULT NULL COMMENT '状态：0->未读；1->已读；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`apply_id`) REFERENCES consultation_apply(id),
  FOREIGN KEY (`source_user_id`) REFERENCES consultation_user_admin(id),
  FOREIGN KEY (`target_user_id`) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Dump of table consultation_inform
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_inform`;

CREATE TABLE `consultation_inform` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` TINYINT DEFAULT NULL COMMENT '通知类型，1->提醒类；2->公告类；3->警告类；4->错误类；5->其他',
  `target_user_id` INT unsigned NOT NULL  COMMENT '外键，接收用户',
  `title` VARCHAR(200) DEFAULT NULL COMMENT '通知标题',
  `content` text DEFAULT NULL COMMENT '通知内容',
  `url` VARCHAR(200) DEFAULT '' COMMENT '跳转链接，放在导航栏直接跳转至目的页面',
  `operation` VARCHAR(50) DEFAULT NULL COMMENT '操作',
  `priority` TINYINT DEFAULT NULL COMMENT '优先级，1最高',
  `status` TINYINT DEFAULT NULL COMMENT '是否处理：0->未处理；1->已处理, 2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (target_user_id) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table consultation_role_admin
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_role_admin`;

CREATE TABLE `consultation_role_admin` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(50) DEFAULT NULL COMMENT '角色名',
  `remark` VARCHAR(50) DEFAULT NULL COMMENT '备注',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_role_admin` (`name`, `remark`, `status`, `create_time`, `update_time`) VALUES
('开发者', '开发者', 1, 1510369829, 1510369829),
('admin', '管理员', 1, 1503037656, 1510378203);


# Dump of table consultation_user_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_user_admin`;

CREATE TABLE `consultation_user_admin` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `doctor_id` INT unsigned NOT NULL COMMENT  '医生id，外键',
  `logo` VARCHAR(100) DEFAULT '' COMMENT '账户头像',
  `username` VARCHAR(50) DEFAULT NULL COMMENT '账号->手机号码',
  `pass` VARCHAR(50) DEFAULT NULL COMMENT '密码',
  `role_id` TINYINT DEFAULT NULL COMMENT '角色',
  `remark` VARCHAR(50) DEFAULT NULL COMMENT '备注',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭；3->禁用',
  `login_time` INT DEFAULT NULL COMMENT '登陆时间',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (doctor_id) REFERENCES consultation_doctor(id),
  FOREIGN KEY (role_id) REFERENCES consultation_role_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `consultation_user_admin` (`doctor_id`, `username`, `pass`, `role_id`, `remark`, `status`, `login_time`, `create_time`, `update_time`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 1, '附二医院管理员', 1, 1508462059, 1503213456, 1508462059),
(2, 'admin2', '21232f297a57a5a743894a0e4a801fc3', 1, '附二医院管理员', 1, 1508462059, 1503213456, 1508462059),
(3, 'role', '29a7e96467b69a9f5a93332e29e9b0de', 2, '县级医院管理员', 1, NULL, 1510115628, 1510115628),
(4, 'test', '29a7e96467b69a9f5a93332e29e9b0de', 2, '县级医院管理员', 1, NULL, 1510115628, 1510115628);



# Dump of table consultation_action_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_action_admin`;

CREATE TABLE `consultation_action_admin` (
  `id` INT unsigned AUTO_INCREMENT NOT NULL COMMENT '主键',
  `name` VARCHAR(50) DEFAULT NULL COMMENT '操作名称',
  `tag` VARCHAR(50) DEFAULT NULL COMMENT '备注',
  `pid` VARCHAR(4) DEFAULT NULL COMMENT '父节点',
  `pids` VARCHAR(10) DEFAULT NULL COMMENT '父子节点关系',
  `level` TINYINT DEFAULT NULL COMMENT '层次',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table consultation_role_action_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_role_action_admin`;

CREATE TABLE `consultation_role_action_admin` (
  `role_id` INT unsigned NOT NULL COMMENT '外键,角色id',
  `action_id` INT DEFAULT NULL COMMENT '外键,操作id',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`role_id`, `action_id`),
  FOREIGN KEY (role_id) REFERENCES consultation_role_admin(id),
  FOREIGN KEY (action_id) REFERENCES consultation_action_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Dump of table consultation_operation_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_operation_log`;

CREATE TABLE `consultation_operation_log` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` VARCHAR(100) DEFAULT NULL COMMENT '外键，用户id',
  `IP` VARCHAR(20) DEFAULT NULL COMMENT 'IP地址',
  `section` VARCHAR(100) DEFAULT NULL COMMENT '操作板块',
  `action_descr` VARCHAR(100) DEFAULT NULL COMMENT '操作详情',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `create_time` INT DEFAULT NULL COMMENT '创建时间',
  `update_time` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (user_id) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
