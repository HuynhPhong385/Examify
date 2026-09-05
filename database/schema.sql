CREATE DATABASE IF NOT EXISTS examify
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE examify;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL UNIQUE,
    description TEXT NULL,
    category VARCHAR(100) NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    passing_score DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_exams_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_exams_pub_updated (is_published, updated_at),
    INDEX idx_exams_pub_title (is_published, title),
    INDEX idx_exams_category (category),
    INDEX idx_exams_title (title),
    FULLTEXT INDEX ft_exams_title_description (title, description)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id BIGINT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    points DECIMAL(7,2) NOT NULL DEFAULT 1.00,
    position INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_questions_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    INDEX idx_questions_exam_position (exam_id, position)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS choices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT UNSIGNED NOT NULL,
    content VARCHAR(1000) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    position TINYINT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT fk_choices_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_choices_question_position (question_id, position),
    INDEX idx_choices_correct (question_id, is_correct)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at DATETIME NULL,
    score DECIMAL(9,2) NOT NULL DEFAULT 0,
    total_points DECIMAL(9,2) NOT NULL DEFAULT 0,
    percentage DECIMAL(6,2) NOT NULL DEFAULT 0,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('in_progress','submitted') NOT NULL DEFAULT 'in_progress',
    CONSTRAINT fk_attempt_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_attempt_user_status (user_id, status),
    INDEX idx_attempt_exam_user (exam_id, user_id),
    INDEX idx_attempt_submitted (submitted_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attempt_answers (
    attempt_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    choice_id BIGINT UNSIGNED NOT NULL,
    is_correct TINYINT(1) NULL,
    points_awarded DECIMAL(7,2) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (attempt_id, question_id),
    CONSTRAINT fk_answer_attempt FOREIGN KEY (attempt_id) REFERENCES attempts(id) ON DELETE CASCADE,
    CONSTRAINT fk_answer_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_answer_choice FOREIGN KEY (choice_id) REFERENCES choices(id) ON DELETE CASCADE,
    INDEX idx_answer_choice (choice_id)
) ENGINE=InnoDB;
