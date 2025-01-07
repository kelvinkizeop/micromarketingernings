<?php
include('includes/db.php');
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query
    $sql = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC";
    $stmt = $conn->prepare($sql);

    // Bind the user_id parameter
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optionally, you can loop through $result if needed:
    // foreach ($result as $transaction) {
    //     // Process each transaction here
    // }
} else {
    echo "You must be logged in to view this page.";
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction History</title>
    <link rel="stylesheet" href="css/dashboard.css">

</head>
<body> 
<header class="transactionhistory">
    <div>
    <h1>TRANSACTION HISTORY</h1>
    </div>
</header>

<section id="transactions" class="transactions">
        <table>
            <thead>
                <tr>
                       <th>Transaction ID</th>
                        <th>Transaction Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($transaction = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $transaction['id']; ?></td>
                        <td><?php echo $transaction['transaction_type']; ?></td>
                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td>
                            <?php
                        
                if ($transaction['transaction_type'] == 'Invest' || $transaction['transaction_type'] == 'Reinvest') {
                    $plan_query = "SELECT plan_name FROM investment_plans WHERE id = (SELECT plan_id FROM user_investments WHERE transaction_id = ? LIMIT 1)";
                    $stmt = $conn->prepare($plan_query);
                    $stmt->bind_param("i", $transaction['id']);
                    $stmt->execute();
                    $stmt->bind_result($plan_name);
                    $stmt->fetch();
                    echo $transaction['plan_name'];  
                } else {
                   
                    if ($transaction['transaction_type'] == 'Deposit') {
                        echo $transaction['payment_method'];
                    } else {
                        echo $transaction['wallet_address'];
                    }
                }
                ?>
                        </td>
                        <td><?php echo $transaction['transaction_date']; ?></td>
                        <td><?php echo $transaction['status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </section>
</body>
