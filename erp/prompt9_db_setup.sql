CREATE TABLE IF NOT EXISTS erp_leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('student', 'teacher') NOT NULL,
    student_id INT NULL,
    teacher_id INT NULL,
    leave_type VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    admin_remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- Dummy Data
INSERT IGNORE INTO erp_leaves (user_type, student_id, leave_type, start_date, end_date, reason, status) 
VALUES ('student', 1, 'Sick Leave', DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Viral fever and doctor prescribed rest.', 'Pending');

INSERT IGNORE INTO erp_leaves (user_type, teacher_id, leave_type, start_date, end_date, reason, status) 
VALUES ('teacher', 1, 'Casual Leave', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'Attending a family wedding.', 'Approved');
