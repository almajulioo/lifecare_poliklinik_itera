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

            Notification::route('fcm', $token)
                ->notify(new FcmTestNotification($request->title, $request->body));

            Log::info('FCM test notification dispatched', [
                'user_id' => auth()->id(),
                'token_suffix' => substr($token, -12),
                'title' => $request->title,
            ]);

            return back()->with('success', 'Notification sent successfully!');
        } catch (\Exception $e) {
            Log::error('FCM test notification failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Error sending notification: ' . $e->getMessage());
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
