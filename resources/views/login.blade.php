<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
    <form id="loginForm" class="space-y-4">
        <div>
            <label for="email" class="block text-gray-700">Email</label>
            <input type="email" id="email" name="email" class="w-full border p-2 rounded" required>
        </div>
        <div>
            <label for="password" class="block text-gray-700">Password</label>
            <input type="password" id="password" name="password" class="w-full border p-2 rounded" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Login</button>
    </form>
    <p id="errorMsg" class="text-red-500 mt-4 hidden"></p>
</div>
</body>
</html>
