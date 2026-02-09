CREATE TABLE roles (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       role_name VARCHAR(50) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (role_name) VALUES
                                  ('customer'),
                                  ('admin'),
                                  ('visitor');