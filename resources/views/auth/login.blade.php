<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUMBS - Login</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#FAF8F3]">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md">
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-lg bg-[#D4A574] flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl font-bold text-white">◆</span>
                </div>
                <h1 class="text-3xl font-bold text-[#3E2723]">CRUMBS</h1>
                <p class="text-[#8B6F47] mt-2">Pastry & Bakery Inventory System</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-xl border border-[#E8DCC8] p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-[#3E2723] mb-6">Welcome Back</h2>

                <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#3E2723] mb-2">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="admin@crumbs.test"
                            class="w-full px-4 py-2 rounded-lg border border-[#E8DCC8] bg-white text-[#3E2723] placeholder-[#8B6F47] focus:outline-none focus:border-[#D4A574] focus:ring-2 focus:ring-[#D4A574] focus:ring-opacity-20 transition"
                            required
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-[#3E2723] mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            value="password"
                            class="w-full px-4 py-2 rounded-lg border border-[#E8DCC8] bg-white text-[#3E2723] placeholder-[#8B6F47] focus:outline-none focus:border-[#D4A574] focus:ring-2 focus:ring-[#D4A574] focus:ring-opacity-20 transition"
                            required
                        >
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember" 
                            class="rounded border-[#E8DCC8] text-[#D4A574] focus:ring-[#D4A574]"
                        >
                        <label for="remember" class="ml-2 text-sm text-[#8B6F47]">Remember me</label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full px-6 py-3 rounded-lg bg-[#D4A574] text-white font-semibold hover:bg-opacity-90 transition mt-6"
                    >
                        Login
                    </button>
                </form>

                <!-- Demo Credentials -->
                {{-- <div class="mt-6 pt-6 border-t border-[#E8DCC8]">
                    <p class="text-xs text-center text-[#8B6F47] mb-2">Demo Credentials:</p>
                    <div class="bg-[#FAF8F3] p-3 rounded text-xs space-y-1">
                        <p class="text-[#3E2723]"><strong>Email:</strong> admin@crumbs.test</p>
                        <p class="text-[#3E2723]"><strong>Password:</strong> password</p>
                    </div>
                </div> --}}
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-[#8B6F47]">
                    Where every gram counts and every batch matters 🍪
                </p>
            </div>
        </div>
    </div>
</body>
</html>
