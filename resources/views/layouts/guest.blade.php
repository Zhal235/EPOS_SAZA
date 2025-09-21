<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EPOS SAZA') }} - Point of Sale System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .gradient-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .glassmorphism {
                background: rgba(255, 255, 255, 0.25);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.18);
            }
            .login-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen gradient-bg flex items-center justify-center p-4">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
            </div>
            
            <div class="w-full max-w-md relative z-10">
                <!-- Logo Section -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-xl shadow-lg mb-4">
                        <i class="fas fa-cash-register text-3xl text-indigo-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">EPOS SAZA</h1>
                    <p class="text-indigo-100">Point of Sale System</p>
                </div>

                <!-- Login Card -->
                <div class="login-card rounded-2xl shadow-2xl p-8">
                    {{ $slot }}
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-6">
                    <p class="text-indigo-100 text-sm">
                        © {{ date('Y') }} EPOS SAZA. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
