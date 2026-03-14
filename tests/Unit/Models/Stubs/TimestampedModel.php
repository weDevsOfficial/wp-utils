<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Stubs;

use WeDevs\WpUtils\Models\Model;
use WeDevs\WpUtils\Models\Traits\HasTimestamps;

/**
 * Stub model that uses only HasTimestamps.
 */
class TimestampedModel extends Model {

    use HasTimestamps;

    protected static $table = 'utils_ts_test';

    protected $fillable = [ 'name' ];

    protected static function getHookPrefix() {
        return 'wputils';
    }
}
