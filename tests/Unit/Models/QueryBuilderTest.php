<?php

namespace WeDevs\WpUtils\Tests\Unit\Models;

use WeDevs\WpUtils\Models\Collection;
use WeDevs\WpUtils\Models\QueryBuilder;
use WeDevs\WpUtils\Tests\TestCase;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\StubModel;

class QueryBuilderTest extends TestCase {

    /**
     * Create a fresh QueryBuilder instance for testing.
     *
     * @return QueryBuilder
     */
    protected function newBuilder(): QueryBuilder {
        return new QueryBuilder( new StubModel() );
    }

    /**
     * Use reflection to access a protected property.
     *
     * @param QueryBuilder $builder  The builder.
     * @param string       $property Property name.
     *
     * @return mixed
     */
    protected function getProperty( QueryBuilder $builder, string $property ) {
        $ref = new \ReflectionProperty( QueryBuilder::class, $property );
        $ref->setAccessible( true );

        return $ref->getValue( $builder );
    }

    /**
     * Use reflection to call a protected method.
     *
     * @param QueryBuilder     $builder The builder.
     * @param string           $method  Method name.
     * @param array<int,mixed> $args    Arguments.
     *
     * @return mixed
     */
    protected function callMethod( QueryBuilder $builder, string $method, array $args = [] ) {
        $ref = new \ReflectionMethod( QueryBuilder::class, $method );
        $ref->setAccessible( true );

        return $ref->invoke( $builder, ...$args );
    }

    // -------------------------------------------------------------------------
    // WHERE clause building (state verification)
    // -------------------------------------------------------------------------

    public function test_where_adds_basic_where_with_equals(): void {
        $builder = $this->newBuilder()->where( 'name', 'Alice' );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertCount( 1, $wheres );
        $this->assertSame( 'basic', $wheres[0]['type'] );
        $this->assertSame( 'name', $wheres[0]['column'] );
        $this->assertSame( '=', $wheres[0]['operator'] );
        $this->assertSame( 'Alice', $wheres[0]['value'] );
        $this->assertSame( 'AND', $wheres[0]['boolean'] );
    }

    public function test_where_with_explicit_operator(): void {
        $builder = $this->newBuilder()->where( 'score', '>', 10 );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( '>', $wheres[0]['operator'] );
        $this->assertSame( 10, $wheres[0]['value'] );
    }

