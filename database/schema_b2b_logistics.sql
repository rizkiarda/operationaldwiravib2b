-- =====================================================================
-- DATABASE SCHEMA: SISTEM ORKESTRASI EKSPEDISI & LOGISTIK B2B
-- Target RDBMS: MySQL 8.0+
-- Engine: InnoDB
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `b2b_logistics_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `b2b_logistics_db`;

-- Matikan cek foreign key sementara saat inisialisasi tabel
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. TABEL PENGGUNA & HAK AKSES (USERS & RBAC)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone_number` VARCHAR(25) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('SUPERADMIN', 'DISPATCHER', 'FINANCE', 'CLIENT_USER', 'VENDOR_USER') NOT NULL,
  `entity_id` BIGINT UNSIGNED NULL COMMENT 'ID klien jika role CLIENT_USER, atau ID vendor jika role VENDOR_USER',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_entity` (`role`, `entity_id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. TABEL MASTER KLIEN B2B (SHIPPER / PEMILIK KARGO)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(150) NOT NULL,
  `business_license_no` VARCHAR(50) NULL COMMENT 'NIB / SIUP',
  `npwp` VARCHAR(30) NULL,
  `address` TEXT NOT NULL,
  `billing_address` TEXT NULL,
  `pic_name` VARCHAR(100) NOT NULL,
  `pic_phone` VARCHAR(25) NOT NULL,
  `pic_email` VARCHAR(100) NULL,
  `default_top_days` INT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Term of Payment default (hari)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. TABEL MASTER AGEN TRANSPORTASI / VENDOR (TRANSPORTER)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `transporters`;
