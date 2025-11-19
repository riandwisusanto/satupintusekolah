<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait HasApiQueryConfig
{
    public static function apiQueryConfig(): array
    {
        $model = new static;
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        // Get 'with' from query parameter
        $with = request()->query('with')
            ? explode(',', request()->query('with'))
            : [];

        $relatedColumns = self::extractNestedRelationColumns($model, $with, 1);
        $allColumns = array_merge($columns, $relatedColumns);

        // Check if model has allowedQueryColumns property
        if (property_exists($model, 'allowedQueryColumns')) {
            $allColumns = array_intersect($allColumns, $model->allowedQueryColumns);
        }

        return [
            'searchable' => $allColumns,
            'filterable' => $allColumns,
            'sortable' => $allColumns,
            'with' => $with,
            'default_sort' => '-created_at',
        ];
    }

    protected static function extractNestedRelationColumns($model, array $with, $depth = 1): array
    {
        $columns = [];

        foreach ($with as $relation) {
            if (!method_exists($model, $relation)) continue;

            $relationInstance = $model->$relation();
            $related = $relationInstance->getRelated();
            $relatedTable = $related->getTable();
            $relatedCols = Schema::getColumnListing($relatedTable);

            foreach ($relatedCols as $col) {
                $columns[] = "$relation.$col";
            }

            // Handle nested relations (depth > 1)
            if ($depth > 1 && property_exists($related, 'defaultWith')) {
                $subWith = $related->defaultWith;
                $nestedCols = self::extractNestedRelationColumns($related, $subWith, $depth - 1);

                foreach ($nestedCols as $nestedCol) {
                    $columns[] = "$relation.$nestedCol";
                }
            }
        }

        return $columns;
    }
}
