<?php
require 'sessions.php';

// Update transaction status
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $pdo->prepare("UPDATE transactions SET status=? WHERE id=?")->execute([$status, $id]);
}

// Fetch transactions
$transactions = $pdo->query("SELECT t.*, u.name as user_name 
FROM transactions t
JOIN users u ON t.user_id=u.id
ORDER BY t.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transactions</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>Transactions</h1>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Gateway</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= $t['user_name'] ?></td>
                <td><?= $t['amount'] ?></td>
                <td><?= $t['currency'] ?></td>
                <td><?= $t['gateway'] ?></td>
                <td><?= $t['status'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <select name="status">
                            <option value="pending" <?= $t['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="completed" <?= $t['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="failed" <?= $t['status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="index.php">Back to Dashboard</a>
</body>

</html>