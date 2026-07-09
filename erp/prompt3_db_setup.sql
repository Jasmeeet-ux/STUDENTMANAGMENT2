-- Update Departments Table
ALTER TABLE departments ADD COLUMN IF NOT EXISTS description TEXT AFTER name;
ALTER TABLE departments ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL;

-- Update Courses Table (Linking to Department)
ALTER TABLE courses ADD COLUMN IF NOT EXISTS department_id INT AFTER category_id;
-- Ignore foreign key constraint errors if it exists
SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE courses ADD CONSTRAINT fk_course_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;
SET FOREIGN_KEY_CHECKS=1;

-- Create Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    course_id INT,
    teacher_id INT,
    credits INT DEFAULT 3,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- Insert Dummy Data for Departments if empty
INSERT IGNORE INTO departments (id, name, description, status) VALUES 
(1, 'Computer Science', 'Department of Computer Science and Engineering', 'active'),
(2, 'Business Administration', 'Department of Management and Business', 'active'),
(3, 'Mechanical Engineering', 'Department of Mechanical and Auto', 'active');
