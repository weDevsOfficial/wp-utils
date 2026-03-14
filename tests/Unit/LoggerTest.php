<?php

namespace WeDevs\WpUtils\Tests\Unit;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use WeDevs\WpUtils\Logger;

/**
 * Test stub that captures log output instead of calling error_log().
 */
class LoggerStub {

    use Logger {
        log as traitLog;
    }

    /**
     * Captured log messages.
     *
     * @var array<int, string>
     */
    public $logged = [];

    /**
     * Override log to capture messages instead of writing to error_log.
     *
     * @param string $message Log message.
     * @param string $level   Log level.
     * @param array  $context Context data.
     *
     * @return void
     */
    protected function log( $message, $level = 'info', array $context = [] ) {
        if ( 'debug' === $level && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
            return;
        }

        $formatted = sprintf(
            '[%s][%s] %s',
            strtoupper( $level ),
            static::class,
            $message,
        );

        if ( ! empty( $context ) ) {
            $formatted .= ' ' . wp_json_encode( $context );
        }

        $this->logged[] = $formatted;
    }
}

class LoggerTest extends TestCase {

    use MockeryPHPUnitIntegration;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        Monkey\Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_log_info_formats_with_info_level(): void {
        $obj = new LoggerStub();
        $obj->log_info( 'Something happened' );

        $this->assertCount( 1, $obj->logged );
        $this->assertStringContainsString( '[INFO]', $obj->logged[0] );
        $this->assertStringContainsString( 'Something happened', $obj->logged[0] );
    }

    public function test_log_error_formats_with_error_level(): void {
        $obj = new LoggerStub();
        $obj->log_error( 'Failure' );

        $this->assertStringContainsString( '[ERROR]', $obj->logged[0] );
        $this->assertStringContainsString( 'Failure', $obj->logged[0] );
    }

    public function test_log_warning_formats_with_warning_level(): void {
        $obj = new LoggerStub();
        $obj->log_warning( 'Caution' );

        $this->assertStringContainsString( '[WARNING]', $obj->logged[0] );
        $this->assertStringContainsString( 'Caution', $obj->logged[0] );
    }

    public function test_log_debug_skips_when_wp_debug_is_off(): void {
        $obj = new LoggerStub();
        $obj->log_debug( 'Debug message' );

        $this->assertEmpty( $obj->logged );
    }

    public function test_log_includes_class_name(): void {
        $obj = new LoggerStub();
        $obj->log_info( 'test' );

        $this->assertStringContainsString( 'LoggerStub', $obj->logged[0] );
    }

    public function test_log_with_context_appends_json(): void {
        $obj = new LoggerStub();
        $obj->log_error( 'Query failed', [ 'user_id' => 5 ] );

        $this->assertStringContainsString( '{"user_id":5}', $obj->logged[0] );
    }

    public function test_log_without_context_has_no_json(): void {
        $obj = new LoggerStub();
        $obj->log_info( 'Clean message' );

        $this->assertStringNotContainsString( '{', $obj->logged[0] );
    }

    public function test_info_does_not_check_wp_debug(): void {
        $obj = new LoggerStub();
        $obj->log_info( 'Always logs' );

        $this->assertCount( 1, $obj->logged );
    }

    public function test_multiple_log_calls_accumulate(): void {
        $obj = new LoggerStub();
        $obj->log_info( 'first' );
        $obj->log_error( 'second' );
        $obj->log_warning( 'third' );

        $this->assertCount( 3, $obj->logged );
    }
}
