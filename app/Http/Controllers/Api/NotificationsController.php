<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
//        dump(auth('api')->user());
        $notifications = auth('api')->user()->notifications()->paginate();

        return NotificationResource::collection($notifications);
    }
}
