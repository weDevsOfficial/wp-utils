<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Stubs;

use WeDevs\WpUtils\Models\Model;
use WeDevs\WpUtils\Models\Traits\HasHash;

/**
 * Stub model that uses only HasHash.
 */
class HashableModel extends Model {

    use HasHash;

    protected static $table = 'utils_hash_test';

    protected $fillable = [ 'name' ];

    protected static function getHookPrefix() {
        return 'wputils';
    }
}
