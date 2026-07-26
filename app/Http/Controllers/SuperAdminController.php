<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminController extends Controller
{
    public function index(): Response
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|in:super_admin,admin,teacher,student',
            'password' => 'required|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->back()->with('success', "নতুন ইউজার '{$user->name}' ({$user->role}) সফলভাবে তৈরি করা হয়েছে।");
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'role' => 'required|in:super_admin,admin,teacher,student',
            'permissions' => 'nullable|array',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', "ইউজার '{$user->name}' এর পারমিশন ও তথ্য সফলভাবে আপডেট করা হয়েছে।");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'আপনি নিজের অ্যাকাউন্ট ডিলিট করতে পারবেন না।');
        }

        $user->delete();
        return redirect()->back()->with('success', 'ইউজার অ্যাকাউন্ট সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
