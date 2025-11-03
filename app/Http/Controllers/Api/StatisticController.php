<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatisticService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    use ApiResponseTrait;

    protected StatisticService $statisticService;

    public function __construct(StatisticService $statisticService)
    {
        $this->statisticService = $statisticService;
    }

    public function getRenewCancelStatistic(Request $request)
    {
        try {
            $result = $this->statisticService->getRenewCancelStatistic($request->validate([
                "from" => "required|date|date_format:Y-m-d H:i:s",
                "to" => "required|date|date_format:Y-m-d H:i:s|after:from",
            ]));

            return $this->responseSuccess(
                $result,
                "Get renew and cancel statistic successfully."
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}
