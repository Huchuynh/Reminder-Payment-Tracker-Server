<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RenewCancelStatisticRequest;
use App\Http\Requests\RevenueStatisticRequest;
use App\Services\StatisticService;
use App\Traits\ApiResponseTrait;

class StatisticController extends Controller
{
    use ApiResponseTrait;

    protected StatisticService $statisticService;

    public function __construct(StatisticService $statisticService)
    {
        $this->statisticService = $statisticService;
    }

    public function getRenewCancelStatisticByService(RenewCancelStatisticRequest $request)
    {
        try {
            $result = $this->statisticService->getRenewCancelStatisticByService($request->validated());

            return $this->responseSuccess(
                $result['data'],
                $result['message'],
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function getRevenueStatisticByPeriod(RevenueStatisticRequest $request)
    {
        try {
            $result = $this->statisticService->getRevenueStatisticByPeriod($request->validated());

            return $this->responseSuccess(
                $result,
                "Get Revenue Statistic by Period Successfully",
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
