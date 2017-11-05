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
  `height` SMALLINT UNSIGNED DEFAULT NULL COMMENT '身高(cm)',
  `weight` FLOAT DEFAULT NULL COMMENT '体重(kg)',
  `phone` VARCHAR(20) NOT NULL COMMENT '联系方式',
  `birthplace`VARCHAR(200) DEFAULT NULL COMMENT '出生地',
  `address` VARCHAR(200) DEFAULT NULL COMMENT '现住址',
  `workunit` VARCHAR(200) DEFAULT NULL COMMENT '工作单位',
  `postcode` VARCHAR(20) DEFAULT NULL COMMENT '邮编',
  `inhospital_time` int(11) DEFAULT NULL COMMENT '入院时间',
  `recordtime` int(11) DEFAULT NULL COMMENT '记录时间',
  `narrator` VARCHAR(100) DEFAULT NULL COMMENT '叙述者',
  `main_narrate` TEXT DEFAULT NULL COMMENT '主诉',
  `presentill_history` TEXT DEFAULT NULL COMMENT '现病史',
  `past_history` TEXT DEFAULT NULL COMMENT '既往史',
  `system_retrospect` TEXT DEFAULT NULL COMMENT '系统回顾',
  `personal_history` TEXT DEFAULT NULL COMMENT '个人史',
  `physical_examrecord` TEXT DEFAULT NULL COMMENT '体检史',
  `status` tinyint(4) DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table consultation_hospital
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_hospital`;

CREATE TABLE `consultation_hospital` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(100) DEFAULT NULL COMMENT '名称',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '联系方式',
  `url` VARCHAR(200) DEFAULT NULL COMMENT '网址',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱',
  `address` VARCHAR(200) DEFAULT NULL COMMENT '地址',
  `role` TINYINT NOT NULL COMMENT '医院角色：1->可会诊医院; 2->不可会诊医院',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `consultation_hospital` (`name`, `phone`, `url`, `email`, `address`,  `role`, `status`, `createtime`, `updatetime`) VALUES
('眼科医联体远程诊疗平台', '0731-12345678', 'http://www.baidu.com', '123445@csd.com', '湖南省长沙市', 1, 1, 1503037656, NULL);


# Dump of table consultation_office
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_office`;

CREATE TABLE `consultation_office` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(200) DEFAULT NULL COMMENT '科室名称',
  `descr` TEXT DEFAULT NULL COMMENT '科室描述',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_office` (`name`, `descr`, `status`, `createtime`, `updatetime`) VALUES
('眼科', '', 1, 1503037656, NULL);


# Dump of table consultation_hospotial_office
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_hospotial_office`;

