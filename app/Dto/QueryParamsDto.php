<?php

namespace App\Dto;

class QueryParamsDto
{
    private string $search = "";
    private string $sort_by = "created_at";
    private string $sort_order = "desc";
    private int $limit = 5;

    public function __construct(string $search, string $sort_by, string $sort_order, int $limit){
        $this->search = $search;
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order;
        $this->limit = $limit;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setSearch(string $search): void
    {
        $this->search = $search;
    }

    public function getSortBy(): string
    {
        return $this->sort_by;
    }

    public function setSortBy(string $sort_by): void
    {
        $this->sort_by = $sort_by;
    }

    public function getSortOrder(): string
    {
        return $this->sort_order;
    }

    public function setSortOrder(string $sort_order): void
    {
        $this->sort_order = $sort_order;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }


}
