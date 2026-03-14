<?php

namespace WeDevs\WpUtils\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WeDevs\WpUtils\Container;

class ContainerStub {

    use Container;
}

class ContainerTest extends TestCase {

    public function test_set_and_get_property(): void {
        $obj = new ContainerStub();
        $obj->foo = 'bar';

        $this->assertSame( 'bar', $obj->foo );
    }

    public function test_get_returns_null_for_missing_property(): void {
        $obj = new ContainerStub();

        $this->assertNull( $obj->nonexistent );
    }

    public function test_isset_returns_true_for_set_property(): void {
        $obj = new ContainerStub();
        $obj->service = new \stdClass();

        $this->assertTrue( isset( $obj->service ) );
    }

    public function test_isset_returns_false_for_missing_property(): void {
        $obj = new ContainerStub();

        $this->assertFalse( isset( $obj->missing ) );
    }

    public function test_isset_returns_true_for_null_value(): void {
        $obj = new ContainerStub();
        $obj->nullable = null;

        $this->assertTrue( isset( $obj->nullable ) );
    }

    public function test_get_returns_null_for_null_value(): void {
        $obj = new ContainerStub();
        $obj->nullable = null;

        $this->assertNull( $obj->nullable );
    }

    public function test_unset_removes_property(): void {
        $obj = new ContainerStub();
        $obj->temp = 'value';

        unset( $obj->temp );

        $this->assertFalse( isset( $obj->temp ) );
        $this->assertNull( $obj->temp );
    }

    public function test_overwrite_property(): void {
        $obj = new ContainerStub();
        $obj->key = 'first';
        $obj->key = 'second';

        $this->assertSame( 'second', $obj->key );
    }

    public function test_stores_different_types(): void {
        $obj = new ContainerStub();

        $obj->string = 'hello';
        $obj->int = 42;
        $obj->array = [ 1, 2, 3 ];
        $obj->object = new \stdClass();
        $obj->bool = true;

        $this->assertSame( 'hello', $obj->string );
        $this->assertSame( 42, $obj->int );
        $this->assertSame( [ 1, 2, 3 ], $obj->array );
        $this->assertInstanceOf( \stdClass::class, $obj->object );
        $this->assertTrue( $obj->bool );
    }

    public function test_multiple_properties_are_independent(): void {
        $obj = new ContainerStub();
        $obj->a = 'alpha';
        $obj->b = 'beta';

        unset( $obj->a );

        $this->assertNull( $obj->a );
        $this->assertSame( 'beta', $obj->b );
    }
}
