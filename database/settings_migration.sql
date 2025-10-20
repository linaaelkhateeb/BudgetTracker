-- Settings feature migration
-- Run this in your `budgettracker` database (e.g., via phpMyAdmin)

START TRANSACTION;

-- User preferences (one row per user)
CREATE TABLE IF NOT EXISTS preferences (
  user_id INT(10) UNSIGNED NOT NULL PRIMARY KEY,
  currency VARCHAR(3) NOT NULL DEFAULT 'USD',
  date_format VARCHAR(20) NOT NULL DEFAULT 'Y-m-d',
  week_start TINYINT(1) NOT NULL DEFAULT 1, -- 1 = Monday
  alerts TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_preferences_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories per user
CREATE TABLE IF NOT EXISTS categories (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(10) UNSIGNED NOT NULL,
  name VARCHAR(64) NOT NULL,
  color VARCHAR(7) DEFAULT NULL, -- e.g. #AABBCC
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_categories_user (user_id),
  CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login history (optional display in Security tab)
CREATE TABLE IF NOT EXISTS login_history (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(10) UNSIGNED NOT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_user (user_id),
  CONSTRAINT fk_login_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
