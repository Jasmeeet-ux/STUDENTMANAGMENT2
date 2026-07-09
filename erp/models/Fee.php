<?php
require_once __DIR__ . '/../Database.php';

class Fee {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCourses() {
        $result = $this->db->query("SELECT * FROM courses WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // --- Fee Structures ---

    public function getStructures($filters = []) {
        $query = "
            SELECT f.*, c.course_name, c.course_code 
            FROM erp_fee_structures f
            LEFT JOIN courses c ON f.course_id = c.id
            WHERE f.deleted_at IS NULL
        ";
        
        if (!empty($filters['course_id'])) {
            $course_id = (int)$filters['course_id'];
            $query .= " AND f.course_id = $course_id";
        }
        $query .= " ORDER BY f.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getStructureById($id) {
        $stmt = $this->db->prepare("
            SELECT f.*, c.course_name 
            FROM erp_fee_structures f
            LEFT JOIN courses c ON f.course_id = c.id
            WHERE f.id = ? AND f.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createStructure($data) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO erp_fee_structures (
                    course_id, fee_type, academic_year, amount, due_date
                ) VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "issds", 
                $data['course_id'], $data['fee_type'], $data['academic_year'], 
                $data['amount'], $data['due_date']
            );
            $stmt->execute();
            $fee_id = $this->db->insert_id;
            
            // Auto-generate invoices for all students in this course
            $stmt2 = $this->db->prepare("
                SELECT DISTINCT u.id 
                FROM users u
                JOIN section_students ss ON u.id = ss.student_id
                JOIN sections sec ON ss.section_id = sec.id
                JOIN classes cls ON sec.class_id = cls.id
                WHERE cls.course_id = ? AND u.role = 'student'
            ");
            $stmt2->bind_param("i", $data['course_id']);
            $stmt2->execute();
            $students = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (!empty($students)) {
                $invStmt = $this->db->prepare("
                    INSERT INTO erp_fee_invoices (student_id, fee_structure_id, total_amount) 
                    VALUES (?, ?, ?)
                ");
                foreach ($students as $st) {
                    $invStmt->bind_param("iid", $st['id'], $fee_id, $data['amount']);
                    $invStmt->execute();
                }
            }
            
            $this->db->commit();
            return $fee_id;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    // --- Invoices ---
    
    public function getInvoices($filters = []) {
        $query = "
            SELECT i.*, u.name as student_name, u.reg_no, 
                   f.fee_type, f.due_date, c.course_name
            FROM erp_fee_invoices i
            JOIN users u ON i.student_id = u.id
            JOIN erp_fee_structures f ON i.fee_structure_id = f.id
            JOIN courses c ON f.course_id = c.id
            WHERE 1=1
        ";
        
        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (u.name LIKE '%$search%' OR u.reg_no LIKE '%$search%')";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND i.status = '$status'";
        }
        
        $query .= " ORDER BY i.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getInvoiceById($id) {
        $stmt = $this->db->prepare("
            SELECT i.*, u.name as student_name, u.reg_no, 
                   f.fee_type, f.due_date, c.course_name
            FROM erp_fee_invoices i
            JOIN users u ON i.student_id = u.id
            JOIN erp_fee_structures f ON i.fee_structure_id = f.id
            JOIN courses c ON f.course_id = c.id
            WHERE i.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    // --- Payments ---
    
    public function getPaymentsByInvoice($invoice_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM erp_fee_payments WHERE invoice_id = ? ORDER BY payment_date DESC
        ");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function addPayment($data) {
        $this->db->begin_transaction();
        try {
            // Add Payment
            $stmt = $this->db->prepare("
                INSERT INTO erp_fee_payments (invoice_id, payment_date, amount, payment_method, reference_no, remarks)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $date = date('Y-m-d H:i:s');
            $stmt->bind_param("isdsss", 
                $data['invoice_id'], $date, $data['amount'], 
                $data['payment_method'], $data['reference_no'], $data['remarks']
            );
            $stmt->execute();
            $payment_id = $this->db->insert_id;
            
            // Update Invoice Paid Amount & Status
            $inv = $this->getInvoiceById($data['invoice_id']);
            $new_paid = $inv['paid_amount'] + $data['amount'];
            $total_due = $inv['total_amount'] + $inv['fine_amount'];
            
            $status = 'Partial';
            if ($new_paid >= $total_due) {
                $status = 'Paid';
            }
            
            $upd = $this->db->prepare("UPDATE erp_fee_invoices SET paid_amount = ?, status = ? WHERE id = ?");
            $upd->bind_param("dsi", $new_paid, $status, $data['invoice_id']);
            $upd->execute();
            
            $this->db->commit();
            return $payment_id;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    public function addFine($invoice_id, $amount) {
        $inv = $this->getInvoiceById($invoice_id);
        $new_fine = $inv['fine_amount'] + $amount;
        $total_due = $inv['total_amount'] + $new_fine;
        
        $status = 'Partial';
        if ($inv['paid_amount'] >= $total_due) {
            $status = 'Paid';
        } else if ($inv['paid_amount'] == 0) {
            $status = 'Pending';
        }
        
        $stmt = $this->db->prepare("UPDATE erp_fee_invoices SET fine_amount = ?, status = ? WHERE id = ?");
        $stmt->bind_param("dsi", $new_fine, $status, $invoice_id);
        return $stmt->execute();
    }

    public function getDashboardStats() {
        $stats = [
            'total_invoices' => 0,
            'total_collected' => 0.00,
            'total_pending' => 0.00,
            'fine_collected' => 0.00
        ];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_fee_invoices");
        $stats['total_invoices'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT SUM(paid_amount) as collected FROM erp_fee_invoices");
        $stats['total_collected'] = $res->fetch_assoc()['collected'] ?? 0;
        
        $res = $this->db->query("SELECT SUM((total_amount + fine_amount) - paid_amount) as pending FROM erp_fee_invoices WHERE status != 'Paid'");
        $stats['total_pending'] = $res->fetch_assoc()['pending'] ?? 0;
        
        $res = $this->db->query("SELECT SUM(fine_amount) as fines FROM erp_fee_invoices");
        $stats['fine_collected'] = $res->fetch_assoc()['fines'] ?? 0;

        return $stats;
    }
}
