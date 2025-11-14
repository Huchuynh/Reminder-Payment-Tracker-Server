<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountQueryRequest;
use App\Http\Requests\UpdateAccountActiveStateRequest;
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

    public function getAccountPaginated(AccountQueryRequest $request)
    {
        try {
            $accounts = $this->accountService->getAccountPaginated($request->validated());
            return AccountResource::collection($accounts);
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function getSelectableAccounts()
    {
        try {
            $recipients = $this->accountService->getSelectableAccounts();

            return $this->responseSuccess(
                $recipients,
                'Get recipients successfully.'
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function updateAccountActiveState(UpdateAccountActiveStateRequest $request)
    {
        try {
            $this->accountService->updateAccountActiveState($request->validated());
            return $this->responseSuccess(null, 'Account status successfully updated.');
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function me(Request $request)
    {
        return $this->responseSuccess(
            $request->user(),
            'Successfully authenticated!'
        );
    }
}
