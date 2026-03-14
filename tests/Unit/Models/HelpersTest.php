<?php

namespace WeDevs\WpUtils\Tests\Unit\Models;

use WeDevs\WpUtils\Models\Traits\HasHash;
use WeDevs\WpUtils\Models\Traits\HasTimestamps;
use WeDevs\WpUtils\Models\Traits\SoftDeletes;
use WeDevs\WpUtils\Tests\TestCase;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\HashableModel;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\SoftDeletableModel;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\StubModel;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\TimestampedModel;

class HelpersTest extends TestCase {

    public function test_class_uses_recursive_returns_empty_for_no_traits(): void {
        $traits = wputils_class_uses_recursive( StubModel::class );

        $this->assertIsArray( $traits );
        $this->assertEmpty( $traits );
    }

    public function test_class_uses_recursive_returns_soft_deletes_trait(): void {
        $traits = wputils_class_uses_recursive( SoftDeletableModel::class );

        $this->assertContains( SoftDeletes::class, $traits );
    }

    public function test_class_uses_recursive_returns_has_hash_trait(): void {
        $traits = wputils_class_uses_recursive( HashableModel::class );

        $this->assertContains( HasHash::class, $traits );
    }

    public function test_class_uses_recursive_returns_has_timestamps_trait(): void {
        $traits = wputils_class_uses_recursive( TimestampedModel::class );

        $this->assertContains( HasTimestamps::class, $traits );
    }

    public function test_class_uses_recursive_accepts_object_instance(): void {
        $model = new StubModel();
        $traits = wputils_class_uses_recursive( $model );

        $this->assertIsArray( $traits );
    }

    public function test_trait_uses_recursive_returns_empty_for_class_with_no_traits(): void {
        $traits = wputils_trait_uses_recursive( StubModel::class );

        $this->assertIsArray( $traits );
        $this->assertEmpty( $traits );
    }
}
