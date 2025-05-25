<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasFilter
{

    public function scopeFilter(Builder &$query): Builder
    {
        $request = request();

        $this->applySort($query, $request);
        $this->applyColumnFilters($query, $request);
        $this->applyRangeFilters($query, $request);

        return $query;
    }


    protected function applySort(Builder &$query, $request): Builder
    {
        if ($request->has('sort_key') && isset($this->filterSort) && in_array($request->sort_key, $this->filterSort)) {
            $direction = in_array($request->sort_type, ['asc', 'desc']) ? $request->sort_type : 'asc';
            $query->orderBy($request->sort_key, $direction);
        }
        return $query;
    }

    protected function applyColumnFilters(Builder &$query, $request): Builder
    {
        if (!empty($this->filterCols)) {
            foreach ($this->filterCols as $column) {
                if ($request->has($column)) {
                    $values = explode(',', $request->$column);
                    $query->whereIn($this->getTable() . '.' . $column, $values);
                }
            }
        }
        return $query;
    }

    protected function applyRangeFilters(Builder &$query, $request): Builder
    {
        if (!empty($this->filterBetween)) {
            foreach ($this->filterBetween as $column => $inputs) {
                if ($request->has($inputs['from']) || $request->has($inputs['to'])) {
                    if ($request->has($inputs['from'])) {
                        $query->where($column, '>=', $request->input($inputs['from']));
                    }
                    if ($request->has($inputs['to'])) {
                        $query->where($column, '<=', $request->input($inputs['to']));
                    }
                }
            }
        }
        return $query;
    }
}
