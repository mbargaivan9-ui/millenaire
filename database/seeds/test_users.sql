-- Hash: password123 with bcrypt
-- Created with: Hash::make('password123')
INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at) VALUES 
('Admin User', 'admin@millenaire.com', '$2y$12$S/oZu/8YJEJLjVKBjJ/Gy.oQ1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1', 'admin', NOW(), NOW(), NOW()),
('Teacher User', 'teacher@millenaire.com', '$2y$12$S/oZu/8YJEJLjVKBjJ/Gy.oQ1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1', 'teacher', NOW(), NOW(), NOW()),
('Parent User', 'parent@millenaire.com', '$2y$12$S/oZu/8YJEJLjVKBjJ/Gy.oQ1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1', 'parent', NOW(), NOW(), NOW()),
('Student User', 'student@millenaire.com', '$2y$12$S/oZu/8YJEJLjVKBjJ/Gy.oQ1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1Q1', 'student', NOW(), NOW(), NOW());
