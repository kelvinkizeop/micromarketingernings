
<?php
session_start();  

if (!isset($_SESSION['form_submitted'])) {
    $_SESSION['form_submitted'] = false;
}

include('includes/db.php');  // Use the new db.php for PostgreSQL

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  

    // Get user details
    $sql = "SELECT * FROM users WHERE id = $1";
    $result = pg_query_params($conn, $sql, array($user_id));
    $user = pg_fetch_assoc($result); 
    $_SESSION['username'] = $user['username']; 

    // Get referral code
    if (empty($user['referral_code'])) {
        $referral_code = "REF" . $user_id; 
        $update_sql = "UPDATE users SET referral_code = $1 WHERE id = $2";
        pg_query_params($conn, $update_sql, array($referral_code, $user_id));
    } else {
        $referral_code = $user['referral_code']; 
    }

    $referral_link = "https://micromarketingearnings.com/referral.php?code=" . $referral_code;

} else {
    $user = null;
    $referral_link = "#";
}


// Fetch the most recent transactions for PostgreSQL
$sql = "SELECT * FROM transactions WHERE user_id = $1 ORDER BY transaction_date DESC";
$stmt = pg_query_params($conn, $sql, array($user_id));  // Execute the query with parameter binding

if ($stmt) {
    $result = pg_fetch_all($stmt);  // Get the result set as an associative array
} else {
    echo "<script>alert('Error fetching transactions.');</script>";
}
// Handle Deposit
if (isset($_POST['deposit_now'])) {
    $deposit_amount = $_POST['deposit_amount'];
    $payment_method = $_POST['payment_method'];

    if ($deposit_amount > 0 && !empty($payment_method)) {
        $transaction_sql = "INSERT INTO transactions (user_id, transaction_type, amount, payment_method, status) 
                            VALUES ($1, 'Deposit', $2, $3, 'pending')";
        pg_query_params($conn, $transaction_sql, array($user_id, $deposit_amount, $payment_method));

        echo "<script>alert('Deposit Pending approval.');</script>";
    } else {
        echo "<script>alert('Invalid deposit amount or payment method!');</script>";
    }
}

// Handle Withdrawal
if (isset($_POST['withdraw_now'])) {
    $withdraw_amount = $_POST['withdraw_amount'];
    $withdraw_method = $_POST['withdraw_method'];
    $wallet_address = $_POST['wallet_address'];

    if ($withdraw_amount > 0 && !empty($withdraw_method) && !empty($wallet_address)) {
        if ($user['account_balance'] >= $withdraw_amount) {
            $transaction_sql = "INSERT INTO transactions (user_id, transaction_type, amount, payment_method, wallet_address, status) 
                                 VALUES ($1, 'Withdrawal', $2, $3, $4, 'Pending')";
            pg_query_params($conn, $transaction_sql, array($user_id, $withdraw_amount, $withdraw_method, $wallet_address));

            $update_balance_sql = "UPDATE users SET account_balance = account_balance - $1 WHERE id = $2";
            pg_query_params($conn, $update_balance_sql, array($withdraw_amount, $user_id));

            echo "<script>alert('Withdrawal successful! Pending approval.');</script>";
        } else {
            echo "<script>alert('Insufficient balance!');</script>";
        }
    } else {
        echo "<script>alert('Invalid withdrawal details!');</script>";
    }
}

// Account Balance 
$sql = "SELECT account_balance FROM users WHERE id = $1";
$stmt = pg_query_params($conn, $sql, array($_SESSION['user_id']));
$result = pg_fetch_assoc($stmt);
$user = $result;

// Transaction Table 
$sql = "SELECT * FROM transactions WHERE user_id = $1 ORDER BY transaction_date DESC LIMIT 5";
$stmt = pg_query_params($conn, $sql, array($user_id));  
$result = pg_fetch_all($stmt);

// Investment Plans
$plan_sql = "SELECT * FROM investment_plans";
$plan_stmt = pg_query($conn, $plan_sql);
$plans_result = pg_fetch_all($plan_stmt);