CREATE TABLE `transporters` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(150) NOT NULL,
  `pic_name` VARCHAR(100) NOT NULL,
  `pic_phone` VARCHAR(25) NOT NULL,
  `pic_email` VARCHAR(100) NULL,
  `npwp` VARCHAR(30) NULL,
  `address` TEXT NOT NULL,
  `bank_name` VARCHAR(50) NOT NULL,
  `bank_account_no` VARCHAR(50) NOT NULL,
  `bank_account_holder` VARCHAR(100) NOT NULL,
  `default_top_days` INT UNSIGNED NOT NULL DEFAULT 14 COMMENT 'Term of Payment ke vendor (hari)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. TABEL DOKUMEN PAYUNG / KONTRAK / MOU
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `mou_contracts`;
CREATE TABLE `mou_contracts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `contract_number` VARCHAR(100) NOT NULL UNIQUE,
  `party_type` ENUM('CLIENT', 'TRANSPORTER') NOT NULL,
  `party_id` BIGINT UNSIGNED NOT NULL COMMENT 'Relasi ke clients.id atau transporters.id',
  `title` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `file_url` VARCHAR(255) NULL COMMENT 'Link PDF kontrak digital',
  `terms_liability` TEXT NULL COMMENT 'Poin penting tanggung jawab & penalti klaim',
  `status` ENUM('ACTIVE', 'EXPIRED', 'TERMINATED') NOT NULL DEFAULT 'ACTIVE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_mou_party` (`party_type`, `party_id`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. TABEL KATALOG TIPE ARMADA (VEHICLE TYPES)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `vehicle_types`;
CREATE TABLE `vehicle_types` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE COMMENT 'Misal: CDD_BOX, FUSO_BAK, TRONTON_WINGBOX',
  `name` VARCHAR(100) NOT NULL,
  `max_weight_kg` DECIMAL(10,2) NOT NULL,
  `max_volume_cbm` DECIMAL(10,2) NOT NULL,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. TABEL ORDER B2B / SHIPPING INSTRUCTION (DARI KLIEN)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: ORD-YYYYMM-XXXXX',
  `client_id` BIGINT UNSIGNED NOT NULL,
  `po_number_client` VARCHAR(100) NOT NULL COMMENT 'Nomor PO fisik/dokumen dari klien',
  `vehicle_type_id` INT UNSIGNED NOT NULL,
  
  -- Alamat Muat & Bongkar
  `origin_facility_name` VARCHAR(150) NOT NULL,
  `origin_address` TEXT NOT NULL,
  `origin_pic_name` VARCHAR(100) NOT NULL,
  `origin_pic_phone` VARCHAR(25) NOT NULL,
  `loading_plan_date` DATETIME NOT NULL,
  
  `dest_facility_name` VARCHAR(150) NOT NULL,
  `dest_address` TEXT NOT NULL,
  `dest_pic_name` VARCHAR(100) NOT NULL,
  `dest_pic_phone` VARCHAR(25) NOT NULL,
  `unloading_plan_date` DATETIME NOT NULL,
  
  -- Finansial Klien (Selling Rate)
  `selling_price` DECIMAL(15,2) NOT NULL COMMENT 'Tarif tagihan ke klien (sebelum pajak)',
  `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 1.10 COMMENT 'Contoh PPN 1.1%',
  `special_instructions` TEXT NULL COMMENT 'Misal: Wajib terpal lapis 2, helm proyek',
  `insurance_type` ENUM('OWNER_RISK', 'ALL_RISK') NOT NULL DEFAULT 'OWNER_RISK',
  
  -- Status Order
  `status` ENUM(
    'DRAFT',
    'CONFIRMED',
    'WO_DISPATCHED',
    'IN_TRANSIT',
    'DELIVERED',
    'COMPLETED',
    'CANCELLED'
  ) NOT NULL DEFAULT 'DRAFT',
  
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_orders_vehicle_type` FOREIGN KEY (`vehicle_type_id`) REFERENCES `vehicle_types` (`id`) ON DELETE RESTRICT,
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_loading_date` (`loading_plan_date`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. TABEL ITEM DETAIL MUATAN (CARGO ITEMS)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `item_name` VARCHAR(200) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(30) NOT NULL COMMENT 'Koli, Karton, Palet, Drum, Zak, Kg',
  `weight_kg` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `volume_cbm` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `remarks` VARCHAR(255) NULL,
  
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. TABEL WORK ORDER & PENUGASAN VENDOR (WO KE TRANSPORTER)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `work_orders`;
CREATE TABLE `work_orders` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wo_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: WO-YYYYMM-XXXXX',
  `order_id` BIGINT UNSIGNED NOT NULL,
  `transporter_id` BIGINT UNSIGNED NOT NULL,
  
  -- Biaya Sewa Armada (Buying Cost)
  `buying_cost` DECIMAL(15,2) NOT NULL COMMENT 'Biaya sewa yang harus dibayar ke vendor',
  `additional_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Biaya inap/kuli jika ada',
  `margin_profit` DECIMAL(15,2) GENERATED ALWAYS AS (0.00) VIRTUAL COMMENT 'Akan dikalkulasi via query/trigger',
  
  -- Informasi Armada & Pengemudi (Diisi oleh Vendor / Dispatcher)
  `driver_name` VARCHAR(100) NULL,
  `driver_phone` VARCHAR(25) NULL,
  `driver_id_card_no` VARCHAR(50) NULL COMMENT 'No KTP Driver',
  `vehicle_plate_no` VARCHAR(20) NULL COMMENT 'Nomor Polisi (Nopol)',
  
  -- Nomor Surat Jalan Resmi
  `delivery_note_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'No. Surat Jalan Driver (SJ/YYYYMM/XXXX)',
  `qr_code_hash` VARCHAR(64) NOT NULL UNIQUE COMMENT 'Hash untuk verifikasi QR pada surat jalan',
  
  `status` ENUM(
    'ASSIGNED',
    'ACCEPTED_BY_VENDOR',
    'AT_ORIGIN',
    'LOADED',
    'ON_THE_WAY',
    'AT_DESTINATION',
    'UNLOADED_WAITING_POD',
    'POD_VERIFIED',
    'REJECTED'
  ) NOT NULL DEFAULT 'ASSIGNED',
  
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_wo_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_wo_transporter` FOREIGN KEY (`transporter_id`) REFERENCES `transporters` (`id`) ON DELETE RESTRICT,
  INDEX `idx_wo_status` (`status`),
  INDEX `idx_wo_delivery_note` (`delivery_note_number`),
  INDEX `idx_wo_plate` (`vehicle_plate_no`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. TABEL STATUS TRACKING / MILESTONE PERJALANAN
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `shipment_tracking_logs`;
CREATE TABLE `shipment_tracking_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `work_order_id` BIGINT UNSIGNED NOT NULL,
  `milestone` VARCHAR(50) NOT NULL COMMENT 'CHECK_IN_ORIGIN, LOADING_START, DEPARTED, CHECKPOINT, ARRIVED_DEST, UNLOADED',
  `location_description` VARCHAR(255) NULL,
  `latitude` DECIMAL(10,8) NULL,
  `longitude` DECIMAL(11,8) NULL,
  `notes` TEXT NULL,
  `logged_by_user_id` BIGINT UNSIGNED NULL,
  `logged_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_tracking_wo` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE,
  INDEX `idx_tracking_wo_time` (`work_order_id`, `logged_at`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. TABEL PROOF OF DELIVERY (POD) & SERAH TERIMA
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `delivery_proofs`;
CREATE TABLE `delivery_proofs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `work_order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  
  -- Berkas Bukti Digital
  `epod_image_url` VARCHAR(255) NOT NULL COMMENT 'Foto Surat Jalan bertanda tangan & cap stempel basah',
  `cargo_unloaded_photo_url` VARCHAR(255) NULL COMMENT 'Foto fisik kargo selesai dibongkar',
  
  -- Serah Terima Penerima
  `recipient_name` VARCHAR(100) NOT NULL COMMENT 'Nama terang penerima barang di lokasi',
  `received_at` DATETIME NOT NULL COMMENT 'Waktu bongkar selesai',
  `submitted_latitude` DECIMAL(10,8) NULL,
  `submitted_longitude` DECIMAL(11,8) NULL,
  
  -- Penanganan Masalah & BAST
  `has_discrepancy` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 jika barang rusak, susut, basah, atau selisih',
  `bast_document_url` VARCHAR(255) NULL COMMENT 'Foto Berita Acara Kerusakan/Selisih jika ada insiden',
  `discrepancy_notes` TEXT NULL,
  
  -- Verifikasi Finansial Internal
  `verification_status` ENUM('PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
  `verified_by_user_id` BIGINT UNSIGNED NULL,
  `verified_at` DATETIME NULL,
  `hardcopy_received_date` DATE NULL COMMENT 'Tanggal surat jalan fisik asli sampai di kantor ekspedisi',
  
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_pod_wo` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. TABEL RINCIAN DISKREPANSI/KLAIM ITEM POD
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `delivery_proof_items`;
CREATE TABLE `delivery_proof_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `delivery_proof_id` BIGINT UNSIGNED NOT NULL,
  `order_item_id` BIGINT UNSIGNED NOT NULL,
  `received_good_qty` DECIMAL(10,2) NOT NULL,
  `received_damaged_qty` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `received_lost_qty` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `remarks` VARCHAR(255) NULL,
  
  CONSTRAINT `fk_pod_items_pod` FOREIGN KEY (`delivery_proof_id`) REFERENCES `delivery_proofs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pod_items_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. TABEL PENAGIHAN KLIEN (CLIENT INVOICES - ACCOUNTS RECEIVABLE)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `client_invoices`;
CREATE TABLE `client_invoices` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: INV-YYYYMM-XXXXX',
  `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `client_id` BIGINT UNSIGNED NOT NULL,
  `subtotal_amount` DECIMAL(15,2) NOT NULL,
  `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `deduction_claim_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Potongan klaim barang rusak jika disepakati',
  `total_amount` DECIMAL(15,2) NOT NULL,
  
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `payment_status` ENUM('UNPAID', 'PARTIALLY_PAID', 'PAID', 'OVERDUE') NOT NULL DEFAULT 'UNPAID',
  `paid_at` DATETIME NULL,
  `receipt_attachment_url` VARCHAR(255) NULL,
  
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_cinv_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_cinv_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT,
  INDEX `idx_cinv_status` (`payment_status`),
  INDEX `idx_cinv_due_date` (`due_date`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 13. TABEL TAGIHAN VENDOR (VENDOR BILLS - ACCOUNTS PAYABLE)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `vendor_bills`;
CREATE TABLE `vendor_bills` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `bill_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nomor tagihan internal: BILL-YYYYMM-XXXXX',
  `work_order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
  `transporter_id` BIGINT UNSIGNED NOT NULL,
  `vendor_invoice_ref_no` VARCHAR(100) NULL COMMENT 'No invoice fisik dari vendor',
  
  `agreed_cost` DECIMAL(15,2) NOT NULL,
  `extra_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Demurrage / inap',
  `penalty_claim_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Potongan penalti kerusakan kargo',
  `total_payable` DECIMAL(15,2) NOT NULL,
  
  `bill_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `payment_status` ENUM('PENDING_APPROVAL', 'APPROVED', 'PAID') NOT NULL DEFAULT 'PENDING_APPROVAL',
  `paid_at` DATETIME NULL,
  `payment_proof_url` VARCHAR(255) NULL,
  
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_vbill_wo` FOREIGN KEY (`work_order_id`) REFERENCES `work_orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_vbill_transporter` FOREIGN KEY (`transporter_id`) REFERENCES `transporters` (`id`) ON DELETE RESTRICT,
  INDEX `idx_vbill_status` (`payment_status`),
  INDEX `idx_vbill_due_date` (`due_date`)
) ENGINE=InnoDB;

-- Hidupkan kembali cek foreign key
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 14. VIEW ANALITIK MARGIN PROFITABILITAS PER TRIP
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW `vw_shipment_profitability` AS
SELECT 
    o.id AS order_id,
    o.order_code,
    c.company_name AS client_name,
    t.company_name AS transporter_name,
    wo.delivery_note_number,
    wo.vehicle_plate_no,
    o.selling_price AS revenue_client,
    (wo.buying_cost + wo.additional_cost) AS cost_vendor,
    (o.selling_price - (wo.buying_cost + wo.additional_cost)) AS gross_profit,
    ROUND(((o.selling_price - (wo.buying_cost + wo.additional_cost)) / o.selling_price) * 100, 2) AS gross_margin_percent,
    dp.verification_status AS pod_status,
    ci.payment_status AS client_payment_status,
    vb.payment_status AS vendor_payment_status
FROM orders o
JOIN clients c ON o.client_id = c.id
JOIN work_orders wo ON wo.order_id = o.id
JOIN transporters t ON wo.transporter_id = t.id
LEFT JOIN delivery_proofs dp ON dp.work_order_id = wo.id
LEFT JOIN client_invoices ci ON ci.order_id = o.id
LEFT JOIN vendor_bills vb ON vb.work_order_id = wo.id;
