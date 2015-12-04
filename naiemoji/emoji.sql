CREATE DATABASE emoji_manager;

USE task_manager;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `token` varchar(32) NOT NULL,
  `created_at` timestamp not null default current_timestamp,
  `updated_at` timestamp not null default current_timestamp on update current_timestamp,
  `time` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `emojis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `keywords` varchar(32) NOT NULL,
  `emoji` varchar(32) NOT NULL,
  `category` varchar(32) NOT NULL,
  `user` varchar(32) NOT NULL,
  `created_at` timestamp not null default current_timestamp,
  `updated_at` timestamp not null default current_timestamp on update current_timestamp,
  PRIMARY KEY (`id`)
);