CREATE TABLE `consultation_hospotial_office` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `hospital_id` INT DEFAULT NULL COMMENT '医院id,外键',
  `office_id` INT DEFAULT NULL COMMENT '科室id,外键',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (hospital_id) REFERENCES consultation_hospital(id),
  FOREIGN KEY (office_id) REFERENCES consultation_office(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_hospotial_office` (`hospital_id`, `office_id`, `status`, `createtime`, `updatetime`) VALUES
(1, 1, 1, 1503037656, NULL);


# Dump of table consultation_doctor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_doctor`;

CREATE TABLE `consultation_doctor` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `office_id` INT DEFAULT NULL COMMENT '科室id,外键',
  `name` VARCHAR(100) DEFAULT NULL COMMENT '姓名',
  `post` VARCHAR(100) DEFAULT NULL COMMENT '职称',
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '手机号',
  `address` VARCHAR(200) DEFAULT NULL COMMENT '地址',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (office_id) REFERENCES consultation_hospotial_office(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_doctor` (`office_id`, `name`, `post`, `phone`, `address`, `status`, `createtime`, `updatetime`) VALUES
(1, 'Smith', '博导', '13623614251', '湖南省长沙市', 1, 1503037656, NULL);


# Dump of table consultation_apply
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_apply`;

CREATE TABLE `consultation_apply` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `patient_id` INT DEFAULT NULL COMMENT '患者id,外键',
  `delivery_user_id` INT DEFAULT NULL COMMENT '送诊用户,外键',
  `apply_type` TINYINT DEFAULT NULL COMMENT '会诊类型：1->正常会诊；2->紧急会诊',
  `illness_state` TEXT DEFAULT NULL COMMENT '病情',
  `eyesill_type` TINYINT DEFAULT NULL COMMENT '眼病类别：1眼表 2眼前节 3眼底 4视光 5其他',
  `vision_left` VARCHAR(20) DEFAULT NULL COMMENT '左眼视力',
  `vision_right` VARCHAR(20) DEFAULT NULL COMMENT '右眼视力',
  `pressure_left` VARCHAR(20) DEFAULT NULL COMMENT '左眼眼压',
  `pressure_right` VARCHAR(20) DEFAULT NULL COMMENT '右眼眼压',
  `eye_photo_left` VARCHAR(200) DEFAULT NULL COMMENT '左眼照图片地址',
  `eye_photo_right` VARCHAR(200) DEFAULT NULL COMMENT '右眼照图片地址',
  `diagnose_state` TEXT DEFAULT NULL COMMENT '诊疗情况',
  `consultation_goal` TEXT DEFAULT NULL COMMENT '会诊目的',
  `apply_project` TINYINT DEFAULT NULL COMMENT '申请会诊项目:1->咨询;2->住院;3->手术;4->其他',
  `other_apply` VARCHAR(1000) DEFAULT NULL COMMENT '其他申请意愿',
  `is_definte_purpose` TINYINT DEFAULT NULL COMMENT '是否明确意向：0->不明确；1->明确',
  `apply_doctor_ids` VARCHAR(100) DEFAULT NULL COMMENT '诊断医生id，可多个',
  `consultation_doctor` VARCHAR(200) DEFAULT NULL COMMENT '会诊医生id，可多个',
  `consultation_office` VARCHAR(200) DEFAULT NULL COMMENT '会诊科室id，可多个',
  `apply_date` INT(11) DEFAULT NULL COMMENT '申请会诊日期',
  `consultation_result` TEXT DEFAULT NULL COMMENT '会诊结果',
  `price` FLOAT DEFAULT NULL COMMENT '收费价格',
  `is_charge` TINYINT DEFAULT NULL COMMENT '是否缴费：0->无；1->已缴费',
  `other_apply_result` VARCHAR(50) DEFAULT NULL COMMENT '其他申请结果',
  `status` TINYINT DEFAULT NULL COMMENT '状态：0 关闭 1 未会诊 2 已会但需病患详细信息 3 得出结果 4 未得出结果 ',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (patient_id) REFERENCES consultation_patient(id),
  FOREIGN KEY (delivery_user_id) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table consultation_communication
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_communication`;

CREATE TABLE `consultation_communication` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `apply_id` INT DEFAULT NULL COMMENT '申请id,外键',
  `source_user_id` INT DEFAULT NULL COMMENT '发送方医生id,外键',
  `target_user_id` INT DEFAULT NULL COMMENT '接收方医生id,外键',
  `words_info` TEXT DEFAULT NULL COMMENT '文字信息',
  `files_info` TEXT DEFAULT NULL COMMENT '文件信息',
  `time` INT DEFAULT NULL COMMENT '时间',
  `status` TINYINT DEFAULT NULL COMMENT '状态：0->未读；1->已读；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`apply_id`) REFERENCES consultation_apply(id),
  FOREIGN KEY (`source_user_id`) REFERENCES consultation_user_admin(id),
  FOREIGN KEY (`target_user_id`) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table consultation_role_admin
# ------------------------------------------------------------
DROP TABLE IF EXISTS `consultation_role_admin`;

CREATE TABLE `consultation_role_admin` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(50) DEFAULT NULL COMMENT '角色名',
  `remark` VARCHAR(50) DEFAULT NULL COMMENT '备注',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `consultation_role_admin` (`name`, `remark`, `status`, `createtime`, `updatetime`) VALUES
('admin', '管理员', 1, 1503037656, NULL),
('edit', '普通管理员', 1, 1503037656, NULL);

# Dump of table consultation_user_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_user_admin`;

CREATE TABLE `consultation_user_admin` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `doctor_id` INT unsigned NOT NULL COMMENT  '医生id，外键',
  `username` VARCHAR(50) DEFAULT NULL COMMENT '账号->手机号码',
  `pass` VARCHAR(50) DEFAULT NULL COMMENT '密码',
  `roleid` TINYINT DEFAULT NULL COMMENT '角色',
  `remark` VARCHAR(50) DEFAULT NULL COMMENT '备注',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭；3->禁用',
  `logintime` INT DEFAULT NULL COMMENT '登陆时间',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (doctor_id) REFERENCES consultation_doctor(id),
  FOREIGN KEY (roleid) REFERENCES consultation_role_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `consultation_user_admin` (`doctor_id`, `username`, `pass`, `roleid`, `remark`, `status`, `logintime`, `createtime`, `updatetime`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 1, '超级管理员', 1, 1508462059, 1503213456, 1508462059);


# Dump of table consultation_action_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_action_admin`;

CREATE TABLE `consultation_action_admin` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` VARCHAR(50) DEFAULT NULL COMMENT '操作名称',
  `tag` VARCHAR(50) DEFAULT NULL COMMENT '备注',
  `pid` VARCHAR(4) DEFAULT NULL COMMENT '父节点',
  `pids` VARCHAR(10) DEFAULT NULL COMMENT '父子节点关系',
  `level` TINYINT DEFAULT NULL COMMENT '层次',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


# Dump of table consultation_role_action_admin
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consultation_role_action_admin`;

CREATE TABLE `consultation_role_action_admin` (
  `roleid` INT unsigned NOT NULL COMMENT '外键,角色id',
  `actionid` INT DEFAULT NULL COMMENT '外键,操作id',
  `status` TINYINT DEFAULT NULL COMMENT '状态：1->启用；2->关闭',
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`roleid`, `actionid`),
  FOREIGN KEY (roleid) REFERENCES consultation_role_admin(id),
  FOREIGN KEY (actionid) REFERENCES consultation_action_admin(id)
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
  `createtime` INT DEFAULT NULL COMMENT '创建时间',
  `updatetime` INT DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  FOREIGN KEY (user_id) REFERENCES consultation_user_admin(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
