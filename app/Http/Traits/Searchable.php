<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait Searchable
{
    public function scopeSearch(Builder|QueryBuilder $query, string $search, array $fields = []): Builder|QueryBuilder
    {
        foreach ($fields as $field) {
            $query->orWhere($field, 'like', '%' . $search . '%');
        }

        return $query;
    }
}
