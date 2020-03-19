/*
 Navicat MySQL Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 100137
 Source Host           : localhost:3306
 Source Schema         : db2

 Target Server Type    : MySQL
 Target Server Version : 100137
 File Encoding         : 65001

 Date: 2/12/2020 22:55:30
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `enroll`;
DROP TABLE IF EXISTS `enroll2`;
DROP TABLE IF EXISTS `assign`;
DROP TABLE IF EXISTS `mentors`;
DROP TABLE IF EXISTS `mentees`;
DROP TABLE IF EXISTS `material`;
DROP TABLE IF EXISTS `meetings`;
DROP TABLE IF EXISTS `time_slot`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `parents`;
DROP TABLE IF EXISTS `users`;

-- ----------------------------
-- Table structure for users
-- ----------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for parents
-- ----------------------------

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`parent_id`),
  CONSTRAINT `parent_user` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for students
-- ----------------------------

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `grade` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`student_id`),
  KEY `student_parent` (`parent_id`),
  CONSTRAINT `student_user` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for admins
-- ----------------------------

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`admin_id`),
  CONSTRAINT `admins_user` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for groups
-- ----------------------------

CREATE TABLE `groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` int(11) DEFAULT NULL,
  `mentor_grade_req` int(11) DEFAULT NULL,
  `mentee_grade_req` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for time_slot
-- ----------------------------

CREATE TABLE `time_slot` (
  `time_slot_id` int(11) NOT NULL AUTO_INCREMENT,
  `day_of_the_week` varchar(255) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`time_slot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for meetings
-- ----------------------------

CREATE TABLE `meetings` (
  `meet_id` int(11) NOT NULL AUTO_INCREMENT,
  `meet_name` varchar(255) NOT NULL,
  `date` date DEFAULT NULL,
  `time_slot_id` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  `announcement` varchar(255) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`meet_id`),
  KEY `meeting_group` (`group_id`),
  KEY `meeting_time_slot` (`time_slot_id`),
  CONSTRAINT `meeting_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `meeting_time_slot` FOREIGN KEY (`time_slot_id`) REFERENCES `time_slot` (`time_slot_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for material
-- ----------------------------

CREATE TABLE `material` (
  `material_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `assigned_date` date NOT NULL,
  `notes` text,
  PRIMARY KEY (`material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mentees
-- ----------------------------

CREATE TABLE `mentees` (
  `mentee_id` int(11) NOT NULL,
  PRIMARY KEY (`mentee_id`),
  CONSTRAINT `mentee_student` FOREIGN KEY (`mentee_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for mentors
-- ----------------------------

CREATE TABLE `mentors` (
  `mentor_id` int(11) NOT NULL,
  PRIMARY KEY (`mentor_id`),
  CONSTRAINT `mentor_student` FOREIGN KEY (`mentor_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for enroll
-- ----------------------------

CREATE TABLE `enroll` (
  `meet_id` int(11) NOT NULL,
  `mentee_id` int(11) NOT NULL,
  PRIMARY KEY (`meet_id`,`mentee_id`),
  KEY `enroll_mentee` (`mentee_id`),
  CONSTRAINT `enroll_mentee` FOREIGN KEY (`mentee_id`) REFERENCES `mentees` (`mentee_id`) ON DELETE CASCADE,
  CONSTRAINT `enroll_meetings` FOREIGN KEY (`meet_id`) REFERENCES `meetings` (`meet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for enroll2
-- ----------------------------

CREATE TABLE `enroll2` (
  `meet_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  PRIMARY KEY (`meet_id`,`mentor_id`),
  KEY `enroll2_mentor` (`mentor_id`),
  CONSTRAINT `enroll2_mentor` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`mentor_id`) ON DELETE CASCADE,
  CONSTRAINT `enroll2_meetings` FOREIGN KEY (`meet_id`) REFERENCES `meetings` (`meet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for assign
-- ----------------------------

CREATE TABLE `assign` (
  `meet_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  PRIMARY KEY (`meet_id`,`material_id`),
  KEY `assign_material` (`material_id`),
  KEY `assign_meetings` (`meet_id`),
  CONSTRAINT `assign_material` FOREIGN KEY (`material_id`) REFERENCES `material` (`material_id`) ON DELETE CASCADE,
  CONSTRAINT `assign_meetings` FOREIGN KEY (`meet_id`) REFERENCES `meetings` (`meet_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Christopher Goulart', 'cgoulart35@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Fenim Patel', 'fenimpatel@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Jaime Klein', 'JaimeKlein@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Bryan Stone', 'BryanStone@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Neal Walton', 'NealWalton@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Josefina Davis', 'JosefinaDavis@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Clay Benson', 'ClayBenson@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Cora Gilbert', 'CoraGilbert@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Sidney Soto', 'SidneySoto@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Robert Bishop', 'RobertBishop@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Winifred Richardson', 'WinifredRichardson@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Laverne Curtis', 'LaverneCurtis@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Meredith West', 'MeredithWest@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Glen Allison', 'GlenAllison@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Clyde Barton', 'ClydeBarton@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Roberto Lane', 'RobertoLane@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Verna Alvarado', 'VernaAlvarado@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Eduardo Duncan', 'EduardoDuncan@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Hector Franklin', 'HectorFranklin@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('George Bennett', 'GeorgeBennett@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Nancy Jackson', 'NancyJackson@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Luke Hale', 'LukeHale@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Goulart', 'lorigoulart@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Patel', 'loripatel@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Klein', 'loriKlein@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Stone', 'loriStone@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Walton', 'loriWalton@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Davis', 'loriDavis@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Benson', 'loriBenson@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Gilbert', 'loriGilbert@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Soto', 'loriSoto@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Bishop', 'loriBishop@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Richardson', 'LoriRichardson@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Curtis', 'LoriCurtis@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori West', 'LoriWest@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Allison', 'LoriAllison@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Barton', 'LoriBarton@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Lane', 'LoriLane@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Alvarado', 'LoriAlvarado@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Duncan', 'LoriDuncan@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Franklin', 'LoriFranklin@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Bennett', 'LoriBennett@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Jackson', 'LoriJackson@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Lori Hale', 'LoriHale@gmail.com', 'password', '5084313590');
INSERT INTO `users` (`name`, `email`, `password`, `phone`) VALUES ('Admin User', 'admin@gmail.com', 'password', '5084313590');

INSERT INTO `admins` (`admin_id`) VALUES (60);

INSERT INTO `parents` (`parent_id`) VALUES (38);
INSERT INTO `parents` (`parent_id`) VALUES (39);
INSERT INTO `parents` (`parent_id`) VALUES (40);
INSERT INTO `parents` (`parent_id`) VALUES (41);
INSERT INTO `parents` (`parent_id`) VALUES (42);
INSERT INTO `parents` (`parent_id`) VALUES (43);
INSERT INTO `parents` (`parent_id`) VALUES (44);
INSERT INTO `parents` (`parent_id`) VALUES (45);
INSERT INTO `parents` (`parent_id`) VALUES (46);
INSERT INTO `parents` (`parent_id`) VALUES (47);
INSERT INTO `parents` (`parent_id`) VALUES (48);
INSERT INTO `parents` (`parent_id`) VALUES (49);
INSERT INTO `parents` (`parent_id`) VALUES (50);
INSERT INTO `parents` (`parent_id`) VALUES (51);
INSERT INTO `parents` (`parent_id`) VALUES (52);
INSERT INTO `parents` (`parent_id`) VALUES (53);
INSERT INTO `parents` (`parent_id`) VALUES (54);
INSERT INTO `parents` (`parent_id`) VALUES (55);
INSERT INTO `parents` (`parent_id`) VALUES (56);
INSERT INTO `parents` (`parent_id`) VALUES (57);
INSERT INTO `parents` (`parent_id`) VALUES (58);
INSERT INTO `parents` (`parent_id`) VALUES (59);

INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (16, 38, 6);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (17, 39, 6);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (18, 40, 6);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (19, 41, 7);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (20, 42, 7);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (21, 43, 7);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (22, 44, 8);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (23, 45, 8);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (24, 46, 8);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (25, 47, 9);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (26, 48, 9);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (27, 49, 9);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (28, 50, 9);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (29, 51, 9);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (30, 52, 10);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (31, 53, 10);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (32, 54, 11);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (33, 55, 11);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (34, 56, 11);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (35, 57, 12);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (36, 58, 12);
INSERT INTO `students` (`student_id`, `parent_id`, `grade`) VALUES (37, 59, 12);

INSERT INTO `mentors` (`mentor_id`) VALUES (28);
INSERT INTO `mentors` (`mentor_id`) VALUES (29);
INSERT INTO `mentors` (`mentor_id`) VALUES (30);
INSERT INTO `mentors` (`mentor_id`) VALUES (31);
INSERT INTO `mentors` (`mentor_id`) VALUES (32);
INSERT INTO `mentors` (`mentor_id`) VALUES (33);
INSERT INTO `mentors` (`mentor_id`) VALUES (34);
INSERT INTO `mentors` (`mentor_id`) VALUES (35);
INSERT INTO `mentors` (`mentor_id`) VALUES (36);
INSERT INTO `mentors` (`mentor_id`) VALUES (37);

INSERT INTO `mentees` (`mentee_id`) VALUES (16);
INSERT INTO `mentees` (`mentee_id`) VALUES (17);
INSERT INTO `mentees` (`mentee_id`) VALUES (18);
INSERT INTO `mentees` (`mentee_id`) VALUES (19);
INSERT INTO `mentees` (`mentee_id`) VALUES (20);
INSERT INTO `mentees` (`mentee_id`) VALUES (21);
INSERT INTO `mentees` (`mentee_id`) VALUES (22);
INSERT INTO `mentees` (`mentee_id`) VALUES (23);
INSERT INTO `mentees` (`mentee_id`) VALUES (24);
INSERT INTO `mentees` (`mentee_id`) VALUES (25);
INSERT INTO `mentees` (`mentee_id`) VALUES (26);
INSERT INTO `mentees` (`mentee_id`) VALUES (27);

INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 6', 6, 9, NULL);
INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 7', 7, 10, NULL);
INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 8', 8, 11, NULL);
INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 9', 9, 12, 6);
INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 10', 10, NULL, 7);
INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 11', 11, NULL, 8);
INSERT INTO `groups` (`name`, `description`, `mentor_grade_req`, `mentee_grade_req`) VALUES ('Group 12', 12, NULL, 9);

INSERT INTO `time_slot` (`day_of_the_week`, `start_time`, `end_time`) VALUES ('Saturday', '14:00:00', '15:00:00');
INSERT INTO `time_slot` (`day_of_the_week`, `start_time`, `end_time`) VALUES ('Sunday', '14:00:00', '15:00:00');
INSERT INTO `time_slot` (`day_of_the_week`, `start_time`, `end_time`) VALUES ('Saturday', '15:00:00', '16:00:00');

INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 6 English Section 1', '2020-03-28', 7, 9, 'Announcement!', 1);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 6 English Section 2', '2020-03-29', 8, 9, 'Announcement!', 1);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 6 Math Section 1', '2020-03-28', 9, 9, 'Announcement!', 1);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 7 English Section 1', '2020-03-28', 7, 9, 'Announcement!', 2);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 7 Math Section 1', '2020-03-29', 8, 9, 'Announcement!', 2);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 8 English Section 1', '2020-04-04', 7, 9, 'Announcement!', 3);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 8 Math Section 1', '2020-04-05', 8, 9, 'Announcement!', 3);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 9 English Section 1', '2020-04-04', 7, 9, 'Announcement!', 4);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Grade 9 Math Section 1', '2020-04-05', 8, 9, 'Announcement!', 4);
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Add Mentors Test', '2020-03-21', '7', '9', 'The admin should be prompted to add mentors.', '4');
INSERT INTO `meetings` (`meet_name`, `date`, `time_slot_id`, `capacity`, `announcement`, `group_id`) VALUES ('Cancel Meeting Test', '2020-03-21', '7', '9', 'This meeting should be canceled because there are less than 3 mentees.', '4');

INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (100, 16);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (100, 17);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (100, 18);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (102, 16);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (102, 17);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (102, 18);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (103, 19);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (103, 20);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (103, 21);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (104, 19);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (104, 20);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (104, 21);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (105, 22);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (105, 23);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (105, 24);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (106, 22);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (106, 23);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (106, 24);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (107, 25);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (107, 26);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (107, 27);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (108, 25);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (108, 26);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (108, 27);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (109, 16);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (109, 17);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (109, 18);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (110, 16);
INSERT INTO `enroll` (`meet_id`, `mentee_id`) VALUES (110, 18);

INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (100, 28);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (100, 29);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (102, 28);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (102, 29);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (103, 30);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (103, 31);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (104, 30);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (104, 31);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (105, 32);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (105, 33);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (106, 32);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (106, 33);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (107, 35);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (107, 36);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (108, 35);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (108, 36);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (110, 28);
INSERT INTO `enroll2` (`meet_id`, `mentor_id`) VALUES (110, 29);

INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 6 English Material', 'Baby Yoda', 'Reading Assignment', 'https://google.com', '2020-02-29', 'Grade 6 English Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 6 Math Material', 'Tiger Woods', 'Math Assignment', 'https://bing.com', '2020-02-29', 'Grade 6 Math Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 7 English Material', 'Baby Yoda', 'Reading Assignment', 'https://google.com', '2020-02-29', 'Grade 7 English Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 7 Math Material', 'Tiger Woods', 'Math Assignment', 'https://bing.com', '2020-03-01', 'Grade 7 Math Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 8 English Material', 'Baby Yoda', 'Reading Assignment', 'https://google.com', '2020-02-29', 'Grade 8 English Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 8 Math Material', 'Tiger Woods', 'Math Assignment', 'https://bing.com', '2020-03-01', 'Grade 8 Math Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 9 English Material', 'Baby Yoda', 'Reading Assignment', 'https://google.com', '2020-02-29', 'Grade 9 English Material Notes');
INSERT INTO `material` (`title`, `author`, `type`, `url`, `assigned_date`, `notes`) VALUES ('Grade 9 Math Material', 'Tiger Woods', 'Math Assignment', 'https://bing.com', '2020-03-01', 'Grade 9 Math Material Notes');

INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (100, 12);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (102, 13);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (103, 14);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (104, 15);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (105, 16);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (106, 17);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (107, 18);
INSERT INTO `assign` (`meet_id`, `material_id`) VALUES (108, 19);
