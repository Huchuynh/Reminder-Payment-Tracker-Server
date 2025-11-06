<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    protected function responseSuccess($data, $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    protected function responseCreateSuccess($data, $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_CREATED);
    }

    protected function responseBadRequest($message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function responseNotFound($message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    protected function responseForbidden($message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }

    protected function responseUnauthorized($message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function responseInternalError($message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function handleExceptionResponse(\Throwable $e): JsonResponse
    {
        if ($e->getCode() === Response::HTTP_BAD_REQUEST)
            return $this->responseBadRequest($e->getMessage());
        else if ($e instanceof ModelNotFoundException)
            return $this->responseNotFound($e->getMessage());
        else if ($e instanceof AuthorizationException)
            return $this->responseForbidden($e->getMessage());
        else if ($e->getCode() === Response::HTTP_UNAUTHORIZED)
            return $this->responseUnauthorized($e->getMessage());
        else
            return $this->responseInternalError($e->getMessage());
    }
}
