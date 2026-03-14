<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Traits;

use Brain\Monkey;
use WeDevs\WpUtils\Tests\TestCase;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\TimestampedModel;

class HasTimestampsTest extends TestCase {

    public function test_boot_registers_creating_and_updating_actions(): void {
        Monkey\Actions\expectAdded( 'wputils_timestamped_model_creating' )->once();
        Monkey\Actions\expectAdded( 'wputils_timestamped_model_updating' )->once();

        new TimestampedModel();
    }

    public function test_fresh_timestamp_returns_current_time(): void {
        $model = new TimestampedModel();

        $this->assertSame( '2026-03-01 12:00:00', $model->freshTimestamp() );
    }

    public function test_model_can_set_timestamps_directly(): void {
        $model = new TimestampedModel();

        $model->setAttribute( 'created_at', $model->freshTimestamp() );
        $model->setAttribute( 'updated_at', $model->freshTimestamp() );

        $this->assertSame( '2026-03-01 12:00:00', $model->created_at );
        $this->assertSame( '2026-03-01 12:00:00', $model->updated_at );
    }

    public function test_existing_timestamp_is_preserved(): void {
        $model = TimestampedModel::newFromRow( [
            'id' => '1',
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
        ] );

        $this->assertSame( '2020-01-01 00:00:00', $model->created_at );
    }

    public function test_updated_at_can_be_changed(): void {
        $model = TimestampedModel::newFromRow( [
            'id' => '1',
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
        ] );

        $model->setAttribute( 'updated_at', '2026-03-01 12:00:00' );

        $this->assertSame( '2026-03-01 12:00:00', $model->updated_at );
    }
}
