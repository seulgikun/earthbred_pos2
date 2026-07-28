<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\VerifyAccountNotification;
use App\Notifications\OwnerPasswordResetNotification;

class UserController extends Controller
{
    /**
     * Handle login and return user details.
     */
    public function login(Request $request)
    {
        $user = User::where('email', strtolower(trim($request->email)))->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Non-owner users must verify their email address before logging in
            if ($user->role !== 'owner' && is_null($user->email_verified_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has not been verified yet. Please check your email for the verification link sent when your account was created.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    /**
     * Display a listing of non-owner accounts (only verified status included).
     */
    public function index()
    {
        $users = User::whereIn('role', ['manager', 'cashier'])
                     ->orderBy('role')
                     ->orderBy('name')
                     ->get(['id', 'name', 'email', 'role', 'email_verified_at', 'created_at']);
                     
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created account and dispatch verification email.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:manager,cashier',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => null, // Unverified until user clicks verification email link
        ]);

        // Send Email Verification Link
        try {
            $user->notify(new VerifyAccountNotification());
        } catch (\Exception $e) {
            \Log::error('Failed sending verification email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->role) . ' account created successfully. A verification link has been sent to ' . $user->email . '.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ]
        ], 201);
    }

    /**
     * Handle Email Verification Link clicks.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return response()->view('account-verified', [
                'status' => 'error',
                'message' => 'Invalid verification link.'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->view('account-verified', [
                'status' => 'already_verified',
                'message' => 'Your account is already verified! You can log in now.'
            ]);
        }

        $user->markEmailAsVerified();

        return response()->view('account-verified', [
            'status' => 'success',
            'message' => 'Your account has been successfully verified! You can now log in.'
        ]);
    }

    /**
     * Handle Forgot Password request from login page.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.'
            ], 404);
        }

        // If user is Manager or Cashier, inform them to ask the Owner
        if ($user->role !== 'owner') {
            return response()->json([
                'success' => false,
                'isStaff' => true,
                'message' => 'Password reset for Manager/Staff accounts must be requested through the Owner. Please inform the Owner to reset your password via Account Management.'
            ], 403);
        }

        // Generate password reset token for Owner
        $token = Str::random(60);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        try {
            $user->notify(new OwnerPasswordResetNotification($token));
        } catch (\Exception $e) {
            \Log::error('Failed sending owner password reset email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'A password reset link has been sent to your Owner email address (' . $email . ').'
        ]);
    }

    /**
     * Handle Reset Password completion for Owner.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset token.'
            ], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Owner password has been reset successfully. You can now log in.'
        ]);
    }

    /**
     * Update the specified user's password (Owner action).
     */
    public function updatePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($id);
        
        if ($user->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully for ' . $user->name . '.'
        ]);
    }

    /**
     * Remove the specified account.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.'
        ]);
    }

    /**
     * Update Void PIN for the owner.
     */
    public function updateVoidPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|min:4'
        ]);

        $owner = User::where('role', 'owner')->first();
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Owner account not found.'], 404);
        }

        $owner->void_pin = Hash::make($request->pin);
        $owner->save();

        return response()->json(['success' => true, 'message' => 'Void PIN updated successfully.']);
    }

    /**
     * Verify Void PIN.
     */
    public function verifyVoidPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string'
        ]);

        $owner = User::where('role', 'owner')->first();
        if (!$owner || !$owner->void_pin) {
            return response()->json(['success' => false, 'message' => 'Void PIN not set by owner.'], 400);
        }

        if (Hash::check($request->pin, $owner->void_pin)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid Void PIN.'], 401);
    }
}
