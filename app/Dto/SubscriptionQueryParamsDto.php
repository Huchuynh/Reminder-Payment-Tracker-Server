<?php

namespace App\Dto;

class SubscriptionQueryParamsDto extends QueryParamsDto
{
    public int $account_id;
    public ?string $start_date = null;
    public ?string $end_date = null;
    public string $status;

    public function __construct(
        string $search,
        string $sort_by,
        string $sort_order,
        int $limit,
        int $account_id,
        ?string $start_date,
        ?string $end_date,
        string $status
    ){
        parent::__construct($search, $sort_by, $sort_order, $limit);
        $this->account_id = $account_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->status = $status;
    }
}
