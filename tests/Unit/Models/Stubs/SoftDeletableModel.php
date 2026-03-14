<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Stubs;

use WeDevs\WpUtils\Models\Model;
use WeDevs\WpUtils\Models\Traits\SoftDeletes;

/**
 * Stub model that uses only SoftDeletes.
 */
class SoftDeletableModel extends Model {

    use SoftDeletes;

    protected static $table = 'utils_soft_test';

    protected $fillable = [ 'name' ];

    protected static function getHookPrefix() {
        return 'wputils';
    }
}
