<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\FcmTestNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class FcmTestController extends Controller
{
    public function index()
    {
        $users = User::whereNotNull('fcm_token')->get();
        $currentUserToken = auth()->user()?->fcm_token;
        return view('fcm-test', compact('users', 'currentUserToken'));
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|min:80',
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:500',
        ]);

        try {
            $token = trim($request->fcm_token);

            // Detailed debug logging
            Log::info('FCM test notification attempt', [
                'user_id' => auth()->id(),
                'token_length' => strlen($token),
                'token_first_20' => substr($token, 0, 20),
                'title' => $request->title,
                'body_length' => strlen($request->body),
            ]);

            // Check if token is valid
            if (strlen($token) < 80) {
                throw new \Exception("FCM token appears invalid (too short: " . strlen($token) . " chars)");
            }

            Notification::route('fcm', $token)
                ->notify(new FcmTestNotification($request->title, $request->body));

            Log::info('FCM test notification dispatched', [
                'user_id' => auth()->id(),
                'token_suffix' => substr($token, -12),
                'title' => $request->title,
            ]);

            return back()->with('success', 'Notification sent successfully!');
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();

            Log::error('FCM test notification failed', [
                'user_id' => auth()->id(),
                'message' => $errorMessage,
                'code' => $errorCode,
                'file' => $errorFile,
                'line' => $errorLine,
                'exception' => get_class($e),
            ]);

            // More user-friendly error message
            $displayMessage = 'Error sending notification: ' . $errorMessage;
            
            // Check for common issues
            if (strpos($errorMessage, 'Unauthenticated') !== false || strpos($errorMessage, 'permission') !== false) {
                $displayMessage = 'Firebase authentication failed. Check your credentials file.';
            } elseif (strpos($errorMessage, 'invalid-argument') !== false) {
                $displayMessage = 'Invalid FCM token. The token may have expired.';
            } elseif (strpos($errorMessage, 'NOT_FOUND') !== false) {
                $displayMessage = 'FCM instance not found. Check your Firebase configuration.';
            }

            return back()->with('error', $displayMessage);
        }
    }

    public function saveToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|min:80',
        ]);

        $user = auth()->user();
        if ($user) {
            $user->fcm_token = trim($request->fcm_token);
            $user->save();
            return response()->json(['success' => true, 'message' => 'Token saved successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }
}
