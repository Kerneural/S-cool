-- Initialize secondary test database for automated test suite isolation
CREATE DATABASE IF NOT EXISTS scool_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON scool_test.* TO 'scool_user'@'%';
FLUSH PRIVILEGES;