// Reinvest/Invest Section
if (isset($_POST['invest_now']) || isset($_POST['reinvest_now'])) {
    $investment_amount = $_POST['investment_amount'];
    $selected_plan = $_POST['reinvestment_plan']; 

    $plan_sql = "SELECT * FROM investment_plans WHERE plan_name = $1";
    $plan_stmt = pg_query_params($conn, $plan_sql, array($selected_plan)); 
    $plan_result = pg_fetch_assoc($plan_stmt);

    if ($investment_amount >= $plan_result['min_investment'] && $investment_amount <= $plan_result['max_investment']) {
        if ($user['account_balance'] >= $investment_amount) {
            $transaction_type = isset($_POST['invest_now']) ? 'Invest' : (isset($_POST['reinvest_now']) ? 'Reinvest' : '');

            $transaction_sql = "INSERT INTO transactions (user_id, transaction_type, amount, payment_method, status, transaction_date)
                                 VALUES ($1, $2, $3, 'N/A', 'Completed', NOW())"; 
            pg_query_params($conn, $transaction_sql, array($user_id, $transaction_type, $investment_amount)); 

            $update_balance_sql = "UPDATE users SET account_balance = account_balance - $1 WHERE id = $2";
            pg_query_params($conn, $update_balance_sql, array($investment_amount, $user_id));

            $investment_sql = "INSERT INTO user_investments (user_id, plan_id, amount_invested, start_date, end_date, status)
                               VALUES ($1, $2, $3, NOW(), NOW() + INTERVAL '$4 days', 'Active')";
            pg_query_params($conn, $investment_sql, array($user_id, $plan_result['id'], $investment_amount, $plan_result['duration_days']));

            $_SESSION['form_submitted'] = true;  

            // Redirect to the same page to prevent re-submission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

            echo "<script>alert('Investment successful!');</script>";
        } else {
            echo "<script>alert('Insufficient balance!');</script>";
        }
    } else {
        echo "<script>alert('Investment amount does not meet the required range!');</script>";
    }
}

// For updating current investment card
$current_investment_sql = "
    SELECT 
        ui.amount_invested, ui.start_date, ui.end_date, ui.status, 
        ip.plan_name, ip.daily_profit_percentage, ip.duration_days
    FROM user_investments ui
    JOIN investment_plans ip ON ui.plan_id = ip.id
    WHERE ui.user_id = $1 AND ui.status = 'Active'
    ORDER BY ui.start_date DESC LIMIT 1
";
$current_investment_stmt = pg_query_params($conn, $current_investment_sql, array($user_id));
$current_investment = pg_fetch_assoc($current_investment_stmt);

if ($current_investment) {
    $plan_name = $current_investment['plan_name'];
    $amount_invested = $current_investment['amount_invested'];
    $start_date = $current_investment['start_date'];
    $end_date = $current_investment['end_date'];
    $status = $current_investment['status'];
    $daily_profit_percentage = $current_investment['daily_profit_percentage'];
} else {
    $plan_name = "No Active Plan";
    $amount_invested = 0;
    $start_date = "-";
    $end_date = "-";
    $status = "None";
    $daily_profit_percentage = 0;
}

pg_close($conn);  
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard-MicroMarketingEarnings</title>
    <link rel="stylesheet" href="css/dashboard.css">