    public function test_or_where_sets_boolean_to_or(): void {
        $builder = $this->newBuilder()
            ->where( 'name', 'Alice' )
            ->orWhere( 'name', 'Bob' );

        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'AND', $wheres[0]['boolean'] );
        $this->assertSame( 'OR', $wheres[1]['boolean'] );
    }

    public function test_where_in_adds_in_clause(): void {
        $builder = $this->newBuilder()->whereIn( 'id', [ 1, 2, 3 ] );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'in', $wheres[0]['type'] );
        $this->assertSame( [ 1, 2, 3 ], $wheres[0]['values'] );
    }

    public function test_where_not_in_adds_not_in_clause(): void {
        $builder = $this->newBuilder()->whereNotIn( 'id', [ 4, 5 ] );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'notIn', $wheres[0]['type'] );
        $this->assertSame( [ 4, 5 ], $wheres[0]['values'] );
    }

    public function test_where_null_adds_null_clause(): void {
        $builder = $this->newBuilder()->whereNull( 'deleted_at' );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'null', $wheres[0]['type'] );
        $this->assertSame( 'deleted_at', $wheres[0]['column'] );
    }

    public function test_where_not_null_adds_not_null_clause(): void {
        $builder = $this->newBuilder()->whereNotNull( 'email' );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'notNull', $wheres[0]['type'] );
    }

    public function test_where_between_adds_between_clause(): void {
        $builder = $this->newBuilder()->whereBetween( 'score', 1, 10 );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'between', $wheres[0]['type'] );
        $this->assertSame( 1, $wheres[0]['min'] );
        $this->assertSame( 10, $wheres[0]['max'] );
    }

    public function test_where_raw_adds_raw_clause(): void {
        $builder = $this->newBuilder()->whereRaw( 'score > 5' );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( 'raw', $wheres[0]['type'] );
        $this->assertSame( 'score > 5', $wheres[0]['sql'] );
    }

    public function test_where_raw_with_bindings(): void {
        $builder = $this->newBuilder()->whereRaw( 'score > %d', [ 5 ] );
        $wheres = $this->getProperty( $builder, 'wheres' );

        $this->assertSame( [ 5 ], $wheres[0]['bindings'] );
    }

    // -------------------------------------------------------------------------
    // ORDER BY
    // -------------------------------------------------------------------------

    public function test_order_by_adds_order_clause(): void {
        $builder = $this->newBuilder()->orderBy( 'name', 'DESC' );
        $orders = $this->getProperty( $builder, 'orders' );

        $this->assertCount( 1, $orders );
        $this->assertSame( 'name', $orders[0]['column'] );
        $this->assertSame( 'DESC', $orders[0]['direction'] );
    }

    public function test_order_by_defaults_to_asc(): void {
        $builder = $this->newBuilder()->orderBy( 'name' );
        $orders = $this->getProperty( $builder, 'orders' );

        $this->assertSame( 'ASC', $orders[0]['direction'] );
    }

    public function test_order_by_normalizes_invalid_direction_to_asc(): void {
        $builder = $this->newBuilder()->orderBy( 'name', 'RANDOM' );
        $orders = $this->getProperty( $builder, 'orders' );

        $this->assertSame( 'ASC', $orders[0]['direction'] );
    }

    public function test_multiple_order_by_clauses(): void {
        $builder = $this->newBuilder()
            ->orderBy( 'last_name', 'ASC' )
            ->orderBy( 'first_name', 'ASC' );

        $orders = $this->getProperty( $builder, 'orders' );

        $this->assertCount( 2, $orders );
    }

    // -------------------------------------------------------------------------
    // LIMIT / OFFSET
    // -------------------------------------------------------------------------

    public function test_limit_sets_limit_value(): void {
        $builder = $this->newBuilder()->limit( 10 );

        $this->assertSame( 10, $this->getProperty( $builder, 'limit_value' ) );
    }

    public function test_offset_sets_offset_value(): void {
        $builder = $this->newBuilder()->offset( 20 );

        $this->assertSame( 20, $this->getProperty( $builder, 'offset_value' ) );
    }

    // -------------------------------------------------------------------------
    // SQL Compilation (via reflection)
    // -------------------------------------------------------------------------

    public function test_compile_select_sql_basic(): void {
        $builder = $this->newBuilder();
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertSame( 'SELECT * FROM wp_utils_stubs', $sql );
    }

    public function test_compile_select_sql_with_single_where(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()->where( 'name', 'Alice' );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'WHERE', $sql );
        $this->assertStringContainsString( 'name', $sql );
        $this->assertStringContainsString( "'Alice'", $sql );
    }

    public function test_compile_select_sql_with_multiple_wheres(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()
            ->where( 'name', 'Alice' )
            ->where( 'score', '>', 5 );

        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'AND', $sql );
    }

    public function test_compile_select_sql_with_or_where(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()
            ->where( 'name', 'Alice' )
            ->orWhere( 'name', 'Bob' );

        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'OR', $sql );
    }

    public function test_compile_select_sql_with_where_in(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()->whereIn( 'id', [ 1, 2, 3 ] );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'IN', $sql );
    }

    public function test_compile_select_sql_with_where_in_empty(): void {
        $builder = $this->newBuilder()->whereIn( 'id', [] );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( '1 = 0', $sql );
    }

    public function test_compile_select_sql_with_where_not_in_empty(): void {
        $builder = $this->newBuilder()->whereNotIn( 'id', [] );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( '1 = 1', $sql );
    }

    public function test_compile_select_sql_with_where_null(): void {
        $builder = $this->newBuilder()->whereNull( 'deleted_at' );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'deleted_at IS NULL', $sql );
    }

    public function test_compile_select_sql_with_where_not_null(): void {
        $builder = $this->newBuilder()->whereNotNull( 'email' );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'email IS NOT NULL', $sql );
    }

    public function test_compile_select_sql_with_where_between(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()->whereBetween( 'score', 1, 10 );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'BETWEEN', $sql );
    }

    public function test_compile_select_sql_with_where_raw(): void {
        $builder = $this->newBuilder()->whereRaw( 'score > 5' );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'score > 5', $sql );
    }

    public function test_compile_select_sql_with_where_raw_and_bindings(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()->whereRaw( 'score > %d', [ 5 ] );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'score > 5', $sql );
    }

    public function test_compile_select_sql_with_order_by(): void {
        $builder = $this->newBuilder()->orderBy( 'name', 'DESC' );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'ORDER BY name DESC', $sql );
    }

    public function test_compile_select_sql_with_limit_and_offset(): void {
        $builder = $this->newBuilder()->limit( 10 )->offset( 20 );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'LIMIT 10', $sql );
        $this->assertStringContainsString( 'OFFSET 20', $sql );
    }

    public function test_compile_select_sql_with_custom_columns(): void {
        $builder = $this->newBuilder();
        $sql = $this->callMethod( $builder, 'toSelectSql', [ 'id, name' ] );

        $this->assertStringContainsString( 'SELECT id, name FROM', $sql );
    }

    public function test_compile_count_sql(): void {
        $builder = $this->newBuilder();
        $sql = $this->callMethod( $builder, 'toCountSql' );

        $this->assertSame( 'SELECT COUNT(*) FROM wp_utils_stubs', $sql );
    }

    public function test_compile_count_sql_with_where(): void {
        $this->mockWpdbPrepare();

        $builder = $this->newBuilder()->where( 'active', 1 );
        $sql = $this->callMethod( $builder, 'toCountSql' );

        $this->assertStringContainsString( 'SELECT COUNT(*) FROM', $sql );
        $this->assertStringContainsString( 'WHERE', $sql );
    }

    public function test_compile_where_basic_with_null_value_produces_is_null(): void {
        $builder = $this->newBuilder()->where( 'deleted_at', null );
        $sql = $this->callMethod( $builder, 'toSelectSql' );

        $this->assertStringContainsString( 'deleted_at IS NULL', $sql );
    }

    // -------------------------------------------------------------------------
    // Operator sanitization
    // -------------------------------------------------------------------------

    public function test_sanitize_operator_allows_valid_operators(): void {
        $valid = [ '=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IS', 'IS NOT' ];

        foreach ( $valid as $op ) {
            $result = $this->callMethod( $this->newBuilder(), 'sanitizeOperator', [ $op ] );
            $this->assertSame( $op, $result, "Operator '{$op}' should be allowed." );
        }
    }

    public function test_sanitize_operator_defaults_invalid_to_equals(): void {
        $result = $this->callMethod( $this->newBuilder(), 'sanitizeOperator', [ 'DROP TABLE' ] );

        $this->assertSame( '=', $result );
    }

    // -------------------------------------------------------------------------
    // Execution methods (mocked $wpdb)
    // -------------------------------------------------------------------------

    public function test_get_returns_collection(): void {
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn(
            [
                (object) [ 'id' => '1', 'name' => 'Alice' ],
                (object) [ 'id' => '2', 'name' => 'Bob' ],
            ],
        );

        $collection = $this->newBuilder()->get();

        $this->assertInstanceOf( Collection::class, $collection );
        $this->assertCount( 2, $collection );
    }

    public function test_get_returns_empty_collection_on_null_result(): void {
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( null );

        $collection = $this->newBuilder()->get();

        $this->assertInstanceOf( Collection::class, $collection );
        $this->assertCount( 0, $collection );
    }

    public function test_first_returns_single_model(): void {
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn(
            [ (object) [ 'id' => '1', 'name' => 'Alice' ] ],
        );

        $model = $this->newBuilder()->first();

        $this->assertInstanceOf( StubModel::class, $model );
        $this->assertSame( 'Alice', $model->name );
    }

    public function test_first_returns_null_when_empty(): void {
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );

        $model = $this->newBuilder()->first();

        $this->assertNull( $model );
    }

    public function test_find_returns_model_by_primary_key(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn(
            [ (object) [ 'id' => '5', 'name' => 'Found' ] ],
        );

        $model = $this->newBuilder()->find( 5 );

        $this->assertNotNull( $model );
        $this->assertSame( 5, $model->id );
    }

    public function test_count_returns_integer(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '5' );

        $count = $this->newBuilder()->count();

        $this->assertSame( 5, $count );
    }

    public function test_exists_returns_true_when_count_greater_than_zero(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '3' );

        $this->assertTrue( $this->newBuilder()->exists() );
    }

    public function test_exists_returns_false_when_count_is_zero(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '0' );

        $this->assertFalse( $this->newBuilder()->exists() );
    }

    public function test_pluck_returns_column_values(): void {
        $this->wpdb->shouldReceive( 'get_col' )->once()->andReturn( [ 'Alice', 'Bob' ] );

        $result = $this->newBuilder()->pluck( 'name' );

        $this->assertSame( [ 'Alice', 'Bob' ], $result );
    }

    public function test_pluck_returns_empty_array_on_empty_result(): void {
        $this->wpdb->shouldReceive( 'get_col' )->once()->andReturn( [] );

        $result = $this->newBuilder()->pluck( 'name' );

        $this->assertSame( [], $result );
    }

    public function test_paginate_returns_correct_structure(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '50' );
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );

        $result = $this->newBuilder()->paginate( 10, 2 );

        $this->assertArrayHasKey( 'data', $result );
        $this->assertArrayHasKey( 'total', $result );
        $this->assertArrayHasKey( 'per_page', $result );
        $this->assertArrayHasKey( 'current_page', $result );
        $this->assertArrayHasKey( 'last_page', $result );

        $this->assertSame( 50, $result['total'] );
        $this->assertSame( 10, $result['per_page'] );
        $this->assertSame( 2, $result['current_page'] );
        $this->assertSame( 5, $result['last_page'] );
    }

    public function test_paginate_handles_page_one(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '3' );
        $this->wpdb->shouldReceive( 'get_results' )->once()->andReturn( [] );

        $result = $this->newBuilder()->paginate( 10, 1 );

        $this->assertSame( 1, $result['current_page'] );
        $this->assertSame( 1, $result['last_page'] );
    }

    public function test_sum_returns_float(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '150.5' );

        $result = $this->newBuilder()->sum( 'score' );

        $this->assertSame( 150.5, $result );
    }

    public function test_avg_returns_float(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '7.25' );

        $result = $this->newBuilder()->avg( 'score' );

        $this->assertSame( 7.25, $result );
    }

    public function test_max_returns_value(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '100' );

        $result = $this->newBuilder()->max( 'score' );

        $this->assertSame( '100', $result );
    }

    public function test_min_returns_value(): void {
        $this->wpdb->shouldReceive( 'get_var' )->once()->andReturn( '1' );

        $result = $this->newBuilder()->min( 'score' );

        $this->assertSame( '1', $result );
    }

    public function test_bulk_update_calls_wpdb_query(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'query' )->once()->andReturn( 3 );

        $builder = $this->newBuilder()->where( 'active', 1 );
        $affected = $builder->update( [ 'name' => 'Updated' ] );

        $this->assertSame( 3, $affected );
    }

    public function test_bulk_update_handles_null_value(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'query' )->once()->andReturn( 1 );

        $affected = $this->newBuilder()->where( 'id', 1 )->update( [ 'email' => null ] );

        $this->assertSame( 1, $affected );
    }

    public function test_bulk_delete_calls_wpdb_query(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'query' )->once()->andReturn( 2 );

        $builder = $this->newBuilder()->where( 'active', 0 );
        $affected = $builder->delete();

        $this->assertSame( 2, $affected );
    }

    // -------------------------------------------------------------------------
    // Chaining returns $this
    // -------------------------------------------------------------------------

    public function test_methods_return_builder_for_chaining(): void {
        $builder = $this->newBuilder();

        $this->assertSame( $builder, $builder->where( 'a', 1 ) );
        $this->assertSame( $builder, $builder->orWhere( 'b', 2 ) );
        $this->assertSame( $builder, $builder->whereIn( 'c', [ 1 ] ) );
        $this->assertSame( $builder, $builder->whereNotIn( 'd', [ 2 ] ) );
        $this->assertSame( $builder, $builder->whereNull( 'e' ) );
        $this->assertSame( $builder, $builder->whereNotNull( 'f' ) );
        $this->assertSame( $builder, $builder->whereBetween( 'g', 1, 10 ) );
        $this->assertSame( $builder, $builder->whereRaw( '1=1' ) );
        $this->assertSame( $builder, $builder->orderBy( 'h' ) );
        $this->assertSame( $builder, $builder->limit( 5 ) );
        $this->assertSame( $builder, $builder->offset( 10 ) );
    }
}
