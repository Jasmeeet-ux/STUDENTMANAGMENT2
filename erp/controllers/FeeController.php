<?php
require_once __DIR__ . '/../models/Fee.php';

class FeeController {
    private $feeModel;

    public function __construct() {
        $this->feeModel = new Fee();
    }

    // Default route: Dashboard / Invoices List
    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $invoices = $this->feeModel->getInvoices($filters);
        $stats = $this->feeModel->getDashboardStats();
        
        require __DIR__ . '/../views/fees/index.php';
    }

    // Fee Structures List
    public function structures() {
        $filters = ['course_id' => $_GET['course_id'] ?? ''];
        $structures = $this->feeModel->getStructures($filters);
        $courses = $this->feeModel->getCourses();
        
        require __DIR__ . '/../views/fees/structures.php';
    }

    public function createStructure() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->feeModel->createStructure($_POST)) {
                $_SESSION['success'] = 'Fee Structure created and invoices generated.';
            } else {
                $_SESSION['error'] = 'Failed to create fee structure.';
            }
            header('Location: ?module=fees&action=structures');
            exit;
        }
    }

    // View Invoice Details & Pay
    public function invoice() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=fees');
            exit;
        }
        
        $invoice = $this->feeModel->getInvoiceById($id);
        $payments = $this->feeModel->getPaymentsByInvoice($id);
        
        require __DIR__ . '/../views/fees/invoice.php';
    }

    // Process Payment
    public function pay() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoice_id = $_POST['invoice_id'];
            if ($payment_id = $this->feeModel->addPayment($_POST)) {
                $_SESSION['success'] = 'Payment recorded successfully.';
                // Redirect to receipt
                header("Location: ?module=fees&action=receipt&id=$payment_id");
                exit;
            } else {
                $_SESSION['error'] = 'Failed to record payment.';
                header('Location: ?module=fees&action=invoice&id=' . $invoice_id);
                exit;
            }
        }
    }

    // Add Fine
    public function addFine() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoice_id = $_POST['invoice_id'];
            $amount = (float)$_POST['amount'];
            if ($this->feeModel->addFine($invoice_id, $amount)) {
                $_SESSION['success'] = 'Fine added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add fine.';
            }
            header('Location: ?module=fees&action=invoice&id=' . $invoice_id);
            exit;
        }
    }

    // Receipt View
    public function receipt() {
        $payment_id = $_GET['id'] ?? null;
        if (!$payment_id) {
            header('Location: ?module=fees');
            exit;
        }
        
        // Fetch Payment Details
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.*, i.student_id, u.name as student_name, u.reg_no, 
                   f.fee_type, f.academic_year, c.course_name 
            FROM erp_fee_payments p
            JOIN erp_fee_invoices i ON p.invoice_id = i.id
            JOIN users u ON i.student_id = u.id
            JOIN erp_fee_structures f ON i.fee_structure_id = f.id
            JOIN courses c ON f.course_id = c.id
            WHERE p.id = ?
        ");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $receipt = $stmt->get_result()->fetch_assoc();
        
        require __DIR__ . '/../views/fees/receipt.php';
    }
}
