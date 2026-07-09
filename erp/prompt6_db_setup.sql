CREATE TABLE IF NOT EXISTS erp_exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    exam_type ENUM('Mid Semester', 'Final Semester', 'Practical Exam', 'Other') NOT NULL,
    class_id INT NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('Upcoming', 'Ongoing', 'Completed') DEFAULT 'Upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS erp_exam_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    exam_date DATE NULL,
    internal_max_marks DECIMAL(5,2) DEFAULT 0.00,
    external_max_marks DECIMAL(5,2) DEFAULT 100.00,
    passing_marks DECIMAL(5,2) DEFAULT 40.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES erp_exams(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY (exam_id, subject_id)
);

CREATE TABLE IF NOT EXISTS erp_exam_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_subject_id INT NOT NULL,
    student_id INT NOT NULL,
    internal_marks DECIMAL(5,2) DEFAULT NULL,
    external_marks DECIMAL(5,2) DEFAULT NULL,
    total_marks DECIMAL(5,2) DEFAULT NULL,
    grade VARCHAR(5) NULL,
    is_absent BOOLEAN DEFAULT FALSE,
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_subject_id) REFERENCES erp_exam_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (exam_subject_id, student_id)
);
