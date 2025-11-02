<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    protected $notificationService;

    public function __construct(NotificationService $service)
    {
        $this->notificationService = $service;
    }

    public function index(Request $request)
    {
        try {
            $notifications = $this->notificationService->get(
                $request->user(),
                $request->validate([
                    "limit" => ["nullable", "integer"],
                ])
            );

            return NotificationResource::collection($notifications);
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $result = $this->notificationService->getById($request->user(), $id);

            return $this->responseSuccess(
                $result,
                'Notifications successfully found'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function markAsRead(Request $request, $id)
    {
        try {
            $result = $this->notificationService->markAsRead($request->user(), $id);

            return $this->responseSuccess(
                $result,
                'Notification successfully marked as read'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }

    }

    public function markAllAsRead(Request $request)
    {
        try {
            $result = $this->notificationService->markAllAsRead($request->user());

            return $this->responseSuccess(
                null,
                $result['message']
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $result = $this->notificationService->delete($request->user(), $id);;

            return $this->responseSuccess(
                null,
                $result['message']
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
