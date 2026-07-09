-- Master Tables
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS designations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS employment_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Upgrade existing teachers table
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS employee_id VARCHAR(50) UNIQUE;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS email VARCHAR(100) UNIQUE;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS phone VARCHAR(20) UNIQUE;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS department_id INT;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS designation_id INT;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS employment_type_id INT;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Other');
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS blood_group VARCHAR(10);
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS dob DATE;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS joining_date DATE;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS qualification VARCHAR(255);
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS experience_years INT DEFAULT 0;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS address TEXT;
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(100);
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(20);
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255);
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active';
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL; -- For Soft Delete

-- Documents Table
CREATE TABLE IF NOT EXISTS teacher_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    document_type ENUM('resume', 'certificate', 'id_proof', 'degree', 'other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- Audit Logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('admin', 'teacher', 'student') NOT NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    record_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('admin', 'teacher', 'student') NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some dummy master data if empty
INSERT IGNORE INTO departments (name) VALUES ('Computer Science'), ('Mathematics'), ('Physics'), ('Chemistry');
INSERT IGNORE INTO designations (name) VALUES ('Professor'), ('Assistant Professor'), ('Lecturer');
INSERT IGNORE INTO employment_types (name) VALUES ('Full-time'), ('Part-time'), ('Contract');
