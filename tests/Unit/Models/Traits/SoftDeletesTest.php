<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Traits;

use Brain\Monkey;
use WeDevs\WpUtils\Tests\TestCase;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\SoftDeletableModel;

class SoftDeletesTest extends TestCase {

    public function test_trashed_returns_false_when_deleted_at_is_null(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->assertFalse( $model->trashed() );
    }

    public function test_trashed_returns_true_when_deleted_at_is_set(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => '2026-01-01 00:00:00' ] );

        $this->assertTrue( $model->trashed() );
    }

    public function test_trash_sets_deleted_at(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        $result = $model->trash();

        $this->assertTrue( $result );
        $this->assertNotNull( $model->deleted_at );
    }

    public function test_trash_fires_hooks_with_prefix(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        Monkey\Actions\expectDone( 'wputils_soft_deletable_model_trashing' )
            ->once()
            ->with( $model );

        Monkey\Actions\expectDone( 'wputils_soft_deletable_model_trashed' )
            ->once()
            ->with( $model );

        $model->trash();
    }

    public function test_restore_clears_deleted_at(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => '2026-01-01 00:00:00' ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        $result = $model->restore();

        $this->assertTrue( $result );
        $this->assertNull( $model->deleted_at );
    }

    public function test_restore_fires_hooks_with_prefix(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => '2026-01-01 00:00:00' ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        Monkey\Actions\expectDone( 'wputils_soft_deletable_model_restoring' )
            ->once()
            ->with( $model );

        Monkey\Actions\expectDone( 'wputils_soft_deletable_model_restored' )
            ->once()
            ->with( $model );

        $model->restore();
    }

    public function test_delete_calls_trash_when_soft_deletes_present(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        $result = $model->delete();

        $this->assertTrue( $result );
        $this->assertNotNull( $model->deleted_at );
    }

    public function test_query_auto_excludes_soft_deleted(): void {
        $builder = SoftDeletableModel::query();

        $ref = new \ReflectionProperty( $builder, 'wheres' );
        $ref->setAccessible( true );
        $wheres = $ref->getValue( $builder );

        $this->assertNotEmpty( $wheres );
        $this->assertSame( 'null', $wheres[0]['type'] );
        $this->assertSame( 'deleted_at', $wheres[0]['column'] );
    }

    public function test_with_trashed_has_no_deleted_at_where_clause(): void {
        $builder = SoftDeletableModel::withTrashed();

        $ref = new \ReflectionProperty( $builder, 'wheres' );
        $ref->setAccessible( true );
        $wheres = $ref->getValue( $builder );

        $this->assertEmpty( $wheres );
    }

    public function test_only_trashed_has_where_not_null_deleted_at(): void {
        $builder = SoftDeletableModel::onlyTrashed();

        $ref = new \ReflectionProperty( $builder, 'wheres' );
        $ref->setAccessible( true );
        $wheres = $ref->getValue( $builder );

        $this->assertNotEmpty( $wheres );
        $this->assertSame( 'notNull', $wheres[0]['type'] );
        $this->assertSame( 'deleted_at', $wheres[0]['column'] );
    }

    public function test_trash_returns_false_on_wpdb_update_failure(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( false );

        $result = $model->trash();

        $this->assertFalse( $result );
        $this->assertNull( $model->deleted_at );
    }

    public function test_restore_returns_false_on_wpdb_update_failure(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => '2026-01-01 00:00:00' ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( false );

        $result = $model->restore();

        $this->assertFalse( $result );
        $this->assertSame( '2026-01-01 00:00:00', $model->deleted_at );
    }

    public function test_trash_then_restore_round_trip(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->wpdb->shouldReceive( 'update' )->andReturn( 1 );

        $model->trash();
        $this->assertTrue( $model->trashed() );

        $model->restore();
        $this->assertFalse( $model->trashed() );
    }

    public function test_force_delete_bypasses_soft_delete(): void {
        $model = SoftDeletableModel::newFromRow( [ 'id' => '1', 'deleted_at' => null ] );

        $this->wpdb->shouldReceive( 'delete' )->once()->andReturn( 1 );

        $result = $model->forceDelete();

        $this->assertTrue( $result );
        $this->assertFalse( $model->exists() );
    }
}
