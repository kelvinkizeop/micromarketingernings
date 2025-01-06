// Fetch data from the Java Servlet
fetch('http://localhost:8080/investment-dashboard/DashboardServlet?user_id=1')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('investment-data');
        data.forEach(item => {
            const div = document.createElement('div');
            div.innerHTML = `Plan: ${item.plan_name}, Profit: ${item.profit}, Duration: ${item.duration}, Amount: ${item.amount}`;
            container.appendChild(div);
        });
    })
    .catch(error => console.error('Error fetching data:', error));

// Toggle Dark Mode
function toggleDarkMode() {
    const body = document.body;
    const button = document.querySelector("button");

    body.classList.toggle("dark-mode");
    if (body.classList.contains("dark-mode")) {
        button.textContent = "Switch to Normal Mode";
    } else {
        button.textContent = "Switch to Dark Mode";
    }
}


//FOR CUSTOM ALERT
function showCustomAlert(message) {
    const alertBox = document.getElementById('custom-alert');
    const messageBox = document.getElementById('custom-alert-message');
    messageBox.textContent = message;
    alertBox.classList.remove('hidden');
}

function closeCustomAlert() {
    const alertBox = document.getElementById('custom-alert');
    alertBox.classList.add('hidden');
}
//for reinvest and invest
function openInvestReinvestForm() {
    document.getElementById("invest-reinvest-overlay").classList.remove("hidden");
}

function closeInvestReinvestOverlay() {
    document.getElementById("invest-reinvest-overlay").classList.add("hidden");
}

//TO SHOW OPTION FOR DEPOSIT 
function showPaymentAddress() {
    const method = document.getElementById('payment_method').value;
    const addressDiv = document.getElementById('payment_address');

    let address = '';
    if (method === 'BTC') {
        address = 'Copy Deposit Address: <strong>bc1qt7tvx2shv9g2rtscrayy5ukr65lxt7mchrq7h7</strong>';
    } else if (method === 'TRX') {
        address = 'Copy Deposit Address: <strong>TK1HYcjPw6d7F7nnvnadrJpFPHzJqFwksF</strong>';
    } else if (method === 'LTC') {
        address = 'Copy Deposit Address: <strong>ltc1q49a58hp9nax0z9v8cppnelljdyg6442e9l9a7m</strong>';
    } else if (method === 'ETH') {
        address = 'Copy Deposit Address: <strong>0xd7795D08D5dea0C7daFdC613E88120A1Ff31d925</strong>';
    } else {
        address = '';
    }

    addressDiv.innerHTML = address;
}
function openDepositOverlay() {
    document.getElementById('deposit-overlay').classList.remove('hidden');
}

function openWithdrawOverlay() {
    document.getElementById('withdraw-overlay').classList.remove('hidden');
}

function closeDepositOverlay() {
    document.getElementById('deposit-overlay').classList.add('hidden');
}

function closeWithdrawOverlay() {
    document.getElementById('withdraw-overlay').classList.add('hidden');
}