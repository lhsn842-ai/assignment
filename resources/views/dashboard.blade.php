<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100 flex flex-col items-center justify-start min-h-screen p-8">

<h1 class="text-3xl font-bold mb-6">Currency Converter</h1>

<div class="bg-white p-6 rounded shadow-md w-full max-w-3xl">
    <form id="exchangeForm" class="flex gap-4 items-center">
        <div>
            <label class="block text-gray-700 mb-1" for="from_currency">From</label>
            <select id="from_currency" class="border rounded p-2" disabled>
                <option value="EUR">EUR</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 mb-1" for="to_currency">To</label>
            <select id="to_currency" class="border rounded p-2">
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
                <option value="JPY">JPY</option>
                <option value="AUD">AUD</option>
                <option value="CAD">CAD</option>
                <option value="CHF">CHF</option>
                <option value="CNY">CNY</option>
                <option value="SEK">SEK</option>
                <option value="NZD">NZD</option>
                <option value="SGD">SGD</option>
                <option value="MXN">MXN</option>
                <option value="HKD">HKD</option>
                <option value="NOK">NOK</option>
                <option value="KRW">KRW</option>
                <option value="TRY">TRY</option>
                <option value="INR">INR</option>
                <option value="RUB">RUB</option>
                <option value="BRL">BRL</option>
                <option value="ZAR">ZAR</option>
                <option value="DKK">DKK</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 mb-1" for="amount">Amount</label>
            <input type="number" id="amount" class="border rounded p-2 w-24" placeholder="0" required>
        </div>

        <div class="flex flex-col justify-end">
            <button type="submit" class="bg-green-600 text-white p-2 rounded hover:bg-green-700">Convert</button>
        </div>
    </form>

    <div id="result" class="mt-6 text-gray-800"></div>
</div>
<script>
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '/login';
    }
</script>
</body>
</html>
