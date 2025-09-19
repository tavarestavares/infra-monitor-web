-- Database Monitor Dashboard SQL Schema
-- Version: 2.0

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS monitor_dashboard;
USE monitor_dashboard;

-- Tabela de serviços internos
CREATE TABLE IF NOT EXISTS internal_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    port INT NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    status ENUM('online', 'offline') DEFAULT 'offline',
    last_check DATETIME,
    last_online DATETIME,
    response_time INT,
    check_interval INT DEFAULT 60,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de serviços externos
CREATE TABLE IF NOT EXISTS external_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    domain VARCHAR(255),
    ip_address VARCHAR(45),
    port INT NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    status ENUM('online', 'offline') DEFAULT 'offline',
    last_check DATETIME,
    last_online DATETIME,
    response_time INT,
    ssl_info TEXT,
    check_interval INT DEFAULT 60,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de histórico de verificações
CREATE TABLE IF NOT EXISTS check_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    service_type ENUM('internal', 'external') NOT NULL,
    status ENUM('online', 'offline') NOT NULL,
    response_time INT,
    error_message TEXT,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_type (service_id, service_type),
    INDEX idx_checked_at (checked_at)
);

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir configurações padrão
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('auto_refresh', '60'),
    ('alert_email', ''),
    ('smtp_host', ''),
    ('smtp_port', '587'),
    ('smtp_user', ''),
    ('smtp_pass', ''),
    ('telegram_bot_token', ''),
    ('telegram_chat_id', ''),
    ('webhook_url', '');

-- Adicionar campo last_online se não existir
ALTER TABLE internal_services ADD COLUMN IF NOT EXISTS last_online DATETIME AFTER last_check;
ALTER TABLE external_services ADD COLUMN IF NOT EXISTS last_online DATETIME AFTER last_check;

-- Criar trigger para atualizar last_online quando status muda para online
DELIMITER $$

CREATE TRIGGER update_internal_last_online
BEFORE UPDATE ON internal_services
FOR EACH ROW
BEGIN
    IF NEW.status = 'online' AND (OLD.status = 'offline' OR OLD.status IS NULL) THEN
        SET NEW.last_online = NOW();
    END IF;
END$$

CREATE TRIGGER update_external_last_online
BEFORE UPDATE ON external_services
FOR EACH ROW
BEGIN
    IF NEW.status = 'online' AND (OLD.status = 'offline' OR OLD.status IS NULL) THEN
        SET NEW.last_online = NOW();
    END IF;
END$$

DELIMITER ;