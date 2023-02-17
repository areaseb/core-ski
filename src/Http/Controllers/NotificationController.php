<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate();
        return view('areaseb::notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['read' => !$notification->read]);
        return (string) !$notification->read;
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return 'done';
    }

    public function show(Notification $notification)
    {
        return view('areaseb::notifications.show', compact('notification'));
    }

}
