<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function getRenewCancelStatisticByPeriod(RevenueStatisticRequest $request)
    {
        try {
            $result = $this->statisticService->getRenewCancelStatisticByPeriod($request->validated());

            return $this->responseSuccess(
                $result,
                "Get Renew Cancel Rate Statistic by Period Successfully",
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
