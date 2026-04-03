/*
 Navicat Premium Dump SQL

 Source Server         : 本地
 Source Server Type    : MySQL
 Source Server Version : 80044 (8.0.44)
 Source Host           : localhost:3306
 Source Schema         : m91jav1

 Target Server Type    : MySQL
 Target Server Version : 80044 (8.0.44)
 File Encoding         : 65001

 Date: 03/04/2026 11:57:12
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sq_actor
-- ----------------------------
DROP TABLE IF EXISTS `sq_actor`;
CREATE TABLE `sq_actor`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `hash_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name_cn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `name_ja` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `name_en` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `avatar` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `mv_count` int NOT NULL DEFAULT 0 COMMENT '作品数量',
  `hot_value` int NOT NULL DEFAULT 0 COMMENT '热度值',
  `desc_zh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `desc_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `desc_jp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `top_show` tinyint NULL DEFAULT 0,
  `top_sort` tinyint NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `name_initials` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `actor_tag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '演员详情标签',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `hash_id`(`hash_id` ASC) USING BTREE,
  INDEX `name_in`(`name_initials` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6923 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'AV女友表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_actor_collect
-- ----------------------------
DROP TABLE IF EXISTS `sq_actor_collect`;
CREATE TABLE `sq_actor_collect`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_actor_id` int NOT NULL DEFAULT 0,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `alias_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `jp_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `is_hot` tinyint NULL DEFAULT 0,
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `sort` int NOT NULL DEFAULT 0,
  `num` int NOT NULL DEFAULT 0,
  `birthday` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `age` tinyint NULL DEFAULT 0,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `body` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `height` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `relate_tags` json NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `source_actor_id`(`source_actor_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1612 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'AV女友采集表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sq_actor_favorites
-- ----------------------------
DROP TABLE IF EXISTS `sq_actor_favorites`;
CREATE TABLE `sq_actor_favorites`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT NULL,
  `related_id` int NULL DEFAULT NULL COMMENT '关联ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `actor_id`(`related_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19172 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '女优收藏表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_admin_log`;
CREATE TABLE `sq_admin_log`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '操作则账号',
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '操作动作',
  `ip` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '操作ip',
  `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '操作详情',
  `referrer` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '操作url来源',
  `context` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '操作详情',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `username`(`username` ASC) USING BTREE,
  INDEX `action`(`action` ASC) USING BTREE,
  INDEX `ip`(`ip` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10270036 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '后台全量操作日志' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sq_ads
-- ----------------------------
DROP TABLE IF EXISTS `sq_ads`;
CREATE TABLE `sq_ads`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '广告标题',
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '广告词',
  `img_url` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '图片地址',
  `url_config` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '广告跳转地址/QQ号/微信号',
  `position` smallint NOT NULL DEFAULT 1 COMMENT '广告位',
  `android_down_url` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `ios_down_url` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '广告类型 0：下载链接 1：跳转qq 2:跳转微信',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0-禁用，1-启用',
  `oauth_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '广告设备类型 0所有 1iOS 2 android',
  `mv_m3u8` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '视频m3u8',
  `channel` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '渠道',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `router` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '路由',
  `start_at` datetime NULL DEFAULT '2000-01-01 00:00:00' COMMENT '广告有效期',
  `end_at` datetime NULL DEFAULT '2099-01-01 00:00:00' COMMENT '广告有效期',
  `sort` int NULL DEFAULT 0 COMMENT '排序 越大越前',
  `product_type` int NULL DEFAULT 0 COMMENT '产品类型',
  `clicked` int NULL DEFAULT 0 COMMENT '点击量',
  `ads_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '广告编码',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `position`(`position` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 280 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '广告表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_ads_categories
-- ----------------------------
DROP TABLE IF EXISTS `sq_ads_categories`;
CREATE TABLE `sq_ads_categories`  (
  `aid` int NOT NULL,
  `cid` int NOT NULL,
  UNIQUE INDEX `typecho_ads_categories_aid_cid`(`aid` ASC, `cid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_advertiser
-- ----------------------------
DROP TABLE IF EXISTS `sq_advertiser`;
CREATE TABLE `sq_advertiser`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '类型',
  `start_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '开始时间',
  `end_at` datetime NULL DEFAULT '2099-12-12 00:00:01' COMMENT '结束时间',
  `link` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '链接',
  `desc` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `article_copywriting` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '文章文案',
  `ads_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '广告中心标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `link`(`link` ASC) USING BTREE,
  INDEX `desc`(`desc` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 176 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '广告主' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_app
-- ----------------------------
DROP TABLE IF EXISTS `sq_app`;
CREATE TABLE `sq_app`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键递增',
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT 'APP名称',
  `intro` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `thumb` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '图标',
  `category_id` int NOT NULL DEFAULT 0 COMMENT '类型ID',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序 越大越前',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '跳转链接',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `type` int NULL DEFAULT 0 COMMENT '产品类型 0:内部产品 2:外部产品',
  `start_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '开始时间',
  `end_at` datetime NULL DEFAULT '2099-12-12 00:00:01' COMMENT '结束时间',
  `click_num` int NULL DEFAULT 0 COMMENT '点击量',
  `ads_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '广告编码',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 189 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_app_category
-- ----------------------------
DROP TABLE IF EXISTS `sq_app_category`;
CREATE TABLE `sq_app_category`  (
  `id` int NOT NULL AUTO_INCREMENT COMMENT '主键递增',
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '类型标题',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序 越大越前',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_app_notice
-- ----------------------------
DROP TABLE IF EXISTS `sq_app_notice`;
CREATE TABLE `sq_app_notice`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `title` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '系统消息',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '内容',
  `position` int NULL DEFAULT 0,
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `thumb_width` int NULL DEFAULT NULL,
  `thumb_height` int NULL DEFAULT NULL,
  `advertiser_id` int NULL DEFAULT 0,
  `status` int NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `show_width` int NULL DEFAULT NULL,
  `show_height` int NULL DEFAULT NULL,
  `type` tinyint NULL DEFAULT NULL,
  `url_config` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `router` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_area_cn
-- ----------------------------
DROP TABLE IF EXISTS `sq_area_cn`;
CREATE TABLE `sq_area_cn`  (
  `id` int NOT NULL COMMENT 'ID',
  `areaname` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '栏目名',
  `parentid` int NULL DEFAULT NULL COMMENT '父栏目',
  `shortname` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `lng` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `lat` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `level` int NOT NULL COMMENT '1.省 2.市 3.区 4.镇',
  `position` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `sort` int UNSIGNED NULL DEFAULT 50 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parentid`(`parentid` ASC) USING BTREE,
  CONSTRAINT `sq_area_cn_ibfk_1` FOREIGN KEY (`parentid`) REFERENCES `sq_area_cn` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '省市区表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_area_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_area_log`;
CREATE TABLE `sq_area_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `url` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '检测域名',
  `ip` char(39) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '检测ip',
  `area` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `oauth_type` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `sick` int NOT NULL COMMENT '返回状态',
  `type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'login 打开app  av视频 xiao 小视频',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '检测时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18428789 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '域名检测日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_article
-- ----------------------------
DROP TABLE IF EXISTS `sq_article`;
CREATE TABLE `sq_article`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `title_jp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `title_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '封面',
  `content` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL COMMENT '内容',
  `content_jp` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL,
  `content_en` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL,
  `is_hot` int NOT NULL DEFAULT 0 COMMENT '是否热门',
  `thumb_show` int NOT NULL DEFAULT 0 COMMENT '封面显示样式',
  `thumb_width` int NOT NULL DEFAULT 0 COMMENT '封面宽度',
  `thumb_height` int NOT NULL DEFAULT 0 COMMENT '封面高度',
  `allow_comment` int NOT NULL DEFAULT 0 COMMENT '是否允许评论',
  `plate_id` int NOT NULL DEFAULT 0 COMMENT '板块ID',
  `is_ad` int NOT NULL DEFAULT 0 COMMENT '是否为广告',
  `is_show` int NOT NULL DEFAULT 0 COMMENT '是否显示',
  `is_finished` int NOT NULL DEFAULT 0 COMMENT '资源是否完成',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `hot_sort` int NULL DEFAULT 0 COMMENT '热门排序',
  `author_aff` int NULL DEFAULT 0 COMMENT '作者',
  `view_ct` int NULL DEFAULT 0 COMMENT '浏览数',
  `comment_ct` int NULL DEFAULT 0 COMMENT '评论数',
  `favorite_ct` int NULL DEFAULT 0 COMMENT '收藏数',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `type` int NULL DEFAULT 0 COMMENT '类型 0-普通文章 1-其他系统配置文章',
  `p_id` int NULL DEFAULT 0 COMMENT '原始记录ID',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `opacity` int NULL DEFAULT 3 COMMENT '透明度',
  `state` smallint NULL DEFAULT 1 COMMENT '状态(1=已发布|2=已通过|3=待审核|4=拒绝)',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `us_404` tinyint NOT NULL DEFAULT 0 COMMENT 'ip限制',
  `is_contribute` tinyint NOT NULL DEFAULT 0,
  `home_top` int UNSIGNED NULL DEFAULT 0 COMMENT '首页热门',
  `seo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'seo描述',
  `is_hide_title` int NOT NULL DEFAULT 0 COMMENT '0不隐藏标题1需要隐藏标题',
  `is_translate` tinyint NULL DEFAULT 1 COMMENT '是否需要翻译(1=需要，2=不需要)',
  `video_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '视频番号(有值时对应sq_mv的_id)',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `p_id`(`p_id` ASC) USING BTREE,
  INDEX `keywords`(`keywords` ASC) USING BTREE,
  INDEX `title`(`title` ASC) USING BTREE,
  INDEX `is_hot`(`is_hot` ASC) USING BTREE,
  INDEX `plate_id`(`plate_id` ASC) USING BTREE,
  INDEX `is_ad`(`is_ad` ASC) USING BTREE,
  INDEX `is_show`(`is_show` ASC) USING BTREE,
  INDEX `is_finished`(`is_finished` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `hot_sort`(`hot_sort` ASC) USING BTREE,
  INDEX `created_at`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 58340 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_article_ads
-- ----------------------------
DROP TABLE IF EXISTS `sq_article_ads`;
CREATE TABLE `sq_article_ads`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `show_detail` int NOT NULL DEFAULT 0 COMMENT '是否显示详情',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `advertiser_id` int NULL DEFAULT 0 COMMENT '广告主ID',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `article_id` int NULL DEFAULT 0 COMMENT '文章ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `show_detail`(`show_detail` ASC) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE,
  INDEX `advertiser_id`(`advertiser_id` ASC) USING BTREE,
  INDEX `article_id`(`article_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3408 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章广告' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_article_comment
-- ----------------------------
DROP TABLE IF EXISTS `sq_article_comment`;
CREATE TABLE `sq_article_comment`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT 0 COMMENT '用户uid',
  `article_id` int NOT NULL DEFAULT 0 COMMENT '内容ID',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '评论内容',
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '评论状态(1=待审核,2=通过,3=拒绝)',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `is_official` tinyint NULL DEFAULT 0 COMMENT '是否官方评论 0-否 1-是',
  `is_top` tinyint NULL DEFAULT 0 COMMENT '是否置顶 0-否 1-是',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_at`(`created_at` ASC) USING BTREE,
  INDEX `article_id`(`article_id` ASC) USING BTREE,
  INDEX `updated_at`(`updated_at` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 112 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_article_tags
-- ----------------------------
DROP TABLE IF EXISTS `sq_article_tags`;
CREATE TABLE `sq_article_tags`  (
  `tags_id` int NOT NULL COMMENT '标签id',
  `a_id` int NOT NULL COMMENT '文章id',
  INDEX `tags_id`(`tags_id` ASC) USING BTREE,
  INDEX `a_id`(`a_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_article_video
-- ----------------------------
DROP TABLE IF EXISTS `sq_article_video`;
CREATE TABLE `sq_article_video`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL DEFAULT 0 COMMENT '文章id',
  `thumb` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `thumb_width` int NOT NULL DEFAULT 0 COMMENT '封面宽度',
  `thumb_height` int NOT NULL DEFAULT 0 COMMENT '封面高度',
  `duration` int NOT NULL DEFAULT 0 COMMENT '视频时长',
  `mp4` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT 'mp4',
  `m3u8` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT 'm3u8',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `status` int NOT NULL DEFAULT 0 COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `article_id`(`article_id` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 43542 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '文章视频' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_btn_ads
-- ----------------------------
DROP TABLE IF EXISTS `sq_btn_ads`;
CREATE TABLE `sq_btn_ads`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `type` tinyint NULL DEFAULT 3,
  `start_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '开始时间',
  `end_at` datetime NULL DEFAULT '2099-12-12 00:00:01' COMMENT '结束时间',
  `url` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `desc` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `sort` smallint NULL DEFAULT 0,
  `advertiser_id` int NULL DEFAULT NULL,
  `status` tinyint NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `ads_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '广告编码',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 359 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_btn_statistics
-- ----------------------------
DROP TABLE IF EXISTS `sq_btn_statistics`;
CREATE TABLE `sq_btn_statistics`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `btn_id` int NOT NULL,
  `adv_id` int NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `btn_id`(`btn_id` ASC) USING BTREE,
  INDEX `adv_id`(`adv_id` ASC) USING BTREE,
  INDEX `ip`(`ip` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4704754 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_cache_keys
-- ----------------------------
DROP TABLE IF EXISTS `sq_cache_keys`;
CREATE TABLE `sq_cache_keys`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '缓存名称',
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '缓存key',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE,
  INDEX `key`(`key` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5851250 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '缓存表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_channel
-- ----------------------------
DROP TABLE IF EXISTS `sq_channel`;
CREATE TABLE `sq_channel`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `channel_num` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '渠道编号 如:cg1001',
  `channel_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '渠道标识',
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '渠道名称',
  `rate` decimal(3, 2) NOT NULL DEFAULT 0.00 COMMENT '分成比例',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0禁用 1启用',
  `aff` int NOT NULL DEFAULT 0 COMMENT '渠道归属aff',
  `parent_channel` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `agent_level` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `web_stat` tinyint NOT NULL DEFAULT 0 COMMENT '是否开启web_app统计',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_channel_id`(`channel_id` ASC) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2282 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '渠道表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_configs
-- ----------------------------
DROP TABLE IF EXISTS `sq_configs`;
CREATE TABLE `sq_configs`  (
  `id` int NOT NULL DEFAULT 0,
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '系统维护 0 否 1是',
  `filter_level` tinyint NOT NULL COMMENT '过滤等级 0 否 1 过滤',
  `img_upload` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '图片上传地址',
  `video_upload` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '视频上传地址',
  `upload_mv_sign` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '上传m3u8的签名字符串',
  `tg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '官方tg群',
  `short_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `websocket` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'websoket地址 逗号分隔',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '系统配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_contact
-- ----------------------------
DROP TABLE IF EXISTS `sq_contact`;
CREATE TABLE `sq_contact`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '标题',
  `icon` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '图标',
  `show_val` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '显示',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '联系方式',
  `group` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '分组标识',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态1 生效',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '联系方式表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_contact_us
-- ----------------------------
DROP TABLE IF EXISTS `sq_contact_us`;
CREATE TABLE `sq_contact_us`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'email地址',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '问题描述',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '问题状态（0=未处理，1=已处理）',
  `updated_at` datetime NOT NULL COMMENT '问题处理时间',
  `created_at` datetime NOT NULL COMMENT '问题提交时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `email`(`email` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `created_at`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_day_data
-- ----------------------------
DROP TABLE IF EXISTS `sq_day_data`;
CREATE TABLE `sq_day_data`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '日期',
  `reg_total` int NOT NULL DEFAULT 0 COMMENT '新增',
  `active_total` int NOT NULL DEFAULT 0 COMMENT '活跃',
  `active_ios` int NOT NULL DEFAULT 0 COMMENT 'ios活跃',
  `active_android` int NOT NULL DEFAULT 0 COMMENT '安卓活跃',
  `active_web` int NOT NULL DEFAULT 0 COMMENT 'PWA活跃',
  `pay_total` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '总充值',
  `vip_total` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'vip充值',
  `coins_total` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '金币充值',
  `reg_pay_total` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '新增充值',
  `reg_pay_scale` decimal(5, 2) NOT NULL DEFAULT 0.00 COMMENT '新增充值占比',
  `pay_success_scale` decimal(5, 2) NOT NULL DEFAULT 0.00 COMMENT '支付成功率',
  `coins_consume_total` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '金币消耗数',
  `each_product_total` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '每个产品的销售数量',
  `created_at` timestamp NULL DEFAULT NULL,
  `pay_num` int NULL DEFAULT 0 COMMENT '订单数',
  `coins_consume_num` int NULL DEFAULT 0 COMMENT '金币消耗数',
  `visit_website` int NULL DEFAULT NULL COMMENT '官网访问数',
  `down_and` int NULL DEFAULT NULL COMMENT '安卓下载数',
  `down_web` int NULL DEFAULT NULL COMMENT 'pwa下载数',
  `down_ios` int NULL DEFAULT NULL COMMENT 'ios下载数',
  `down_total` int NULL DEFAULT NULL COMMENT '总下载数',
  `down_rate` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '官网点击率',
  `down_window` int NULL DEFAULT NULL COMMENT 'window下载量',
  `down_macos` int NULL DEFAULT NULL COMMENT 'MACOS下载量',
  `wb_main_line` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '网页主线路',
  `main_line_suc` int NULL DEFAULT NULL COMMENT '成功数',
  `main_line_fail` int NULL DEFAULT NULL COMMENT '失败数',
  `main_line_rate` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '主线路成功率',
  `we_bk_line` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备用线路',
  `bk_line_suc` int NULL DEFAULT NULL COMMENT '备用成功数',
  `bk_line_fail` int NULL DEFAULT NULL COMMENT '备用失败数',
  `bk_line_rate` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '备用线路成功率',
  `keep_1day` int NULL DEFAULT 0 COMMENT '次日留存',
  `keep_3day` int NULL DEFAULT 0 COMMENT '3日留存',
  `keep_7day` int NULL DEFAULT 0 COMMENT '7日留存',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_date`(`date` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 487 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_feedback
-- ----------------------------
DROP TABLE IF EXISTS `sq_feedback`;
CREATE TABLE `sq_feedback`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `mv_id` bigint NOT NULL COMMENT '关联影片ID',
  `question` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '问题描述',
  `message_type` tinyint NOT NULL COMMENT '问题类型:1=不当内容 2=影片失效 3=版权 4=其它',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（0=未处理，1=已处理）\n',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '处理时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_at`(`created_at` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 39071 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '用户反馈表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_flutter_router
-- ----------------------------
DROP TABLE IF EXISTS `sq_flutter_router`;
CREATE TABLE `sq_flutter_router`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '名称',
  `router` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '路由',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL COMMENT '状态',
  `created_at` timestamp NOT NULL COMMENT '创建时间',
  `updated_at` timestamp NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 82 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '内部跳转路由配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_front_statistical
-- ----------------------------
DROP TABLE IF EXISTS `sq_front_statistical`;
CREATE TABLE `sq_front_statistical`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '位置',
  `advertiser_id` int NULL DEFAULT 0 COMMENT '广告主',
  `ip_ct` int NULL DEFAULT 0 COMMENT 'ip',
  `uv_ct` int NOT NULL DEFAULT 0 COMMENT 'UV数',
  `click_ct` int NOT NULL DEFAULT 0 COMMENT '点击数',
  `date` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '日期',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `advertiser_id`(`advertiser_id` ASC) USING BTREE,
  INDEX `position`(`position` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 30924 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '广告统计' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_link
-- ----------------------------
DROP TABLE IF EXISTS `sq_link`;
CREATE TABLE `sq_link`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '名称',
  `clicks` bigint NULL DEFAULT 0 COMMENT '点击量',
  `status` tinyint NULL DEFAULT 0 COMMENT '1=上架 2=下架',
  `follow` tinyint NULL DEFAULT 1 COMMENT '1=显示 2=隐藏',
  `url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '链接',
  `sort` smallint NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_login_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_login_log`;
CREATE TABLE `sq_login_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0,
  `oauth_type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `oauth_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1952929 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '登陆记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_logs
-- ----------------------------
DROP TABLE IF EXISTS `sq_logs`;
CREATE TABLE `sq_logs`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '类型，error,success',
  `ip` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ua` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `http_refer_host` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'sever_refer_host',
  `http_refer_url` varchar(4096) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'sever_refer_url',
  `browser_referer` varchar(4096) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'document.refer',
  `line_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'line url',
  `line_host` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'line host',
  `date` date NOT NULL,
  `created` bigint NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2569438 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_managers
-- ----------------------------
DROP TABLE IF EXISTS `sq_managers`;
CREATE TABLE `sq_managers`  (
  `uid` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `username` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `password` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `nickname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '对外展示名称',
  `role_id` tinyint NOT NULL DEFAULT 8,
  `role_type` enum('admin','normal') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT 'normal',
  `gender` tinyint(1) NOT NULL DEFAULT 1,
  `regip` varchar(39) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `regdate` timestamp NULL DEFAULT NULL,
  `lastip` varchar(39) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `lastvisit` int UNSIGNED NOT NULL DEFAULT 0,
  `expired_at` timestamp NULL DEFAULT NULL COMMENT '会员到期时间',
  `lastpost` int UNSIGNED NOT NULL DEFAULT 0,
  `oltime` smallint NOT NULL DEFAULT 0 COMMENT '在线小时数',
  `login_task` smallint NOT NULL DEFAULT 0 COMMENT '是否领取7日任务',
  `pageviews` mediumint UNSIGNED NOT NULL DEFAULT 0 COMMENT '论坛用的,电影可以不用',
  `score` int NOT NULL DEFAULT 0 COMMENT '用户积分',
  `aff` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT '邀请码md5( md5(uuid) )',
  `invited_by` int NULL DEFAULT 0 COMMENT '被谁 aff 邀请',
  `invited_num` int NOT NULL DEFAULT 0 COMMENT '已邀请安装个数',
  `newpm` tinyint NOT NULL DEFAULT 0,
  `secret` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `new_comment_reply` tinyint NULL DEFAULT 0,
  `new_topic_reply` tinyint NULL DEFAULT 0,
  `login_count` mediumint UNSIGNED NOT NULL DEFAULT 0,
  `app_version` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT 'app版本号',
  `validate` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`) USING BTREE,
  INDEX `uuid`(`uuid` ASC) USING BTREE,
  INDEX `invited_by`(`invited_by` ASC) USING BTREE,
  INDEX `invited_num`(`invited_num` ASC) USING BTREE,
  INDEX `lastactivity`(`expired_at` ASC) USING BTREE,
  INDEX `regdate`(`regdate` ASC) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `username`(`username` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 272 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_member_follow
-- ----------------------------
DROP TABLE IF EXISTS `sq_member_follow`;
CREATE TABLE `sq_member_follow`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0 COMMENT '用户自己',
  `to_aff` int NOT NULL DEFAULT 0 COMMENT '关注的人',
  `created_at` datetime NOT NULL COMMENT '关注时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `to_aff`(`to_aff` ASC) USING BTREE,
  INDEX `aff_to_aff`(`aff` ASC, `to_aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1737200 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '关注表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_member_post_score
-- ----------------------------
DROP TABLE IF EXISTS `sq_member_post_score`;
CREATE TABLE `sq_member_post_score`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0 COMMENT '评分aff',
  `to_aff` int NOT NULL DEFAULT 0 COMMENT '被评分人aff\n',
  `score` int NOT NULL DEFAULT 0 COMMENT '分数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff_to_aff`(`aff` ASC, `to_aff` ASC) USING BTREE,
  INDEX `to_aff`(`to_aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 196 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_unicode_ci COMMENT = '用户帖子评分表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_member_update_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_member_update_log`;
CREATE TABLE `sq_member_update_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0 COMMENT '用户AFF',
  `update` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '更新信息',
  `status` int NULL DEFAULT 0 COMMENT '状态 0待审核 1拒绝 2成功',
  `refuse_reason` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '拒绝原因',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 285 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '用户信息修改审核表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_members
-- ----------------------------
DROP TABLE IF EXISTS `sq_members`;
CREATE TABLE `sq_members`  (
  `uid` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `oauth_type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '设备\'ios\',\'android\'',
  `oauth_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `oauth_new_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `uuid` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `username` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `password` char(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role_id` tinyint NOT NULL DEFAULT 8,
  `gender` tinyint(1) NOT NULL DEFAULT 1,
  `regip` varchar(39) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `regdate` timestamp NULL DEFAULT NULL,
  `lastip` varchar(39) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `expired_at` datetime NULL DEFAULT NULL COMMENT '会员到期时间',
  `lastpost` int UNSIGNED NOT NULL DEFAULT 0,
  `oltime` smallint NOT NULL DEFAULT 0 COMMENT '在线小时数',
  `pageviews` mediumint UNSIGNED NOT NULL DEFAULT 0 COMMENT '论坛用的,电影可以不用',
  `score` int NOT NULL DEFAULT 0 COMMENT '用户积分',
  `aff` int NOT NULL DEFAULT 0 COMMENT '唯一标示',
  `channel` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'self',
  `invited_by` int NULL DEFAULT NULL COMMENT '被谁 aff 邀请',
  `invited_num` int NOT NULL DEFAULT 0 COMMENT '已邀请安装个数',
  `ban_post` int NULL DEFAULT 0 COMMENT '1禁止发资源',
  `post_num` int NULL DEFAULT 0 COMMENT '一天最多发资源数',
  `login_count` mediumint UNSIGNED NOT NULL DEFAULT 0,
  `app_version` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT 'app版本号',
  `validate` tinyint(1) NOT NULL DEFAULT 0,
  `share` smallint NULL DEFAULT 0 COMMENT '分享',
  `is_login` smallint NOT NULL DEFAULT 0,
  `nickname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `thumb` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '用户头像',
  `coins` double NOT NULL DEFAULT 0 COMMENT '铜钱',
  `money` double NOT NULL DEFAULT 0 COMMENT '元宝',
  `proxy_money` decimal(11, 2) NOT NULL DEFAULT 0.00 COMMENT '代理收益',
  `temp_vip` int NOT NULL DEFAULT 0 COMMENT '临时卡',
  `followed_count` int NOT NULL DEFAULT 0 COMMENT '有多少人关注此用户',
  `videos_count` int NOT NULL DEFAULT 0 COMMENT '作品数',
  `fabulous_count` int NOT NULL DEFAULT 0 COMMENT '获赞数',
  `likes_count` int NOT NULL DEFAULT 0 COMMENT '喜欢数',
  `comment_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '视频被评论数',
  `vip_level` tinyint NOT NULL DEFAULT 0 COMMENT 'vip等级',
  `person_signnatrue` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '个人签名',
  `old_vip` int NOT NULL DEFAULT 0 COMMENT '生日',
  `stature` int NOT NULL DEFAULT 0 COMMENT '身高',
  `interest` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '爱好兴趣(此字段已用于嫌疑用户白名单)',
  `city` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '城市',
  `used_money_free_num` int NOT NULL DEFAULT 0 COMMENT '使用免费解锁元宝次数',
  `agent_fee` smallint NOT NULL DEFAULT 0 COMMENT '经纪人扣点1为1%',
  `agent` smallint NOT NULL DEFAULT 0 COMMENT '1经纪人2认证楼风',
  `level` int NULL DEFAULT NULL,
  `build_id` int NOT NULL DEFAULT 0 COMMENT 'build_id 超级签名标识',
  `auth_status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 未认证 1 认证通过',
  `exp` int NOT NULL DEFAULT 0 COMMENT '积分',
  `is_virtual` enum('yes','no') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'no' COMMENT '是否是虚拟用户',
  `chat_uid` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `phone` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '手机',
  `phone_prefix` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '手机地区号',
  `free_view_cnt` smallint NULL DEFAULT 0 COMMENT '剩余免费观看次数',
  `lastactivity` timestamp NULL DEFAULT NULL COMMENT '最后活跃时间',
  `income_total` int NOT NULL COMMENT '提现',
  `income_money` int NOT NULL DEFAULT 0 COMMENT '收益',
  `draw_name` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '提现名字',
  `ip_invite` tinyint NOT NULL DEFAULT 0 COMMENT '是否通过ip进行的邀请',
  `order_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付订单数量',
  `post_count` int UNSIGNED NOT NULL COMMENT '帖子数量',
  `topic_count` int NOT NULL DEFAULT 0 COMMENT '创建的剧集数量',
  `follow_count` int NOT NULL DEFAULT 0 COMMENT '用户关注的数量',
  `tags` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '用户打的标签 最多5个 用逗号分隔',
  `free_view_date` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '剩余免费观看次数视效',
  `invited_reg_num` int NOT NULL DEFAULT 0 COMMENT '操作失败',
  `unread_reply` int NOT NULL DEFAULT 0 COMMENT '未读的评论回复',
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '邮箱',
  `is_admin` int NULL DEFAULT 0 COMMENT '是否为管理',
  `ucenter_id` int NULL DEFAULT 0 COMMENT '用户中心ID',
  `balance` decimal(18, 2) NOT NULL DEFAULT 0.00 COMMENT '余额',
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE INDEX `device`(`oauth_type` ASC, `oauth_id` ASC) USING BTREE,
  INDEX `uuid`(`uuid` ASC) USING BTREE,
  INDEX `invited_by`(`invited_by` ASC) USING BTREE,
  INDEX `invited_num`(`invited_num` ASC) USING BTREE,
  INDEX `lastactivity`(`expired_at` ASC) USING BTREE,
  INDEX `regdate`(`regdate` ASC) USING BTREE,
  INDEX `username`(`username` ASC) USING BTREE,
  INDEX `oauth_new_id`(`oauth_new_id` ASC) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `phone`(`phone` ASC, `phone_prefix` ASC) USING BTREE,
  INDEX `email`(`email` ASC) USING BTREE,
  INDEX `is_admin`(`is_admin` ASC) USING BTREE,
  INDEX `ucenter_id`(`ucenter_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5562246 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_members_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_members_log`;
CREATE TABLE `sq_members_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `oauth_type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '设备类型',
  `lastip` varchar(39) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `lastactivity` timestamp NOT NULL,
  `oltime` smallint NOT NULL DEFAULT 0 COMMENT '在线小时数',
  `pageviews` mediumint UNSIGNED NOT NULL DEFAULT 0,
  `app_version` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT 'app版本号',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `lastactivity`(`lastactivity` ASC) USING BTREE,
  INDEX `uuid`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 38210 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_menu
-- ----------------------------
DROP TABLE IF EXISTS `sq_menu`;
CREATE TABLE `sq_menu`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '图标',
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `icon_width` int NOT NULL DEFAULT 0 COMMENT '图标宽度',
  `icon_height` int NOT NULL DEFAULT 0 COMMENT '图标高度',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '跳转地址',
  `status` int NOT NULL DEFAULT 0 COMMENT '状态',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `type` int NULL DEFAULT 0 COMMENT '跳转类型',
  `position` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT 'left' COMMENT '位置',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `sq_menu_position_index`(`position` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 128 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '菜单列表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_menu_config
-- ----------------------------
DROP TABLE IF EXISTS `sq_menu_config`;
CREATE TABLE `sq_menu_config`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_zh` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '中文名称',
  `name_jp` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '日文名称',
  `name_en` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '英文名称',
  `sort` mediumint NULL DEFAULT 0 COMMENT '排序',
  `type_url` tinyint NULL DEFAULT NULL COMMENT '链接类型',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接URL',
  `status` tinyint NULL DEFAULT 2 COMMENT '状态',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `is_menu` tinyint NULL DEFAULT 0 COMMENT '是不是下拉菜单',
  `p_id` int NULL DEFAULT NULL COMMENT '菜单id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_money_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_money_log`;
CREATE TABLE `sq_money_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0 COMMENT '用户aff',
  `source` int NOT NULL COMMENT '日志来源',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1增 2减',
  `coinCnt` decimal(11, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '获币数量',
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `source_aff` int NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `data_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '数据名称',
  `data_id` int NOT NULL DEFAULT 0 COMMENT '数据id',
  `prev_coin` int NOT NULL DEFAULT 0 COMMENT '之前的金额',
  `next_coin` int NOT NULL DEFAULT 0 COMMENT '之后的金额',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '元宝明细' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sq_mv
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv`;
CREATE TABLE `sq_mv`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `theme` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `title_zh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `title_jp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `title_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `description_zh` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `description_jp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `description_en` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `m3u8` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `v_ext` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `duration` mediumint NULL DEFAULT NULL,
  `cover` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `cover_full` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `preview_video` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `watch_count` int NULL DEFAULT 0,
  `favorite_count` int NULL DEFAULT 0,
  `comment_html_count` int NULL DEFAULT NULL,
  `comment_count` int NULL DEFAULT 0,
  `is_hot` tinyint NULL DEFAULT 2,
  `is_show` tinyint NULL DEFAULT 2,
  `is_recommend` tinyint NULL DEFAULT 2,
  `is_latest` tinyint NULL DEFAULT 2,
  `latest_sort` tinyint NULL DEFAULT NULL,
  `hot_sort` tinyint NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `publish_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `like_count` int NULL DEFAULT 1,
  `hot_today` int NULL DEFAULT 0,
  `hot_week` int NULL DEFAULT 0,
  `hot_month` int NULL DEFAULT 0,
  `hot_total` int NULL DEFAULT 0,
  `xp_id` int UNSIGNED NOT NULL DEFAULT 0,
  `search_hot` tinyint NULL DEFAULT 0,
  `search_sort` int NULL DEFAULT 0,
  `time_node` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '时间节点',
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发行时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `_id`(`_id` ASC) USING BTREE,
  INDEX `hot_today`(`hot_today` ASC) USING BTREE,
  INDEX `hot_week`(`hot_week` ASC) USING BTREE,
  INDEX `hot_month`(`hot_month` ASC) USING BTREE,
  INDEX `hot_total`(`hot_total` ASC) USING BTREE,
  INDEX `idx_mv__id`(`_id` ASC) USING BTREE,
  INDEX `idx_mv_is_show`(`is_show` ASC) USING BTREE,
  INDEX `idx_mv_updated_at`(`updated_at` ASC) USING BTREE,
  INDEX `idx_mv_created_at`(`created_at` ASC) USING BTREE,
  INDEX `idx_mv_is_hot`(`is_hot` ASC) USING BTREE,
  INDEX `idx_mv_hot_sort`(`hot_sort` ASC) USING BTREE,
  INDEX `idx_mv_watch_count`(`watch_count` ASC) USING BTREE,
  INDEX `idx_mv_is_recommend`(`is_recommend` ASC) USING BTREE,
  INDEX `idx_mv_is_latest`(`is_latest` ASC) USING BTREE,
  INDEX `idx_mv_search_hot`(`search_hot` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 40345 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sq_mv_actor_conn
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_actor_conn`;
CREATE TABLE `sq_mv_actor_conn`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `mv_id` bigint NOT NULL COMMENT '影片id',
  `actor_id` int NOT NULL COMMENT '演员id',
  `update_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE,
  INDEX `actor_id`(`actor_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 53383 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '影片演员关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_collect
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_collect`;
CREATE TABLE `sq_mv_collect`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `source_movie_id` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `mid` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `img_x` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `img_y` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `img_type` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `cat_id` int UNSIGNED NOT NULL DEFAULT 0,
  `cat_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `status` int UNSIGNED NOT NULL DEFAULT 0,
  `status_text` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `show_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `duration` int UNSIGNED NOT NULL DEFAULT 0,
  `tags` json NULL,
  `money` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `pay_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `update_status` tinyint NULL DEFAULT 0,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `language` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `director` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `actor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `issue_date` date NULL DEFAULT '1970-01-01',
  `number` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `source_tags` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `preview_images` json NULL,
  `links` json NULL,
  `img_width` int UNSIGNED NOT NULL DEFAULT 0,
  `img_height` int UNSIGNED NOT NULL DEFAULT 0,
  `score` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `source_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `is_used` tinyint NULL DEFAULT 2,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_mv_updated_at`(`updated_at` ASC) USING BTREE,
  INDEX `idx_mv_created_at`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2189 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sq_mv_comment
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_comment`;
CREATE TABLE `sq_mv_comment`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT 0 COMMENT '用户uid',
  `mv_id` int NOT NULL DEFAULT 0 COMMENT '视频ID',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '评论内容',
  `state` tinyint(1) NOT NULL DEFAULT 1 COMMENT '评论状态(1=待审核,2=通过,3=拒绝)',
  `created_at` datetime NOT NULL COMMENT '审核时间',
  `updated_at` datetime NOT NULL COMMENT '创建时间',
  `pid` int NOT NULL DEFAULT 0,
  `comment_count` int NOT NULL DEFAULT 0,
  `reply_uid` int NOT NULL DEFAULT 0,
  `from` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'app' COMMENT 'html,app',
  `is_official` tinyint NULL DEFAULT 0 COMMENT '是否官方评论 0-否 1-是',
  `is_top` tinyint NULL DEFAULT 0 COMMENT '是否置顶 0-否 1-是',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_at`(`created_at` ASC) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE,
  INDEX `updated_at`(`updated_at` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 130155 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_data
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_data`;
CREATE TABLE `sq_mv_data`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `av_mv_id` int NOT NULL COMMENT '关联91av_mv表id',
  `video_id` int NOT NULL COMMENT '采集视频id',
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '采集视频标题',
  `category` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '分类名（显示用的）',
  `tag_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分类标签名（标识）',
  `preview_mp4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频预览mp4',
  `lang` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '语言',
  `created_at` bigint NOT NULL DEFAULT 0,
  `updated_at` bigint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 78500 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_hot
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_hot`;
CREATE TABLE `sq_mv_hot`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `mv_id` bigint NOT NULL COMMENT '影片id',
  `date` date NOT NULL COMMENT '热度日期',
  `watch_count` int NULL DEFAULT 0 COMMENT '观看数量',
  `collect_count` int NULL DEFAULT 0 COMMENT '收藏数量',
  `hot_value` int NOT NULL DEFAULT 0 COMMENT '热度',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `mvid_date`(`date` ASC, `mv_id` ASC) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE,
  INDEX `watch_count`(`watch_count` ASC) USING BTREE,
  INDEX `collect_count`(`collect_count` ASC) USING BTREE,
  INDEX `hot_value`(`hot_value` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1579517 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '影片热度表(影片三月内的热度值)' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_style
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_style`;
CREATE TABLE `sq_mv_style`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_zh` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name_jp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '日语',
  `name_en` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '英语',
  `desc_zh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '描述',
  `desc_jp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `desc_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `iconv` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '主题小图标',
  `count` int NULL DEFAULT NULL COMMENT '影片数量',
  `sort` int NOT NULL DEFAULT 0 COMMENT '手动排序',
  `check_num` int NOT NULL DEFAULT 0 COMMENT '点击量',
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL,
  `top_show` tinyint NOT NULL DEFAULT 0,
  `top_sort` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `style_name_en`(`name_en` ASC) USING BTREE,
  INDEX `style_name_jp`(`name_jp` ASC) USING BTREE,
  INDEX `style_name_zh`(`name_zh` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '影片主题' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_style_conn
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_style_conn`;
CREATE TABLE `sq_mv_style_conn`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `style_id` int NULL DEFAULT NULL COMMENT '主题id',
  `mv_id` int NULL DEFAULT NULL COMMENT 'mv_id',
  `created_at` datetime NULL DEFAULT NULL,
  `update_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `style_id`(`style_id` ASC) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE,
  INDEX `idx_style_mv`(`style_id` ASC, `mv_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 81731 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '影片主题关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_tag
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_tag`;
CREATE TABLE `sq_mv_tag`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `p_id` int NULL DEFAULT 0 COMMENT '子标签',
  `name_zh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '标签名称',
  `name_jp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '日文标签名称',
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '英文标签名称',
  `another_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '别名',
  `desc_zh` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '标签描述',
  `desc_jp` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `desc_en` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `is_show` tinyint NOT NULL DEFAULT 1,
  `is_hot` tinyint NOT NULL DEFAULT 0 COMMENT '是否热推(1=是|0=否)',
  `count` int NOT NULL COMMENT '关联影片数',
  `top_show` tinyint NOT NULL DEFAULT 0,
  `top_sort` tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `name_en`(`name_en` ASC) USING BTREE,
  INDEX `tag_name_jp`(`name_jp` ASC) USING BTREE,
  INDEX `tag_name_zh`(`name_zh` ASC) USING BTREE,
  INDEX `p_id`(`p_id` ASC) USING BTREE,
  INDEX `is_show`(`is_show` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3605 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '影片标签表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_mv_tag_conn
-- ----------------------------
DROP TABLE IF EXISTS `sq_mv_tag_conn`;
CREATE TABLE `sq_mv_tag_conn`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag_id` int NOT NULL COMMENT '影片标签id',
  `mv_id` bigint NOT NULL COMMENT '影片id',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  UNIQUE INDEX `tag_mv_id`(`tag_id` ASC, `mv_id` ASC) USING BTREE,
  INDEX `tag_id`(`tag_id` ASC) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 345554 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '影片标签关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_notice
-- ----------------------------
DROP TABLE IF EXISTS `sq_notice`;
CREATE TABLE `sq_notice`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` char(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '公告类型 url 跳转链接  route 路由',
  `aff` int NOT NULL DEFAULT 0 COMMENT '如：0 所有人',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '跳转地址',
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1显示 0 关闭',
  `created_at` timestamp NULL DEFAULT NULL,
  `img_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '图片地址',
  `router` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '路由',
  `width` int NOT NULL DEFAULT 0,
  `height` int NOT NULL DEFAULT 0,
  `visible_type` tinyint NOT NULL DEFAULT 0 COMMENT '可见类型',
  `sort` int NOT NULL DEFAULT 0 COMMENT '可见类型',
  `pos` smallint NOT NULL DEFAULT 0 COMMENT '位置',
  `start_at` datetime NULL DEFAULT '2000-01-01 00:00:00' COMMENT '广告有效期',
  `end_at` datetime NULL DEFAULT '2099-01-01 00:00:00' COMMENT '广告有效期',
  `clicked` int NULL DEFAULT 0 COMMENT '点击量',
  `ads_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '跑马灯公告' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_permissions
-- ----------------------------
DROP TABLE IF EXISTS `sq_permissions`;
CREATE TABLE `sq_permissions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `p_id` int NOT NULL DEFAULT 0,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `module` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `controller` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '控制器',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `args` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '参数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `p_id`(`p_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 484 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_plate
-- ----------------------------
DROP TABLE IF EXISTS `sq_plate`;
CREATE TABLE `sq_plate`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '板块名',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `article_ct` int NOT NULL DEFAULT 0 COMMENT '文章数量',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `status` int NULL DEFAULT 1 COMMENT '状态',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT 'seo关键字',
  `seo_title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT 'seo标题',
  `show_client` enum('all','pc','app') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'all' COMMENT '客户端显示',
  `seo_description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT 'seo描述',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '板块表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post
-- ----------------------------
DROP TABLE IF EXISTS `sq_post`;
CREATE TABLE `sq_post`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户AFF',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `is_deleted` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户删除标识 0否 1是 ',
  `like_num` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数量',
  `comment_num` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数量',
  `is_best` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '置精 0否 1是',
  `category` tinyint NOT NULL DEFAULT 1 COMMENT '帖子类型 1图片 2视频 3图文',
  `refuse_reason` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '拒绝通过的原因',
  `photo_num` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片数量',
  `video_num` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '视频数量',
  `is_finished` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '资源是否完成 0否1是',
  `ipstr` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户ip',
  `cityname` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '定位城市',
  `topic_id` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '话题ID',
  `view_num` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览数量',
  `refresh_at` timestamp NULL DEFAULT NULL COMMENT '刷新时间',
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `reward_amount` int NOT NULL DEFAULT 0 COMMENT '打赏金币',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:待审核 1:审核中 2.审核通过 3.未通过 4.被举报',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序 越大越前',
  `favorite_num` int NOT NULL DEFAULT 0 COMMENT '收藏数',
  `reward_num` int NOT NULL DEFAULT 0 COMMENT '打赏次数',
  `set_top` int NULL DEFAULT 0,
  `content_word` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '帖子文字内容',
  `coins` int NOT NULL DEFAULT 0 COMMENT '解锁金币',
  `admin_id` int NULL DEFAULT 0 COMMENT '审核管理员ID',
  `is_subscribe` tinyint NULL DEFAULT 0 COMMENT '是否订阅贴 0免费 1订阅帖子',
  `hot_sort` int NULL DEFAULT 0 COMMENT '热门排序(浏览+点赞+收藏)',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `is_deleted`(`is_deleted` ASC) USING BTREE,
  INDEX `is_best`(`is_best` ASC) USING BTREE,
  INDEX `category`(`category` ASC) USING BTREE,
  INDEX `topic_id`(`topic_id` ASC) USING BTREE,
  INDEX `refresh_at`(`refresh_at` ASC) USING BTREE,
  INDEX `is_subscribe`(`is_subscribe` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 189683 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '帖子表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_ban
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_ban`;
CREATE TABLE `sq_post_ban`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0,
  `num` int NOT NULL DEFAULT 0 COMMENT '违规次数',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4197 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '用户帖子评论违规记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_club_members
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_club_members`;
CREATE TABLE `sq_post_club_members`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL,
  `club_id` int NOT NULL,
  `club_aff` int NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `expired_at` bigint NOT NULL,
  `created_at` bigint NOT NULL,
  `updated_at` bigint NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `club_id`(`club_id` ASC) USING BTREE,
  INDEX `club_aff`(`club_aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17972 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子粉丝团成员' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_clubs
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_clubs`;
CREATE TABLE `sq_post_clubs`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL,
  `month` int NOT NULL DEFAULT 0 COMMENT '月卡价格',
  `quarter` int NOT NULL DEFAULT 0 COMMENT '季卡价格',
  `year` int NOT NULL DEFAULT 0 COMMENT '年卡价格',
  `post_num` int NOT NULL DEFAULT 0 COMMENT '帖子数量',
  `member_num` int NOT NULL DEFAULT 0 COMMENT '成员数量',
  `month_income` int NOT NULL DEFAULT 0 COMMENT '月卡收益',
  `quarter_income` int NOT NULL DEFAULT 0 COMMENT '季卡收益',
  `year_income` int NOT NULL DEFAULT 0 COMMENT '年卡收益',
  `created_at` bigint NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` bigint NOT NULL DEFAULT 0 COMMENT '创建时间',
  `notice` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '通知',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `aff`(`aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 61 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子粉丝团' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_comment
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_comment`;
CREATE TABLE `sq_post_comment`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '帖子ID',
  `pid` int NOT NULL DEFAULT 0 COMMENT '评论ID,默认0(第一层评论)',
  `aff` int NOT NULL DEFAULT 0 COMMENT '用户aff',
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '留言内容',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:待审核\n1:审核通过\n2.未通过\n3.禁言\n',
  `is_read` tinyint NOT NULL DEFAULT 0 COMMENT '被回复者是否已读',
  `like_num` int NOT NULL DEFAULT 0 COMMENT '此条评论点赞数量',
  `video_num` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '视频数量',
  `photo_num` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片数量',
  `ipstr` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户ip',
  `cityname` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '定位城市',
  `complain_num` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '被举报次数',
  `refuse_reason` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '拒绝通过原因',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_finished` int NOT NULL DEFAULT 0 COMMENT '资源是否处理完 0未处理 1已处理',
  `author` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '评论者昵称',
  `reply_aff` int NOT NULL DEFAULT 0 COMMENT '回复对应评论人的aff',
  `ads_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '广告链接',
  `redirect_type` tinyint NULL DEFAULT 0 COMMENT '跳转类型 0外部 1内部',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `admin_id` int NULL DEFAULT 0 COMMENT '审核管理ID',
  `reply_ct` int NULL DEFAULT 0 COMMENT '二级回复数量',
  `sec_parent` int NULL DEFAULT 0 COMMENT 'app回复ID',
  `fix_reply` tinyint NULL DEFAULT 0 COMMENT '是否更新二级评论数量',
  `is_top` tinyint NULL DEFAULT 0 COMMENT '置顶',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `post_id`(`post_id` ASC) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1160843 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_comment_keyword
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_comment_keyword`;
CREATE TABLE `sq_post_comment_keyword`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT '' COMMENT '关键词',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `keyword`(`keyword` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_comments_like
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_comments_like`;
CREATE TABLE `sq_post_comments_like`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT NULL,
  `cid` int NULL DEFAULT NULL COMMENT '评论ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `cid`(`cid` ASC) USING BTREE,
  INDEX `cid_aff`(`cid` ASC, `aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 44257 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '社区评论点赞表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_creator
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_creator`;
CREATE TABLE `sq_post_creator`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT 0 COMMENT 'aff',
  `nickname` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `post_club_month` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '帖子订阅月卡价格',
  `post_club_quarter` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '帖子订阅季卡价格',
  `post_club_year` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '帖子订阅年卡价格',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否是博主 0不是 1是',
  `ban_post` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否封禁 0不是 1是',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `work_score` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '作品分数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_aff`(`aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 58 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子博主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_media
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_media`;
CREATE TABLE `sq_post_media`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `media_url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '视频或图片地址',
  `cover` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '视频封面',
  `thumb_width` int NOT NULL DEFAULT 0 COMMENT '封面宽',
  `thumb_height` int NOT NULL DEFAULT 0 COMMENT '封面高',
  `pid` int NOT NULL DEFAULT 0 COMMENT '帖子ID',
  `aff` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT '上传用户AFF',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型 1图片 2视频',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 未转换 1 已转换 2 转换中',
  `duration` int NOT NULL DEFAULT 0 COMMENT '视频持续时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `relate_type` tinyint(1) NULL DEFAULT NULL COMMENT '关联类型 1帖子 2评论',
  `mp4` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT 'mp4',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 534645 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子媒体表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_reward_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_reward_log`;
CREATE TABLE `sq_post_reward_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT 0,
  `post_id` int NULL DEFAULT 0,
  `post_aff` int NULL DEFAULT 0,
  `amount` int NULL DEFAULT 0,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 263 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '帖子打赏表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_topic
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_topic`;
CREATE TABLE `sq_post_topic`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` int NOT NULL DEFAULT 0 COMMENT '类型ID',
  `thumb` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '话题封面',
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '话题名称',
  `follow_num` int NOT NULL DEFAULT 0 COMMENT '关注人数',
  `view_num` int NOT NULL DEFAULT 0 COMMENT '浏览人数',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '0不显示 1显示',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `sort` int NOT NULL COMMENT '排序 越大越前',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '热门 0非热门 1热门 ',
  `bg_thumb` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '背景图片',
  `post_num` int NOT NULL DEFAULT 0 COMMENT '帖子数',
  `intro` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL COMMENT '简介',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `pid`(`pid` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子话题信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_post_topic_category
-- ----------------------------
DROP TABLE IF EXISTS `sq_post_topic_category`;
CREATE TABLE `sq_post_topic_category`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '类型名称',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '0不显示 1显示',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `sort` int NULL DEFAULT 0 COMMENT '排序 越大越前',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子话题分类' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_push_url
-- ----------------------------
DROP TABLE IF EXISTS `sq_push_url`;
CREATE TABLE `sq_push_url`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `status` tinyint NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 156004 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_rank_lists
-- ----------------------------
DROP TABLE IF EXISTS `sq_rank_lists`;
CREATE TABLE `sq_rank_lists`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `m_id` int NOT NULL,
  `name` tinyint NOT NULL DEFAULT 1 COMMENT '榜单类型 1好评 2人气 3热卖',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'av' COMMENT '视频类型',
  `rank_num` int NOT NULL DEFAULT 1 COMMENT '榜单名次',
  `count` int NOT NULL DEFAULT 0 COMMENT '榜单值',
  `week` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '0' COMMENT '第几期',
  `created_at` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `m_id`(`m_id` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `week`(`week` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '榜单记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_register_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_register_log`;
CREATE TABLE `sq_register_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL DEFAULT 0,
  `old_uid` int NULL DEFAULT NULL,
  `aff` int NULL DEFAULT NULL,
  `oauth_type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `phone` char(13) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `oauth_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2887 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '注册记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_relationships
-- ----------------------------
DROP TABLE IF EXISTS `sq_relationships`;
CREATE TABLE `sq_relationships`  (
  `cid` int UNSIGNED NOT NULL,
  `mid` int UNSIGNED NOT NULL,
  PRIMARY KEY (`cid`, `mid`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '文章分类关系表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_release
-- ----------------------------
DROP TABLE IF EXISTS `sq_release`;
CREATE TABLE `sq_release`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_remote_upload
-- ----------------------------
DROP TABLE IF EXISTS `sq_remote_upload`;
CREATE TABLE `sq_remote_upload`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` bigint UNSIGNED NOT NULL COMMENT '用户ID',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `progress_rate` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '进度(如 75% / 0.75 / 文本)',
  `upload_type` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0普通 1分段',
  `upload_status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0未上传 1上传完成',
  `slice_status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0未切片 1切片中 2切片完成',
  `cover` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面路径/URL',
  `mp4_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'MP4地址',
  `m3u8_url` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'M3U8地址',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_uid`(`uid` ASC) USING BTREE,
  INDEX `idx_status`(`upload_status` ASC, `slice_status` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_role
-- ----------------------------
DROP TABLE IF EXISTS `sq_role`;
CREATE TABLE `sq_role`  (
  `role_id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_name` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '角色名称',
  `role_action_ids` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '权限ids,1,2,5',
  PRIMARY KEY (`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 33 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_role_permission
-- ----------------------------
DROP TABLE IF EXISTS `sq_role_permission`;
CREATE TABLE `sq_role_permission`  (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  INDEX `role_id`(`role_id` ASC) USING BTREE,
  INDEX `permission_id`(`permission_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '权限-角色关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_rotate_images
-- ----------------------------
DROP TABLE IF EXISTS `sq_rotate_images`;
CREATE TABLE `sq_rotate_images`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '轮播图标题',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '轮播图片描述',
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '轮播图片路径',
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `sort` int NOT NULL DEFAULT 0 COMMENT '轮播图片排序',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态，默认0关闭1开启',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_rub_comment
-- ----------------------------
DROP TABLE IF EXISTS `sq_rub_comment`;
CREATE TABLE `sq_rub_comment`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `p_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '帖子/文章ID',
  `aff` int NOT NULL DEFAULT 0 COMMENT '用户aff',
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '留言内容',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 文章评论 1 社区帖子评论',
  `ipstr` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '用户ip',
  `cityname` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '' COMMENT '定位城市',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nickname` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '' COMMENT '昵称',
  `data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `p_id`(`p_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13118 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '垃圾评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_search_word
-- ----------------------------
DROP TABLE IF EXISTS `sq_search_word`;
CREATE TABLE `sq_search_word`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `aff` int NOT NULL,
  `type` tinyint NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2454855 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_sensitive_words
-- ----------------------------
DROP TABLE IF EXISTS `sq_sensitive_words`;
CREATE TABLE `sq_sensitive_words`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '敏感词',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 112 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_seo
-- ----------------------------
DROP TABLE IF EXISTS `sq_seo`;
CREATE TABLE `sq_seo`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `position` int NULL DEFAULT NULL,
  `title_cn` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `title_ja` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `title_en` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `keywords_cn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `keywords_ja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `keywords_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `description_cn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `description_ja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `description_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `index`(`id` ASC) USING BTREE,
  INDEX `position`(`position` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 33 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_sessions
-- ----------------------------
DROP TABLE IF EXISTS `sq_sessions`;
CREATE TABLE `sq_sessions`  (
  `sid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `ip` char(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT 'ip',
  `uuid` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `username` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `action` int NULL DEFAULT 0,
  `lastactivity` int UNSIGNED NOT NULL DEFAULT 0,
  `lastolupdate` int UNSIGNED NOT NULL DEFAULT 0,
  `pageviews` smallint UNSIGNED NOT NULL DEFAULT 0,
  `seccode` mediumint UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE INDEX `sid`(`sid` ASC) USING BTREE,
  INDEX `action`(`action` ASC) USING BTREE,
  INDEX `lastactivity`(`lastactivity` ASC) USING BTREE,
  INDEX `lastolupdate`(`lastolupdate` ASC) USING BTREE,
  INDEX `uid`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_setting
-- ----------------------------
DROP TABLE IF EXISTS `sq_setting`;
CREATE TABLE `sq_setting`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置名',
  `var_name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置key名称',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置值',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置备注',
  `status` enum('yes','no') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `var_name`(`var_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 56 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_sys_total
-- ----------------------------
DROP TABLE IF EXISTS `sq_sys_total`;
CREATE TABLE `sq_sys_total`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '键名',
  `value` int NOT NULL DEFAULT 0 COMMENT '统计',
  `date` date NOT NULL COMMENT '日期',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `date`(`date` ASC, `name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 455 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统统计' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_system_notice
-- ----------------------------
DROP TABLE IF EXISTS `sq_system_notice`;
CREATE TABLE `sq_system_notice`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `read` int NULL DEFAULT 1 COMMENT '1未读2已读',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `type` int NULL DEFAULT NULL COMMENT '1系统消息2解锁验证消息',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1871324 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_tags
-- ----------------------------
DROP TABLE IF EXISTS `sq_tags`;
CREATE TABLE `sq_tags`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '标签名',
  `count` int NULL DEFAULT 0 COMMENT '文章数',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `type` tinyint NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 51475 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '文章标签' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_transit
-- ----------------------------
DROP TABLE IF EXISTS `sq_transit`;
CREATE TABLE `sq_transit`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `main_line` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '',
  `backup_line` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '0',
  `fixed_domain` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '固定域名',
  `app_download_url` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT 'app下载地址',
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '0',
  `home_url` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '',
  `twitter_url` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '0',
  `qq_url` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT '0',
  `address_page` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  `transit_head_meta` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '中转页面配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user
-- ----------------------------
DROP TABLE IF EXISTS `sq_user`;
CREATE TABLE `sq_user`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `uuid` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '邮箱',
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '用户名',
  `password` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `profile` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '个人简介',
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `allow_login` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否允许登录(1=是|0=否)',
  `reg_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '注册IP',
  `last_login_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `sq_user_name`(`username` ASC) USING BTREE,
  UNIQUE INDEX `sq_user_email`(`email` ASC) USING BTREE,
  INDEX `uuid`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100812 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_community_like_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_community_like_log`;
CREATE TABLE `sq_user_community_like_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT NULL,
  `related_id` int NULL DEFAULT NULL COMMENT '帖子ID',
  `type` int NULL DEFAULT NULL COMMENT '类型 0帖子 1评论',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `related_id`(`related_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 821484 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子点赞表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_favorites_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_favorites_log`;
CREATE TABLE `sq_user_favorites_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `aff` int NULL DEFAULT NULL,
  `related_id` int NULL DEFAULT NULL COMMENT '关联ID',
  `type` int NULL DEFAULT NULL COMMENT '类型 0帖子 1评论',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `book_id`(`related_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9864967 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '帖子收藏表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_help
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_help`;
CREATE TABLE `sq_user_help`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `answer` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL,
  `status` int NULL DEFAULT NULL,
  `type` int NULL DEFAULT NULL,
  `views` int NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_model
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_model`;
CREATE TABLE `sq_user_model`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 56 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '客服回复模板' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_mv_collected
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_mv_collected`;
CREATE TABLE `sq_user_mv_collected`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `mv_id` bigint NOT NULL DEFAULT 0,
  `uid` bigint NOT NULL COMMENT '用户唯一id',
  `created_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1272756 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户影片收藏记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_online
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_online`;
CREATE TABLE `sq_user_online`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date NULL DEFAULT NULL,
  `t0` int NULL DEFAULT 0,
  `t1` int NULL DEFAULT 0,
  `t2` int NULL DEFAULT 0,
  `t3` int NULL DEFAULT 0,
  `t4` int NULL DEFAULT 0,
  `t5` int NULL DEFAULT 0,
  `t6` int NULL DEFAULT 0,
  `t7` int NULL DEFAULT 0,
  `t8` int NULL DEFAULT 0,
  `t9` int NULL DEFAULT 0,
  `t10` int NULL DEFAULT 0,
  `t11` int NULL DEFAULT 0,
  `t12` int NULL DEFAULT 0,
  `t13` int NULL DEFAULT 0,
  `t14` int NULL DEFAULT 0,
  `t15` int NULL DEFAULT 0,
  `t16` int NULL DEFAULT 0,
  `t17` int NULL DEFAULT 0,
  `t18` int NULL DEFAULT 0,
  `t19` int NULL DEFAULT 0,
  `t20` int NULL DEFAULT 0,
  `t21` int NULL DEFAULT 0,
  `t22` int NULL DEFAULT 0,
  `t23` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `date`(`date` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 325 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_play_later
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_play_later`;
CREATE TABLE `sq_user_play_later`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `mv_id` bigint NOT NULL,
  `uid` bigint NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `mv_id`(`mv_id` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 132113 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_user_post_topic_follow_log
-- ----------------------------
DROP TABLE IF EXISTS `sq_user_post_topic_follow_log`;
CREATE TABLE `sq_user_post_topic_follow_log`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `aff` int NOT NULL DEFAULT 0 COMMENT '用户aff',
  `related_id` int NOT NULL DEFAULT 0 COMMENT '帖子的话题ID',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aff`(`aff` ASC) USING BTREE,
  INDEX `related_id`(`related_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = '用户关注记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_users
-- ----------------------------
DROP TABLE IF EXISTS `sq_users`;
CREATE TABLE `sq_users`  (
  `uid` int NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `password` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `mail` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `screenName` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created` bigint NULL DEFAULT 0,
  `activated` int NULL DEFAULT 0,
  `logged` int NULL DEFAULT 0,
  `group` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'visitor',
  `authCode` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE INDEX `typecho_users_name`(`name` ASC) USING BTREE,
  UNIQUE INDEX `typecho_users_mail`(`mail` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_version
-- ----------------------------
DROP TABLE IF EXISTS `sq_version`;
CREATE TABLE `sq_version`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '版本号',
  `type` char(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'android' COMMENT '型号',
  `apk` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '下载连接',
  `tips` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT '更新说明',
  `must` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 不强制更新 1强制',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 启用  2 停用',
  `message` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '\'\'' COMMENT '系统维护公告',
  `mstatus` tinyint(1) NOT NULL DEFAULT 0 COMMENT '系统公告状态 0 没有 1通知 2禁用',
  `channel` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL COMMENT '渠道标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci COMMENT = 'app下载版本控制表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sq_visit_statistics
-- ----------------------------
DROP TABLE IF EXISTS `sq_visit_statistics`;
CREATE TABLE `sq_visit_statistics`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
