-- Migration: Room Dashboard Payments
-- Creates room_transactions table for customer-initiated payments

CREATE TABLE IF NOT EXISTS `room_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `transaction_type` ENUM('ORDER', 'EXTENSION') NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'order_id or extension_id',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` ENUM('GCASH', 'CASH') NOT NULL,
  `gcash_account_name` varchar(255) DEFAULT NULL,
  `gcash_reference_number` varchar(100) DEFAULT NULL,
  `status` ENUM('PENDING_CASHIER_VERIFICATION', 'PENDING_CASH_COLLECTION', 'APPROVED', 'REJECTED', 'PAID', 'COMPLETED') NOT NULL,
  `cashier_id` int(11) DEFAULT NULL COMMENT 'cashier who verified/completed',
  `cashier_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `idx_rental_id` (`rental_id`),
  KEY `idx_room_id` (`room_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
