-- Migration: Add customer_transactions table
-- Run this migration to support in-room GCash/Cash payment requests

CREATE TABLE IF NOT EXISTS `customer_transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `transaction_type` enum('ORDER','EXTEND_TIME') NOT NULL,
  `reference_id` int(11) DEFAULT NULL COMMENT 'order_id or extension_id',
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('GCASH','CASH') NOT NULL,
  `gcash_account_name` varchar(255) DEFAULT NULL,
  `gcash_reference_number` varchar(100) DEFAULT NULL,
  `status` enum('PENDING_VERIFICATION','APPROVED','REJECTED','PENDING_STAFF_COLLECTION','COLLECTED','COMPLETED') NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`transaction_id`),
  KEY `rental_id` (`rental_id`),
  KEY `room_id` (`room_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
