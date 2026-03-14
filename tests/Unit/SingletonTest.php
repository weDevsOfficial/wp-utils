<?php

namespace WeDevs\WpUtils\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WeDevs\WpUtils\Singleton;

class SingletonStubA {

    use Singleton;

    public $value = 'A';
}

class SingletonStubB {

    use Singleton;

    public $value = 'B';
}

class SingletonTest extends TestCase {

    protected function tearDown(): void {
        // Reset the static instances array between tests.
        $ref = new \ReflectionProperty( SingletonStubA::class, 'instances' );
        $ref->setAccessible( true );
        $ref->setValue( null, [] );

        parent::tearDown();
    }

    public function test_instance_returns_same_object(): void {
        $first = SingletonStubA::instance();
        $second = SingletonStubA::instance();

        $this->assertSame( $first, $second );
    }

    public function test_instance_returns_correct_class(): void {
        $instance = SingletonStubA::instance();

        $this->assertInstanceOf( SingletonStubA::class, $instance );
    }

    public function test_different_classes_get_separate_instances(): void {
        $a = SingletonStubA::instance();
        $b = SingletonStubB::instance();

        $this->assertNotSame( $a, $b );
        $this->assertInstanceOf( SingletonStubA::class, $a );
        $this->assertInstanceOf( SingletonStubB::class, $b );
    }

    public function test_instance_preserves_state(): void {
        $instance = SingletonStubA::instance();
        $instance->value = 'modified';

        $same = SingletonStubA::instance();

        $this->assertSame( 'modified', $same->value );
    }
}
