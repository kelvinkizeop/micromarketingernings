<?php
session_start();
include('includes/db.php');
if (!isset($_SESSION['form_submitted'])) {
    $_SESSION['form_submitted'] = false;
}


// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: /backend/login.php');
    exit;
}

// Fetch admin-specific data
$totalDeposits = $conn->query("SELECT SUM(amount) FROM transactions WHERE transaction_type='Deposit' AND status='Completed'")->fetch_row()[0] ?? 0;
$totalWithdrawals = $conn->query("SELECT SUM(amount) FROM transactions WHERE transaction_type='Withdrawal' AND status='Completed'")->fetch_row()[0] ?? 0;
$activeUsers = $conn->query("SELECT COUNT(*) FROM users WHERE account_balance > 0")->fetch_row()[0] ?? 0;

// Approve transactions via AJAX
if (isset($_POST['approve_transaction'])) {
    $transaction_id = $_POST['transaction_id'];
    $status = $_POST['status'];
    $conn->query("UPDATE transactions SET status='$status' WHERE id=$transaction_id");

    // After updating the transaction, return the updated totals for deposits and withdrawals
    if (isset($_POST['fetch_totals'])) {
        $totalDeposits = $conn->query("SELECT SUM(amount) FROM transactions WHERE transaction_type='Deposit' AND status='Completed'")->fetch_row()[0] ?? 0;
        $totalWithdrawals = $conn->query("SELECT SUM(amount) FROM transactions WHERE transaction_type='Withdrawal' AND status='Completed'")->fetch_row()[0] ?? 0;
        echo json_encode([
            'totalDeposits' => number_format($totalDeposits, 2),
            'totalWithdrawals' => number_format($totalWithdrawals, 2)
        ]);
        exit;
          // Redirect to the same page to prevent re-submission
          header("Location: " . $_SERVER['PHP_SELF']);
          exit();
    }
}

// Update user balance
if (isset($_POST['update_balance'])) {
    $user_id = $_POST['user_id'];
    $new_balance = $_POST['new_balance'];
    $conn->query("UPDATE users SET account_balance=$new_balance WHERE id=$user_id");
    echo "<script>alert('User balance updated successfully!');</script>";
}

// Fetch pending transactions
$pendingTransactions = $conn->query("SELECT * FROM transactions WHERE status='Pending'");
$allUsers = $conn->query("SELECT id, username, account_balance FROM users");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="#overview">Overview</a></li>
                <li><a href="#transactions">Approve Transactions</a></li>
                <li><a href="#users">Manage Users</a></li>
                <li><a href="/backend/logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="content">
            <!-- Overview Section -->
            <section id="overview">
                <h2>Overview</h2>
                <div class="stats">
                    <div class="stat">
                        <h3>Total Deposits</h3>
                        <p>$<?php echo number_format($totalDeposits, 2); ?></p>
                    </div>
                    <div class="stat">
                        <h3>Total Withdrawals</h3>
                        <p>$<?php echo number_format($totalWithdrawals, 2); ?></p>
                    </div>
                    <div class="stat">
                        <h3>Active Users</h3>
                        <p><?php echo $activeUsers; ?></p>
                    </div>
                </div>
            </section>

            <!-- Approve Transactions Section -->
            <section id="transactions">
                <h2>Approve Transactions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $pendingTransactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $transaction['id']; ?></td>
                                <td><?php echo $transaction['user_id']; ?></td>
                                <td><?php echo $transaction['transaction_type']; ?></td>
                                <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td><?php echo $transaction['status']; ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                        <select name="status">
                                            <option value="Completed">Approve</option>
                                            <option value="Rejected">Reject</option>
                                        </select>
                                        <button type="submit" name="approve_transaction">Submit</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Manage Users Section -->
            <section id="users">
                <h2>Manage Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $allUsers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td>$<?php echo number_format($user['account_balance'], 2); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="number" name="new_balance" placeholder="New Balance" required>
                                        <button type="submit" name="update_balance">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <script src="js/admin.js"></script>
</body>
</html>
