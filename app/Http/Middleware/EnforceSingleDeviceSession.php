<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnforceSingleDeviceSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $currentSessionId = session()->getId();
            $user = Auth::user();

            // Store current session ID if not set
            if (!$user->device_token) {
                $user->update(['device_token' => $currentSessionId]);
            } else if ($user->device_token !== $currentSessionId) {
                // Another device logged in: invalidate current session
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'আপনার অ্যাকাউন্টটিতে অন্য একটি ডিভাইস থেকে লগইন করা হয়েছে। আপনাকে সমসাময়িক সেশন থেকে লগআউট করা হয়েছে।',
                ]);
            }
        }

        return $next($request);
    }
}
