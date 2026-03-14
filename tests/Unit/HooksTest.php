<?php

namespace WeDevs\WpUtils\Tests\Unit;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use WeDevs\WpUtils\Hooks;

class HooksStub {

    use Hooks;

    public function on_init() {
        // stub callback.
    }

    public function filter_title( $title ) {
        return $title . ' - Modified';
    }
}

class HooksTest extends TestCase {

    use MockeryPHPUnitIntegration;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_add_action_calls_wp_add_action(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'add_action' )
            ->once()
            ->with( 'init', [ $obj, 'on_init' ], 10, 1 )
            ->andReturn( true );

        $result = $obj->add_action( 'init', 'on_init' );

        $this->assertTrue( $result );
    }

    public function test_add_action_with_custom_priority_and_args(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'add_action' )
            ->once()
            ->with( 'save_post', [ $obj, 'on_init' ], 20, 3 )
            ->andReturn( true );

        $obj->add_action( 'save_post', 'on_init', 20, 3 );
    }

    public function test_add_filter_calls_wp_add_filter(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'add_filter' )
            ->once()
            ->with( 'the_title', [ $obj, 'filter_title' ], 10, 1 )
            ->andReturn( true );

        $result = $obj->add_filter( 'the_title', 'filter_title' );

        $this->assertTrue( $result );
    }

    public function test_add_filter_with_custom_priority(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'add_filter' )
            ->once()
            ->with( 'the_content', [ $obj, 'filter_title' ], 99, 2 )
            ->andReturn( true );

        $obj->add_filter( 'the_content', 'filter_title', 99, 2 );
    }

    public function test_remove_action_calls_wp_remove_action(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'remove_action' )
            ->once()
            ->with( 'init', [ $obj, 'on_init' ], 10 )
            ->andReturn( true );

        $result = $obj->remove_action( 'init', 'on_init' );

        $this->assertTrue( $result );
    }

    public function test_remove_action_with_custom_priority(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'remove_action' )
            ->once()
            ->with( 'init', [ $obj, 'on_init' ], 20 )
            ->andReturn( true );

        $obj->remove_action( 'init', 'on_init', 20 );
    }

    public function test_remove_filter_calls_wp_remove_filter(): void {
        $obj = new HooksStub();

        Monkey\Functions\expect( 'remove_filter' )
            ->once()
            ->with( 'the_title', [ $obj, 'filter_title' ], 10 )
            ->andReturn( true );

        $result = $obj->remove_filter( 'the_title', 'filter_title' );

        $this->assertTrue( $result );
    }
}
