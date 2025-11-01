<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;
use function Livewire\Volt\state;

layout('layouts.guest');

form(LoginForm::class);
state(['showPassword' => false]);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

$togglePassword = function () {
    $this->showPassword = !$this->showPassword;
};

?>

<div x-data="{ showPassword: @entangle('showPassword') }">
    <!-- Header -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back!</h2>
        <p class="text-gray-600">Sign in to access your EPOS dashboard</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-indigo-500"></i>Email Address
            </label>
            <div class="relative">
                <input wire:model="form.email" id="email" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 pl-12" 
                       type="email" name="email" placeholder="Enter your email" required autofocus autocomplete="username">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-lock mr-2 text-indigo-500"></i>Password
            </label>
            <div class="relative">
                <input wire:model="form.password" id="password" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 pl-12 pr-12"
                       :type="showPassword ? 'text' : 'password'"
                       name="password" placeholder="Enter your password" required autocomplete="current-password">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-key text-gray-400"></i>
                </div>
                <button type="button" wire:click="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'" class="text-gray-400 hover:text-gray-600"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" 
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 focus:ring-offset-0" name="remember">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
            
            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:text-indigo-500 transition-colors duration-200" 
                   href="{{ route('password.request') }}" wire:navigate>
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit" 
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform hover:scale-[1.02] transition-all duration-200 shadow-lg">
            <span wire:loading.remove>
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In to Dashboard
            </span>
            <span wire:loading>
                <i class="fas fa-spinner fa-spin mr-2"></i>Signing in...
            </span>
        </button>
    </form>
    
    <!-- Additional Info -->
    <div class="mt-6 text-center">
        <div class="text-sm text-gray-500">
            <p class="mb-2">Don't have an account? Contact your administrator</p>
            <div class="flex items-center justify-center space-x-4 text-xs">
                <span class="flex items-center">
                    <i class="fas fa-shield-alt text-green-500 mr-1"></i>Secure Login
                </span>
                <span class="flex items-center">
                    <i class="fas fa-clock text-blue-500 mr-1"></i>24/7 Support
                </span>
            </div>
        </div>
    </div>
</div>
