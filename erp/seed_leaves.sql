INSERT IGNORE INTO erp_attendance (section_id, student_id, date, status, remarks) SELECT section_id, student_id, CURDATE(), 'Leave', 'Medical' FROM section_students LIMIT 25;
