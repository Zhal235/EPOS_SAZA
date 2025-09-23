<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventCustomerLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is a customer
        if (Auth::check() && Auth::user()->isCustomer()) {
            Auth::logout();
            
            return redirect('/login')->withErrors([
                'email' => 'Customers are not allowed to login to this system.',
            ]);
        }
        
        return $next($request);
    }
}
