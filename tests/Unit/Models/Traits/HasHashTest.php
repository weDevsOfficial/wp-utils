<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Traits;

use Brain\Monkey;
use WeDevs\WpUtils\Tests\TestCase;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\HashableModel;

class HasHashTest extends TestCase {

    public function test_boot_registers_creating_action(): void {
        Monkey\Actions\expectAdded( 'wputils_hashable_model_creating' )->once();

        new HashableModel();
    }

    public function test_hash_can_be_set_as_attribute(): void {
        $model = new HashableModel();
        $model->fill( [ 'name' => 'Test' ] );

        // Simulate what the boot creating callback does.
        $model->setAttribute( 'hash', wp_generate_uuid4() );

        $this->assertSame( 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', $model->hash );
    }

    public function test_get_route_key_returns_hash(): void {
        $model = HashableModel::newFromRow( [ 'id' => '1', 'hash' => 'my-hash-123' ] );

        $this->assertSame( 'my-hash-123', $model->getRouteKey() );
    }

    public function test_find_by_hash_returns_model_when_found(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn(
            [ (object) [ 'id' => '1', 'hash' => 'abc-123', 'name' => 'Found' ] ],
        );

        $model = HashableModel::findByHash( 'abc-123' );

        $this->assertNotNull( $model );
        $this->assertSame( 'abc-123', $model->hash );
    }

    public function test_find_by_hash_returns_null_when_not_found(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn( [] );

        $model = HashableModel::findByHash( 'nonexistent' );

        $this->assertNull( $model );
    }

    public function test_find_by_hash_or_fail_throws_when_not_found(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn( [] );

        $this->expectException( \RuntimeException::class );

        HashableModel::findByHashOrFail( 'nonexistent-hash' );
    }

    public function test_find_by_hash_or_fail_returns_model_when_found(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn(
            [ (object) [ 'id' => '1', 'hash' => 'found-hash' ] ],
        );

        $model = HashableModel::findByHashOrFail( 'found-hash' );

        $this->assertNotNull( $model );
        $this->assertSame( 'found-hash', $model->hash );
    }

    public function test_new_from_row_preserves_hash(): void {
        $model = HashableModel::newFromRow( [ 'id' => '1', 'hash' => 'existing-hash-value' ] );

        $this->assertSame( 'existing-hash-value', $model->hash );
    }
}
