CREATE TABLE IF NOT EXISTS erp_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    due_date DATETIME NOT NULL,
    max_marks DECIMAL(5,2) DEFAULT 10.00,
    status ENUM('Active', 'Closed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS erp_assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_text TEXT NULL,
    file_path VARCHAR(255) NULL,
    marks_obtained DECIMAL(5,2) NULL,
    feedback TEXT NULL,
    status ENUM('Pending', 'Submitted', 'Graded', 'Late') DEFAULT 'Pending',
    submitted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES erp_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (assignment_id, student_id)
);

-- Dummy Data
INSERT IGNORE INTO erp_assignments (id, title, description, class_id, subject_id, teacher_id, due_date, max_marks, status) 
VALUES (1, 'Write an Essay on Software Engineering', 'Submit a 500-word essay covering SDLC phases.', 1, 1, 1, DATE_ADD(NOW(), INTERVAL 7 DAY), 20.00, 'Active');
