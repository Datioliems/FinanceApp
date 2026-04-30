-- ============================================================
-- CHECKPOINT C — Schema đơn giản (không có Auth)
-- Phạm vi đề bài: Transaction + Budget + Template Method
-- ============================================================
-- Cách chạy:
--   mysql -u root -p -e "CREATE DATABASE de13_finance CHARACTER SET utf8mb4;"
--   mysql -u root -p de13_finance < database/migrations/000_schema.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── categories ───────────────────────────────────────────────
-- Không có user_id FK → danh mục dùng chung (1 người dùng)
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)  NOT NULL,
    `type`       ENUM('income','expense','both') NOT NULL DEFAULT 'both',
    `icon`       VARCHAR(50)   NULL,
    `color`      VARCHAR(7)    NULL      COMMENT 'Hex cho Chart.js',
    `created_at` DATETIME      NOT NULL  DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_cat_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Danh mục thu/chi';

-- ── budgets ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `budgets` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `category_id`     INT UNSIGNED   NOT NULL,
    `limit_amount`    DECIMAL(15,2)  NOT NULL,
    `alert_threshold` TINYINT        NOT NULL DEFAULT 80
                                     COMMENT 'Cảnh báo khi chi >= X%',
    `month`           TINYINT(2)     NOT NULL,
    `year`            SMALLINT(4)    NOT NULL,
    `created_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_budget_cat_month` (`category_id`, `month`, `year`),
    INDEX `idx_budget_month` (`month`, `year`),
    CONSTRAINT `fk_budget_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Ngân sách tháng theo danh mục';

-- ── transactions ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `transactions` (
    `id`          INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `category_id` INT UNSIGNED        NOT NULL,
    `type`        ENUM('income','expense') NOT NULL,
    `amount`      DECIMAL(15,2)       NOT NULL,
    `note`        VARCHAR(500)        NULL,
    `trans_date`  DATE                NOT NULL,
    `created_at`  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_tx_date`     (`trans_date`),
    INDEX `idx_tx_type`     (`type`),
    INDEX `idx_tx_category` (`category_id`),

    CONSTRAINT `fk_tx_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tất cả giao dịch thu và chi';

SET FOREIGN_KEY_CHECKS = 1;

-- ── Seed danh mục mẫu ────────────────────────────────────────
INSERT IGNORE INTO `categories` (`name`, `type`, `icon`, `color`) VALUES
('Ăn uống',       'expense', 'bi-cup-hot',     '#FF6384'),
('Đi lại',        'expense', 'bi-car-front',   '#36A2EB'),
('Giải trí',      'expense', 'bi-controller',  '#FFCE56'),
('Hóa đơn',       'expense', 'bi-receipt',     '#4BC0C0'),
('Mua sắm',       'expense', 'bi-bag',         '#9966FF'),
('Y tế',          'expense', 'bi-heart-pulse', '#FF9F40'),
('Lương',         'income',  'bi-cash-coin',   '#22c55e'),
('Thu nhập khác', 'income',  'bi-plus-circle', '#3b82f6');

SELECT 'Schema checkpoint C đã sẵn sàng!' AS status;
SHOW TABLES;
