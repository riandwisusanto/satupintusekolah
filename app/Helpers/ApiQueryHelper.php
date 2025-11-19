<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiQueryHelper
{
    public static function apply(Builder $query, array $config)
    {
        $request = request();

        if ($with = request()->query('with')) {
            $relations = explode(',', $with);
            $validRelations = self::filterValidRelations($query->getModel(), $relations);
            $query->with($validRelations);
        }

        $query = self::applyRelations($query, $config);
        $query = self::applyFilters($query, $config, $request);
        $query = self::applySearch($query, $config, $request);
        $query = self::applySort($query, $config, $request);

        if ($request->boolean('all')) {
            return [
                'data' => $query->get(),
                'meta' => [
                    'total' => $query->count(),
                    'all' => true
                ]
            ];
        }

        // Pagination
        $perPage = $request->integer('per_page', 10);
        $data = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
            ],
        ]);
    }

    protected static function applyRelations(Builder $query, array $config)
    {
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }
        return $query;
    }

    protected static function applyFilters(Builder $query, array $config, Request $request)
    {
        $filters = $request->input('filter', []);

        foreach ($filters as $field => $value) {
            if (strpos($field, '.') !== false) {
                [$relation, $column] = explode('.', $field, 2);

                $query->whereHas($relation, function ($q) use ($column, $value) {
                    if (is_array($value) && self::isAssoc($value)) {
                        foreach ($value as $operator => $v) {
                            switch ($operator) {
                                case 'gt':
                                    $q->where($column, '>', $v);
                                    break;
                                case 'lt':
                                    $q->where($column, '<', $v);
                                    break;
                                case 'gte':
                                    $q->where($column, '>=', $v);
                                    break;
                                case 'lte':
                                    $q->where($column, '<=', $v);
                                    break;
                                case 'ne':
                                    $q->where($column, '!=', $v);
                                    break;
                                case 'like':
                                    $q->where($column, 'like', "%$v%");
                                    break;
                                default:
                                    break;
                            }
                        }
                    } elseif (is_array($value)) {
                        $q->whereIn($column, $value);
                    } else {
                        if (
                            in_array($column, ['created_at', 'updated_at', 'deleted_at'])
                            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
                        ) {
                            $q->whereDate($column, $value);
                        } else {
                            $q->where($column, $value);
                        }
                    }
                });
            } else {
                if (is_array($value) && self::isAssoc($value)) {
                    foreach ($value as $operator => $v) {
                        switch ($operator) {
                            case 'gt':
                                $query->where($field, '>', $v);
                                break;
                            case 'lt':
                                $query->where($field, '<', $v);
                                break;
                            case 'gte':
                                $query->where($field, '>=', $v);
                                break;
                            case 'lte':
                                $query->where($field, '<=', $v);
                                break;
                            case 'ne':
                                $query->where($field, '!=', $v);
                                break;
                            case 'like':
                                $query->where($field, 'like', "%$v%");
                                break;
                            default:
                                break;
                        }
                    }
                } elseif (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    if (
                        in_array($field, ['created_at', 'updated_at', 'deleted_at'])
                        && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
                    ) {
                        $query->whereDate($field, $value);
                    } else {
                        $query->where($field, $value); // default: equal
                    }
                }
            }
        }

        return $query;
    }

    protected static function applySearch(Builder $query, array $config, Request $request)
    {
        $search = $request->query('search');
        if (!$search || empty($config['searchable'])) return $query;

        $query->where(function ($q) use ($search, $config) {
            foreach ($config['searchable'] as $field) {
                if (strpos($field, '.') !== false) {
                    [$relation, $column] = explode('.', $field);
                    $q->orWhereHas($relation, function ($qr) use ($column, $search) {
                        $qr->where($column, 'like', "%{$search}%");
                    });
                } else {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });

        return $query;
    }

    protected static function applySort(Builder $query, array $config, Request $request)
    {
        $sort = $request->query('sort', $config['default_sort'] ?? null);
        if (!$sort || empty($config['sortable'])) return $query;

        $direction = 'asc';
        if (strpos($sort, '-') === 0) {
            $direction = 'desc';
            $sort = ltrim($sort, '-');
        }

        if (!in_array($sort, $config['sortable'])) return $query;

        if (strpos($sort, '.') !== false) {
            [$relationName, $column] = explode('.', $sort);

            if (!method_exists($query->getModel(), $relationName)) return $query;
            $relation = $query->getModel()->{$relationName}();

            $mainTable = $query->getModel()->getTable();

            // --- Handle belongsTo ---
            if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                $relatedTable = $relation->getRelated()->getTable();
                $foreignKey = $relation->getForeignKeyName(); // sender_id
                $ownerKey   = $relation->getOwnerKeyName();   // id di table related
                $query->leftJoin($relatedTable, "{$relatedTable}.{$ownerKey}", '=', "{$mainTable}.{$foreignKey}");
                $query->orderBy("{$relatedTable}.{$column}", $direction);
            }
            // --- Handle hasOne / hasMany ---
            elseif (
                $relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne
                || $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany
            ) {
                $relatedTable = $relation->getRelated()->getTable();
                $foreignKey = $relation->getForeignKeyName(); // main table key di table related
                $localKey   = $relation->getLocalKeyName();
                $query->leftJoin($relatedTable, "{$relatedTable}.{$foreignKey}", '=', "{$mainTable}.{$localKey}");
                $query->orderBy("{$relatedTable}.{$column}", $direction);
            }
            // --- Handle morphTo ---
            elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\MorphTo) {
                $related = $relation->getRelated();
                $relatedTable = $related->getTable();
                $foreignKey = $relation->getForeignKeyName();
                $ownerKey   = $related->getKeyName();
                $query->leftJoin($relatedTable, "{$relatedTable}.{$ownerKey}", '=', "{$mainTable}.{$foreignKey}");
                $query->orderBy("{$relatedTable}.{$column}", $direction);
            } else {
                // fallback jika tipe relasi tidak didukung
                return $query;
            }

            // selalu select main table untuk mencegah collision
            $query->select("{$mainTable}.*");
        } else {
            // sort field biasa
            $query->orderBy($sort, $direction);
        }

        return $query;
    }

    protected static function filterValidRelations(Model $model, array $relations): array
    {
        return array_filter($relations, function ($relation) use ($model) {
            $base = explode('.', $relation)[0];

            return method_exists($model, $base) &&
                $model->{$base}() instanceof Relation;
        });
    }

    public static function isAssoc(array $arr): bool
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function injectFilter(array $filters): void
    {
        request()->merge([
            'filter' => array_merge(
                request()->get('filter', []),
                $filters
            ),
        ]);
    }

    /**
     * Generate filter configuration from model
     */
    public static function generateFilterConfig(Model $model): array
    {
        $config = method_exists($model, 'apiQueryConfig') ? $model->apiQueryConfig() : [];

        return [
            'searchable' => $config['searchable'] ?? [],
            'filterable' => $config['filterable'] ?? [],
            'select_options' => $config['select_options'] ?? [],
            'relation_select' => $config['relation_select'] ?? [],
        ];
    }

    /**
     * Apply filters with enhanced functionality including number operations
     */
    public static function queryWithFilters(Builder $query, array $params, Model $model): array
    {
        $config = method_exists($model, 'apiQueryConfig') ? $model->apiQueryConfig() : [];

        // Apply relations
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        // Apply filters with enhanced logic
        self::applyEnhancedFilters($query, $params, $config);

        // Apply search
        self::applyEnhancedSearch($query, $params, $config);

        // Apply sorting
        self::applyEnhancedSort($query, $params, $config);

        // Handle pagination
        $perPage = $params['per_page'] ?? 10;
        $page = $params['page'] ?? 1;

        if (isset($params['all']) && $params['all'] === 'true') {
            $data = $query->get();
            $total = $data->count();

            return [
                'data' => $data,
                'meta' => [
                    'total' => $total,
                    'per_page' => $total,
                    'current_page' => 1,
                    'last_page' => 1,
                    'all' => true,
                ],
                'filter_config' => self::generateFilterConfig($model)
            ];
        }

        $data = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $data->items(),
            'meta' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
            'filter_config' => self::generateFilterConfig($model)
        ];
    }

    /**
     * Apply enhanced filters with number operations support
     */
    protected static function applyEnhancedFilters(Builder $query, array $params, array $config): void
    {
        $filters = $params['filter'] ?? [];

        foreach ($filters as $field => $value) {
            if (empty($value)) continue;

            // Handle date range
            if (isset($value['from']) || isset($value['to'])) {
                self::applyDateRangeFilter($query, $field, $value);
                continue;
            }

            // Handle number operations
            if (isset($value['operator']) && isset($value['value'])) {
                self::applyNumberFilter($query, $field, $value['operator'], $value['value']);
                continue;
            }

            // Handle array values (multiple select)
            if (is_array($value) && !self::isAssoc($value)) {
                self::applyArrayFilter($query, $field, $value);
                continue;
            }

            // Handle simple filters
            self::applySimpleFilter($query, $field, $value);
        }
    }

    /**
     * Apply date range filter
     */
    protected static function applyDateRangeFilter(Builder $query, string $field, array $value): void
    {
        if (isset($value['from']) && !empty($value['from'])) {
            $query->where($field, '>=', $value['from']);
        }

        if (isset($value['to']) && !empty($value['to'])) {
            $query->where($field, '<=', $value['to']);
        }
    }

    /**
     * Apply number filter with operator
     */
    protected static function applyNumberFilter(Builder $query, string $field, string $operator, $value): void
    {
        $operators = [
            '=' => '=',
            '!=' => '!=',
            '>' => '>',
            '<' => '<',
            '>=' => '>=',
            '<=' => '<=',
            'contains' => 'like',
            'starts_with' => 'like',
            'ends_with' => 'like',
        ];

        if (!isset($operators[$operator])) {
            return;
        }

        $dbOperator = $operators[$operator];

        if (in_array($operator, ['contains', 'starts_with', 'ends_with'])) {
            switch ($operator) {
                case 'contains':
                    $searchValue = "%{$value}%";
                    break;
                case 'starts_with':
                    $searchValue = "{$value}%";
                    break;
                case 'ends_with':
                    $searchValue = "%{$value}";
                    break;
                default:
                    $searchValue = "%{$value}%";
                    break;
            }
            $query->where($field, $dbOperator, $searchValue);
        } else {
            $query->where($field, $dbOperator, $value);
        }
    }

    /**
     * Apply array filter (for multiple select)
     */
    protected static function applyArrayFilter(Builder $query, string $field, array $values): void
    {
        if (empty($values)) return;

        if (strpos($field, '.') !== false) {
            [$relation, $column] = explode('.', $field, 2);
            $query->whereHas($relation, function ($q) use ($column, $values) {
                $q->whereIn($column, $values);
            });
        } else {
            $query->whereIn($field, $values);
        }
    }

    /**
     * Apply simple filter
     */
    protected static function applySimpleFilter(Builder $query, string $field, $value): void
    {
        if (strpos($field, '.') !== false) {
            [$relation, $column] = explode('.', $field, 2);
            $query->whereHas($relation, function ($q) use ($column, $value) {
                $q->where($column, $value);
            });
        } else {
            $query->where($field, $value);
        }
    }

    /**
     * Apply enhanced search
     */
    protected static function applyEnhancedSearch(Builder $query, array $params, array $config): void
    {
        $search = $params['search'] ?? '';
        if (empty($search) || empty($config['searchable'])) {
            return;
        }

        $query->where(function ($q) use ($search, $config) {
            foreach ($config['searchable'] as $field) {
                if (strpos($field, '.') !== false) {
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($qr) use ($column, $search) {
                        $qr->where($column, 'like', "%{$search}%");
                    });
                } else {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply enhanced sorting
     */
    protected static function applyEnhancedSort(Builder $query, array $params, array $config): void
    {
        $sort = $params['sort'] ?? $config['default_sort'] ?? null;
        if (!$sort) return;

        $direction = strpos($sort, '-') === 0 ? 'desc' : 'asc';
        $sortField = ltrim($sort, '-');

        if (!empty($config['sortable']) && !in_array($sortField, $config['sortable'])) {
            return;
        }

        if (strpos($sortField, '.') !== false) {
            self::applyRelationSort($query, $sortField, $direction);
        } else {
            $query->orderBy($sortField, $direction);
        }
    }

    /**
     * Apply relation sorting
     */
    protected static function applyRelationSort(Builder $query, string $sortField, string $direction): void
    {
        [$relationName, $column] = explode('.', $sortField, 2);
        $model = $query->getModel();

        if (!method_exists($model, $relationName)) {
            return;
        }

        $relation = $model->{$relationName}();
        $mainTable = $model->getTable();

        // Handle belongsTo relation
        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
            $relatedTable = $relation->getRelated()->getTable();
            $foreignKey = $relation->getForeignKeyName();
            $ownerKey = $relation->getOwnerKeyName();

            $query->leftJoin($relatedTable, "{$relatedTable}.{$ownerKey}", '=', "{$mainTable}.{$foreignKey}")
                ->orderBy("{$relatedTable}.{$column}", $direction)
                ->select("{$mainTable}.*");
        }
        // Handle hasOne/hasMany relation
        elseif (
            $relation instanceof \Illuminate\Database\Eloquent\Relations\HasOne ||
            $relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany
        ) {
            $relatedTable = $relation->getRelated()->getTable();
            $foreignKey = $relation->getForeignKeyName();
            $localKey = $relation->getLocalKeyName();

            $query->leftJoin($relatedTable, "{$relatedTable}.{$foreignKey}", '=', "{$mainTable}.{$localKey}")
                ->orderBy("{$relatedTable}.{$column}", $direction)
                ->select("{$mainTable}.*");
        }
    }
}
