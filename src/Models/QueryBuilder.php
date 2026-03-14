<?php

namespace WeDevs\WpUtils\Models;

/**
 * Fluent query builder for models.
 *
 * Wraps $wpdb to provide a chainable, safe query interface.
 */
class QueryBuilder {

    /**
     * The model instance this builder is for.
     *
     * @var Model
     */
    protected $model;

    /**
     * WHERE clauses.
     *
     * Each entry: [ 'type' => 'basic|in|null|notNull|notIn|between|raw|or',
     *               'column' => ..., 'operator' => ..., 'value' => ..., ... ]
     *
     * @var array<int, array<string, mixed>>
     */
    protected $wheres = [];

    /**
     * ORDER BY clauses.
     *
     * Each entry: [ 'column' => ..., 'direction' => 'ASC'|'DESC' ]
     *
     * @var array<int, array{column: string, direction: string}>
     */
    protected $orders = [];

    /**
     * LIMIT value.
     *
     * @var int|null
     */
    protected $limit_value = null;

    /**
     * OFFSET value.
     *
     * @var int|null
     */
    protected $offset_value = null;

    /**
     * QueryBuilder constructor.
     *
     * @param Model $model The model instance.
     */
    public function __construct( Model $model ) {
        $this->model = $model;
    }

    // -------------------------------------------------------------------------
    // WHERE Clauses
    // -------------------------------------------------------------------------

    /**
     * Add a basic WHERE clause.
     *
     * @param string $column           Column name.
     * @param mixed  $operator_or_value Operator or value (if 2 args, treated as '=').
     * @param mixed  $value            Value (optional).
     *
     * @return static
     */
    public function where( $column, $operator_or_value = null, $value = null ) {
        if ( null === $value ) {
            $value = $operator_or_value;
            $operator = '=';
        } else {
            $operator = $operator_or_value;
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => strtoupper( $operator ),
            'value' => $value,
            'boolean' => 'AND',
        ];

        return $this;
    }

    /**
     * Add an OR WHERE clause.
     *
     * @param string $column           Column name.
     * @param mixed  $operator_or_value Operator or value.
     * @param mixed  $value            Value (optional).
     *
     * @return static
     */
    public function orWhere( $column, $operator_or_value = null, $value = null ) {
        if ( null === $value ) {
            $value = $operator_or_value;
            $operator = '=';
        } else {
            $operator = $operator_or_value;
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => strtoupper( $operator ),
            'value' => $value,
            'boolean' => 'OR',
        ];

        return $this;
    }

    /**
     * Add a WHERE IN clause.
     *
     * @param string            $column Column name.
     * @param array<int, mixed> $values Values to match.
     *
     * @return static
     */
    public function whereIn( $column, array $values ) {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND',
        ];

