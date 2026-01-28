<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Preprod Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h1 class="text-xl font-semibold text-gray-800 mb-2">Preprod Environment</h1>
        <p class="text-sm text-gray-500 mb-6">Enter password to access</p>
        
        <form method="POST" action="/preprod-auth">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->url() }}">
            <input 
                type="password" 
                name="preprod_password" 
                placeholder="Password"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-4"
                autofocus
            >
            <button 
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
                Access
            </button>
        </form>
    </div>
</body>
</html>