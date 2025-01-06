$(document).ready(function() {
    // Function to handle the approval of transactions
    $('form[name="approve_transaction"]').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var transactionId = form.find("input[name='transaction_id']").val();
        var status = form.find("select[name='status']").val();

        // Send AJAX request to update transaction status
        $.ajax({
            url: 'admin.php', // Same page (admin.php) to process the request
            method: 'POST',
            data: {
                approve_transaction: true,
                transaction_id: transactionId,
                status: status
            },
            success: function(response) {
                // Update the total values dynamically after a transaction update
                updateTotals();
                alert('Transaction updated successfully!');
            },
            error: function() {
                alert('Error updating transaction.');
            }
        });
    });

    // Function to fetch and update total deposits and withdrawals
    function updateTotals() {
        $.ajax({
            url: 'admin.php', // Same page (admin.php) to fetch updated totals
            method: 'GET',
            data: {
                fetch_totals: true
            },
            success: function(response) {
                // Parse the response and update the DOM
                var data = JSON.parse(response);
                $('#totalDeposits').text('$' + data.totalDeposits);
                $('#totalWithdrawals').text('$' + data.totalWithdrawals);
            }
        });
    }

    // Initially update the totals when the page loads
    updateTotals();
});
