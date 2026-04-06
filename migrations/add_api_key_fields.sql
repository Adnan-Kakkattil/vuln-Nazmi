-- ============================================================================
-- Add Missing Columns to pos_api_keys Table
-- ============================================================================
-- This migration adds columns that the admin interface expects
-- ============================================================================

SET @dbname = DATABASE();
SET @tablename = 'pos_api_keys';

-- Add key_name column (alias for name)
SET @columnname = 'key_name';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT ''Column key_name already exists'' AS message',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` VARCHAR(255) DEFAULT NULL COMMENT ''Alias for name column''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add api_secret column (alias for api_secret_hash)
SET @columnname = 'api_secret';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT ''Column api_secret already exists'' AS message',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` VARCHAR(255) DEFAULT NULL COMMENT ''Alias for api_secret_hash''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add tenant_id column
SET @columnname = 'tenant_id';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT ''Column tenant_id already exists'' AS message',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` BIGINT UNSIGNED DEFAULT NULL COMMENT ''POS tenant ID''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add tenant_name column
SET @columnname = 'tenant_name';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT ''Column tenant_name already exists'' AS message',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` VARCHAR(255) DEFAULT NULL COMMENT ''POS tenant name''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add allowed_ips column
SET @columnname = 'allowed_ips';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT ''Column allowed_ips already exists'' AS message',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` VARCHAR(255) DEFAULT NULL COMMENT ''Comma-separated list of allowed IP addresses''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add notes column
SET @columnname = 'notes';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            (TABLE_SCHEMA = @dbname)
            AND (TABLE_NAME = @tablename)
            AND (COLUMN_NAME = @columnname)
    ) > 0,
    'SELECT ''Column notes already exists'' AS message',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` TEXT DEFAULT NULL COMMENT ''Additional notes''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing records to sync name -> key_name and api_secret_hash -> api_secret
UPDATE pos_api_keys 
SET 
    key_name = COALESCE(key_name, name),
    api_secret = COALESCE(api_secret, api_secret_hash)
WHERE key_name IS NULL OR api_secret IS NULL;

-- ============================================================================
-- Migration Complete!
-- ============================================================================
SELECT 'API key fields migration completed successfully!' AS message;
