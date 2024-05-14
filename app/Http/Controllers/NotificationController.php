<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request){
        auth()->user()->unreadNotifications->where('id', $request->id)->markAsRead();
    }
}
