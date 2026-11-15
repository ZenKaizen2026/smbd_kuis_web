CREATE DATABASE IF NOT EXISTS `db_smbd_kuis`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `db_smbd_kuis`;

CREATE TABLE IF NOT EXISTS `app_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nim_or_username` VARCHAR(50) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_app_users_username` (`nim_or_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `app_learning_progress` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `modul_no` TINYINT UNSIGNED NOT NULL,
  `status` ENUM('belum', 'ulang', 'paham') NOT NULL DEFAULT 'belum',
  `last_score` DECIMAL(5,2) DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_modul` (`user_id`, `modul_no`),
  CONSTRAINT `fk_prog_user` FOREIGN KEY (`user_id`) REFERENCES `app_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `app_quiz_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `modul_no` TINYINT UNSIGNED NOT NULL,
  `score_percent` DECIMAL(5,2) NOT NULL,
  `correct_count` SMALLINT UNSIGNED NOT NULL,
  `total_questions` SMALLINT UNSIGNED NOT NULL,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 0,
  `detail_json` JSON NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attempt_user_modul` (`user_id`, `modul_no`),
  CONSTRAINT `fk_attempt_user` FOREIGN KEY (`user_id`) REFERENCES `app_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `app_custom_modules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `modul_no` TINYINT UNSIGNED NOT NULL,
  `judul` VARCHAR(150) NOT NULL,
  `isi_materi` TEXT NOT NULL,
  `contoh_sql` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_custom_mod_user` (`user_id`, `modul_no`),
  CONSTRAINT `fk_custom_mod_user` FOREIGN KEY (`user_id`) REFERENCES `app_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