        return $this;
    }

    /**
     * Add a WHERE NOT IN clause.
     *
     * @param string            $column Column name.
     * @param array<int, mixed> $values Values to exclude.
     *
     * @return static
     */
    public function whereNotIn( $column, array $values ) {
        $this->wheres[] = [
            'type' => 'notIn',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND',
        ];

        return $this;
    }

    /**
     * Add a WHERE IS NULL clause.
     *
     * @param string $column Column name.
     *
     * @return static
     */
    public function whereNull( $column ) {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => 'AND',
        ];

        return $this;
    }

    /**
     * Add a WHERE IS NOT NULL clause.
     *
     * @param string $column Column name.
     *
     * @return static
     */
    public function whereNotNull( $column ) {
        $this->wheres[] = [
            'type' => 'notNull',
            'column' => $column,
            'boolean' => 'AND',
        ];

        return $this;
    }

    /**
     * Add a WHERE BETWEEN clause.
     *
     * @param string $column Column name.
     * @param mixed  $min    Minimum value.
     * @param mixed  $max    Maximum value.
     *
     * @return static
     */
    public function whereBetween( $column, $min, $max ) {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'min' => $min,
            'max' => $max,
            'boolean' => 'AND',
        ];

        return $this;
    }

    /**
     * Add a raw WHERE clause.
     *
     * @param string            $sql      Raw SQL fragment.
     * @param array<int, mixed> $bindings Bindings for prepare().
     *
     * @return static
     */
    public function whereRaw( $sql, array $bindings = [] ) {
        $this->wheres[] = [
            'type' => 'raw',
            'sql' => $sql,
            'bindings' => $bindings,
            'boolean' => 'AND',
        ];

        return $this;
    }

    // -------------------------------------------------------------------------
    // ORDER, LIMIT, OFFSET
    // -------------------------------------------------------------------------

    /**
     * Add an ORDER BY clause.
     *
     * @param string $column    Column name.
     * @param string $direction 'ASC' or 'DESC'.
     *
     * @return static
     */
    public function orderBy( $column, $direction = 'ASC' ) {
        $direction = strtoupper( $direction );

        if ( ! in_array( $direction, [ 'ASC', 'DESC' ], true ) ) {
            $direction = 'ASC';
        }

        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Set the LIMIT.
     *
     * @param int $limit Number of rows.
     *
     * @return static
     */
    public function limit( $limit ) {
        $this->limit_value = (int) $limit;

        return $this;
    }

    /**
     * Set the OFFSET.
     *
     * @param int $offset Number of rows to skip.
     *
     * @return static
     */
    public function offset( $offset ) {
        $this->offset_value = (int) $offset;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Execution — Read
    // -------------------------------------------------------------------------

    /**
     * Execute the query and return a collection of models.
     *
     * @return Collection
     */
    public function get() {
        global $wpdb;

        $entity = $this->model::getEntityName();

        /**
         * Filter the query builder before execution.
         *
         * @param QueryBuilder $builder The query builder instance.
         */
        $builder = apply_filters( $this->model::hookName( $entity . '_query' ), $this );

        $sql = $builder->toSelectSql();
        $results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

        if ( null === $results ) {
            $results = [];
        }

        $collection = $this->model::hydrate( $results );

        /**
         * Filter the query results.
         *
         * @param Collection   $collection The result collection.
         * @param QueryBuilder $builder    The query builder instance.
         */
        return apply_filters( $this->model::hookName( $entity . '_query_results' ), $collection, $builder );
    }

    /**
     * Get the first result.
     *
     * @return Model|null
     */
    public function first() {
        $this->limit( 1 );

        $results = $this->get();

        return $results->first();
    }

    /**
     * Find a model by primary key.
     *
     * @param int $id Primary key value.
     *
     * @return Model|null
     */
    public function find( $id ) {
        return $this->where( $this->model::getPrimaryKey(), $id )->first();
    }

    /**
     * Get the count of matching rows.
     *
     * @return int
     */
    public function count() {
        global $wpdb;

        $sql = $this->toCountSql();

        return (int) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }

    /**
     * Check if any matching rows exist.
     *
     * @return bool
     */
    public function exists() {
        return $this->count() > 0;
    }

    /**
     * Pluck a single column's values.
     *
     * @param string $column Column name.
     *
     * @return array<int, string>
     */
    public function pluck( $column ) {
        global $wpdb;

        $safe_column = sanitize_key( $column );
        $sql = $this->toSelectSql( $safe_column );
        $results = $wpdb->get_col( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

        return $results ?: [];
    }

    /**
     * Paginate results.
     *
     * @param int $per_page Items per page.
     * @param int $page     Current page number.
     *
     * @return array{data: Collection, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function paginate( $per_page = 20, $page = 1 ) {
        $per_page = max( 1, (int) $per_page );
        $page = max( 1, (int) $page );

        // Clone to count without limit/offset.
        $count_builder = clone $this;
        $count_builder->limit_value = null;
        $count_builder->offset_value = null;
        $total = $count_builder->count();

        $this->limit( $per_page );
        $this->offset( ( $page - 1 ) * $per_page );

        $data = $this->get();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $per_page,
            'current_page' => $page,
            'last_page' => (int) ceil( $total / $per_page ),
        ];
    }

    // -------------------------------------------------------------------------
    // Execution — Aggregates
    // -------------------------------------------------------------------------

    /**
     * Get the sum of a column.
     *
     * @param string $column Column name.
     *
     * @return float
     */
    public function sum( $column ) {
        return (float) $this->aggregate( 'SUM', $column );
    }

    /**
     * Get the average of a column.
     *
     * @param string $column Column name.
     *
     * @return float
     */
    public function avg( $column ) {
        return (float) $this->aggregate( 'AVG', $column );
    }

    /**
     * Get the max of a column.
     *
     * @param string $column Column name.
     *
     * @return mixed
     */
    public function max( $column ) {
        return $this->aggregate( 'MAX', $column );
    }

    /**
     * Get the min of a column.
     *
     * @param string $column Column name.
     *
     * @return mixed
     */
    public function min( $column ) {
        return $this->aggregate( 'MIN', $column );
    }

    /**
     * Run an aggregate function.
     *
     * @param string $function SQL aggregate function name.
     * @param string $column   Column name.
     *
     * @return mixed
     */
    protected function aggregate( $function, $column ) {
        global $wpdb;

        $safe_column = sanitize_key( $column );
        $table = $this->model::getTable();
        $where = $this->compileWheres();

        $sql = "SELECT {$function}({$safe_column}) FROM {$table}";

        if ( $where ) {
            $sql .= " WHERE {$where}";
        }

        return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }

    // -------------------------------------------------------------------------
    // Execution — Write
    // -------------------------------------------------------------------------

    /**
     * Bulk update matching rows.
     *
     * @param array<string, mixed> $attributes Columns and values to update.
     *
     * @return int Number of affected rows.
     */
    public function update( array $attributes ) {
        global $wpdb;

        $table = $this->model::getTable();
        $set = [];

        foreach ( $attributes as $column => $value ) {
            $safe_column = sanitize_key( $column );

            if ( null === $value ) {
                $set[] = "{$safe_column} = NULL";
            } elseif ( is_int( $value ) || is_float( $value ) ) {
                $set[] = $wpdb->prepare( "{$safe_column} = %s", $value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            } else {
                $set[] = $wpdb->prepare( "{$safe_column} = %s", $value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            }
        }

        $set_sql = implode( ', ', $set );
        $where = $this->compileWheres();

        $sql = "UPDATE {$table} SET {$set_sql}";

        if ( $where ) {
            $sql .= " WHERE {$where}";
        }

        return (int) $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }

    /**
     * Bulk delete matching rows.
     *
     * @return int Number of affected rows.
     */
    public function delete() {
        global $wpdb;

        $table = $this->model::getTable();
        $where = $this->compileWheres();

        $sql = "DELETE FROM {$table}";

        if ( $where ) {
            $sql .= " WHERE {$where}";
        }

        return (int) $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }

    // -------------------------------------------------------------------------
    // SQL Compilation
    // -------------------------------------------------------------------------

    /**
     * Compile a SELECT SQL string.
     *
     * @param string $columns Columns to select (default '*').
     *
     * @return string
     */
    protected function toSelectSql( $columns = '*' ) {
        $table = $this->model::getTable();
        $sql = "SELECT {$columns} FROM {$table}";

        $where = $this->compileWheres();

        if ( $where ) {
            $sql .= " WHERE {$where}";
        }

        $sql .= $this->compileOrders();
        $sql .= $this->compileLimit();

        return $sql;
    }

    /**
     * Compile a COUNT SQL string.
     *
     * @return string
     */
    protected function toCountSql() {
        $table = $this->model::getTable();
        $sql = "SELECT COUNT(*) FROM {$table}";

        $where = $this->compileWheres();

        if ( $where ) {
            $sql .= " WHERE {$where}";
        }

        return $sql;
    }

    /**
     * Compile all WHERE clauses into SQL.
     *
     * @return string
     */
    protected function compileWheres() {
        global $wpdb;

        if ( empty( $this->wheres ) ) {
            return '';
        }

        $parts = [];

        foreach ( $this->wheres as $index => $where ) {
            $clause = $this->compileWhere( $where, $wpdb );

            if ( $index > 0 ) {
                $clause = $where['boolean'] . ' ' . $clause;
            }

            $parts[] = $clause;
        }

        return implode( ' ', $parts );
    }

    /**
     * Compile a single WHERE clause.
     *
     * @param array<string, mixed> $where WHERE clause definition.
     * @param \wpdb                $wpdb  WordPress database object.
     *
     * @return string
     */
    protected function compileWhere( $where, $wpdb ) {
        $column = sanitize_key( $where['column'] ?? '' );

        switch ( $where['type'] ) {
            case 'basic':
                $operator = $this->sanitizeOperator( $where['operator'] );

                if ( null === $where['value'] ) {
                    return 'IS' === $operator || '=' === $operator
                        ? "{$column} IS NULL"
                        : "{$column} IS NOT NULL";
                }

                return $wpdb->prepare( "{$column} {$operator} %s", $where['value'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            case 'in':
                if ( empty( $where['values'] ) ) {
                    return '1 = 0'; // Always false.
                }

                $placeholders = implode( ', ', array_fill( 0, count( $where['values'] ), '%s' ) );

                return $wpdb->prepare( "{$column} IN ({$placeholders})", ...$where['values'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

            case 'notIn':
                if ( empty( $where['values'] ) ) {
                    return '1 = 1'; // Always true.
                }

                $placeholders = implode( ', ', array_fill( 0, count( $where['values'] ), '%s' ) );

                return $wpdb->prepare( "{$column} NOT IN ({$placeholders})", ...$where['values'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

            case 'null':
                return "{$column} IS NULL";

            case 'notNull':
                return "{$column} IS NOT NULL";

            case 'between':
                return $wpdb->prepare( "{$column} BETWEEN %s AND %s", $where['min'], $where['max'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            case 'raw':
                /** @var string $raw_sql */
                $raw_sql = $where['sql'];

                if ( ! empty( $where['bindings'] ) ) {
                    return $wpdb->prepare( $raw_sql, ...$where['bindings'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                }

                return $raw_sql;

            default:
                return '1 = 1';
        }
    }

    /**
     * Compile ORDER BY clauses.
     *
     * @return string
     */
    protected function compileOrders() {
        if ( empty( $this->orders ) ) {
            return '';
        }

        $parts = array_map(
            function ( $order ) {
                $column = sanitize_key( $order['column'] );

                return "{$column} {$order['direction']}";
            },
            $this->orders,
        );

        return ' ORDER BY ' . implode( ', ', $parts );
    }

    /**
     * Compile LIMIT and OFFSET.
     *
     * @return string
     */
    protected function compileLimit() {
        $sql = '';

        if ( null !== $this->limit_value ) {
            $sql .= ' LIMIT ' . (int) $this->limit_value;
        }

        if ( null !== $this->offset_value ) {
            $sql .= ' OFFSET ' . (int) $this->offset_value;
        }

        return $sql;
    }

    /**
     * Sanitize an SQL operator.
     *
     * @param string $operator The operator.
     *
     * @return string
     */
    protected function sanitizeOperator( $operator ) {
        $allowed = [ '=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IS', 'IS NOT' ];
        $operator = strtoupper( trim( $operator ) );

        return in_array( $operator, $allowed, true ) ? $operator : '=';
    }
}
