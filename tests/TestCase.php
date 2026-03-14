<?php

namespace WeDevs\WpUtils\Tests;

use Brain\Monkey;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;
use WeDevs\WpUtils\Models\Model;

/**
 * Base test case for all wp-utils tests.
 *
 * Sets up Brain\Monkey for WordPress function stubs,
 * creates a mock $wpdb, and resets static caches between tests.
 */
abstract class TestCase extends BaseTestCase {

    use MockeryPHPUnitIntegration;

    /**
     * The mocked wpdb instance.
     *
     * @var \Mockery\MockInterface|\wpdb
     */
    protected $wpdb;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();

        Monkey\setUp();

        // Create a mock wpdb and register it globally.
        $this->wpdb = Mockery::mock( 'wpdb' );
        $this->wpdb->prefix = 'wp_';
        $GLOBALS['wpdb'] = $this->wpdb;

        // Stub commonly used WordPress functions.
        $this->stubWordPressFunctions();
    }

    /**
     * Tear down the test environment.
     *
     * @return void
     */
    protected function tearDown(): void {
        // Reset the static boot caches so each test starts fresh.
        $this->resetModelStatics();

        unset( $GLOBALS['wpdb'] );

        Monkey\tearDown();

        parent::tearDown();
    }

    /**
     * Stub commonly used WordPress functions via Brain\Monkey.
     *
     * @return void
     */
    protected function stubWordPressFunctions(): void {
        Monkey\Functions\when( 'current_time' )->justReturn( '2026-03-01 12:00:00' );
        Monkey\Functions\when( 'get_current_user_id' )->justReturn( 1 );
        Monkey\Functions\when( 'wp_generate_uuid4' )->justReturn( 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee' );

        Monkey\Functions\when( 'sanitize_key' )->alias(
            function ( $key ) {
                return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
            },
        );

        Monkey\Functions\when( 'sanitize_text_field' )->alias(
            function ( $str ) {
                return trim( strip_tags( (string) $str ) );
            },
        );

        Monkey\Functions\when( '__' )->returnArg( 1 );
        Monkey\Functions\when( 'esc_html' )->returnArg( 1 );
        Monkey\Functions\when( 'esc_html__' )->returnArg( 1 );

        Monkey\Functions\when( 'absint' )->alias(
            function ( $val ) {
                return abs( (int) $val );
            },
        );
    }

    /**
     * Reset Model static properties to ensure test isolation.
     *
     * @return void
     */
    protected function resetModelStatics(): void {
        $ref = new \ReflectionProperty( Model::class, 'booted' );
        $ref->setAccessible( true );
        $ref->setValue( null, [] );

        $ref2 = new \ReflectionProperty( Model::class, 'trait_initializers' );
        $ref2->setAccessible( true );
        $ref2->setValue( null, [] );
    }

    /**
     * Helper: set up wpdb->prepare to do simple vsprintf substitution.
     *
     * @return void
     */
    protected function mockWpdbPrepare(): void {
        $this->wpdb->shouldReceive( 'prepare' )->andReturnUsing(
            function ( $query, ...$args ) {
                if ( empty( $args ) ) {
                    return $query;
                }

                $i = 0;

                return preg_replace_callback(
                    '/%[sd]/',
                    function ( $match ) use ( $args, &$i ) {
                        $val = $args[ $i ] ?? '';
                        $i++;

                        return '%d' === $match[0] ? (int) $val : "'" . $val . "'";
                    },
                    $query,
                );
            },
        );
    }
}
