<?php

namespace App\Dto;

class QueryParamsDto
{
    public string $search = "";
    public string $sort_by = "created_at";
    public string $sort_order = "desc";
    public int $limit = 5;

    public function __construct(string $search, string $sort_by, string $sort_order, int $limit){
        $this->search = $search;
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->limit = $limit;
    }
}