</head>
<body>

    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>My Dashboard</h2>
            <ul>
                <li><a href="#overview">Portfolio</a></li>
                <li><a href="#deposit" onclick="openDepositOverlay()">Deposit</a></li>
                <li><a href="#withdraw" onclick="openWithdrawOverlay()">Withdraw</a></li>
                <li><a href="/backend/transaction-history.php">Transaction History</a></li>
                <li><a href="#invest-reinvest" onclick="openInvestReinvestForm()">Invest & Reinvest</a></li>
                <li><a href="#settings">Settings</a></li>
                <button onclick="toggleDarkMode()">Switch Dark Mode</button>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="content">
            <div class="header">
                <h1> Welcome To Your Dashboard </h1>
                <a href="/backend/logout.php"><button>Logout</button></a>
            </div>

            <!-- Overview Section -->
            <section id="overview" class="overview">
                <div class="card">
                    <h3>Account Balance</h3>
                    <p>
                    <?php
                if ($user) {
               
                    echo "$" . number_format($user['account_balance'], 2); 
                } else {
                   
                    echo "$0.00";
                }
                                 ?>
                    </p>
                </div>

                <div class="card">
                    <h3>Referral Link</h3>
                    <p>   <?php
               
                if ($user) {
                   
                    echo "<a href='" . $referral_link . "' target='_blank'>" . $referral_link . "</a>";
                } else {
                  
                    echo "Please log in to access your referral link.";
                }
               ?>
               </p>
            </div>


    <div class="card">
                    <h3>Current Investment</h3>

                <?php if ($current_investment): ?>
                       <p><strong>Plan Name:</strong> <?php echo htmlspecialchars($plan_name); ?></p>
                       <p><strong>Amount Invested:</strong> $<?php echo number_format($amount_invested, 2); ?></p>
                       <p><strong>Start Date:</strong> <?php echo htmlspecialchars($start_date); ?></p>
                       <p><strong>End Date:</strong> <?php echo htmlspecialchars($end_date); ?></p>
                      <p><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></p>
                      <p><strong>Estimated Profit:</strong> <?php echo htmlspecialchars($daily_profit_percentage); ?>%</p>
        <?php else: ?>
            <p><strong>No Active Plan Found</strong></p>
             <p>You currently do not have an active investment plan. Start investing today!</p>
        <?php endif; ?>

    </div>


                <div class="card">
                    <h3>Company Information Board</h3>
                    <p> WE WILL PASS INFORMATION TO ALL USERS HERE!! </p>
                </div>
            </section>
        </main>
    </div>

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

    <section id="deposit-overlay" class="overlay hidden">
    <div class="overlay-content">
        <h2>Deposit Funds</h2>
        <form method="POST">
            <label for="deposit_amount">Enter Deposit Amount:</label>
            <input type="number" id="deposit_amount" name="deposit_amount" placeholder="Enter amount to deposit" required>

            <label for="payment_method">Select Payment Method:</label>
            <select id="payment_method" name="payment_method" required onchange="showPaymentAddress()">
                <option value="">Select Payment Method</option>
                <option value="BTC">BTC</option>
                <option value="TRX">TRX</option>
                <option value="LTC">LTC</option>
                <option value="ETH">ETH</option>
            </select>

            <div id="payment_address" style="margin-top: 10px;">
            </div>

            <button type="submit" name="deposit_now" class="button">Deposit Now</button>
        </form>
        <button onclick="closeDepositOverlay()" class="back">Go Back</button>
    </div>
</section>


    <section id="withdraw-overlay" class="overlay hidden">
    <div class="overlay-content">
        <h2>Withdraw Funds</h2>
        <form method="POST">
            <label for="withdraw_amount">Enter Withdrawal Amount:</label>
            <input type="number" id="withdraw_amount" name="withdraw_amount" placeholder="Enter amount to withdraw" required>

            <label for="withdraw_method">Select Withdrawal Method:</label>
            <select id="withdraw_method" name="withdraw_method" required>
                <option value="">Select Withdrawal Method</option>
                <option value="BTC">BTC</option>
                <option value="TRX">TRX</option>
                <option value="LTC">LTC</option>
                <option value="ETH">ETH</option>
            </select>

            <label for="wallet_address">Enter Your Wallet Address:</label>
            <input type="text" id="wallet_address" name="wallet_address" placeholder="Enter your wallet address" required>

            <button type="submit" name="withdraw_now" class="button">Withdraw Now</button>
        </form>
        <button onclick="closeWithdrawOverlay()" class="back">Go Back</button>
    </div>
</section>


<section id="invest-reinvest-overlay" class="overlay hidden">
    <div class="overlay-content ">
        <h2>Invest & Reinvest</h2>
    
        <form method="POST">
            <label for="investment_amount">Enter Investment Amount:</label>
            <input type="number" id="investment_amount" name="investment_amount" placeholder="Enter amount to invest/reinvest" required />

            <label for="reinvestment_plan">Select Plan:</label>
            <select id="reinvestment_plan" name="reinvestment_plan" class="invest-dropdown">
            <?php while ($plan = $plans_result->fetch_assoc()): ?>
        <option value="<?php echo $plan['plan_name']; ?>">
            <?php echo $plan['plan_name']; ?> - 
            Min: $<?php echo number_format($plan['min_investment'], 2); ?>, 
            Max: $<?php echo number_format($plan['max_investment'], 2); ?>
        </option>
    <?php endwhile; ?>
            </select>
            <br><br>
            <button type="submit" name="invest_now" class="button1">Invest Now</button>
            <button type="submit" name="reinvest_now"class="button1">>Reinvest</button>
        </form>
        <button onclick="closeInvestReinvestOverlay()" class="back">Go Back</button>
    </div>
</section>

 <script src="js/dashboard.js"></script>
</body>
</html>





