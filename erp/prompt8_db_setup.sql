CREATE TABLE IF NOT EXISTS erp_fee_structures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    fee_type VARCHAR(255) NOT NULL,
    academic_year VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS erp_fee_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_structure_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Pending', 'Partial', 'Paid') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_structure_id) REFERENCES erp_fee_structures(id) ON DELETE CASCADE,
    UNIQUE KEY (student_id, fee_structure_id)
);

CREATE TABLE IF NOT EXISTS erp_fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_date DATETIME NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'Card', 'Bank Transfer', 'Online') DEFAULT 'Cash',
    reference_no VARCHAR(255) NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES erp_fee_invoices(id) ON DELETE CASCADE
);

-- Dummy Data
INSERT IGNORE INTO erp_fee_structures (id, course_id, fee_type, academic_year, amount, due_date) 
VALUES (1, 1, 'Tuition Fee - Semester 1', '2026-2027', 15000.00, '2026-09-01');

INSERT IGNORE INTO erp_fee_structures (id, course_id, fee_type, academic_year, amount, due_date) 
VALUES (2, 1, 'Library Fee', '2026-2027', 2000.00, '2026-09-01');
