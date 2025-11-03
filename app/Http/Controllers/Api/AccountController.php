<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountQueryRequest;
use App\Http\Resources\AccountResource;
use App\Services\AccountService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponseTrait;

    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function getAccountWithSubscription(AccountQueryRequest $request)
    {
        try {
            $accounts = $this->accountService->getAccountWithSubscription($request->validated());

            return AccountResource::collection($accounts);
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function me(Request $request)
    {
        return $this->responseSuccess(
            $request->user(),
            "Successfully authenticated!"
        );
    }
}
