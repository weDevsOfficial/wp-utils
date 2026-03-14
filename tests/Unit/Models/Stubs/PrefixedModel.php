<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Stubs;

use WeDevs\WpUtils\Models\Model;

/**
 * Stub model with a custom hook prefix.
 *
 * Verifies that consumers can override getHookPrefix() to namespace hooks.
 */
class PrefixedModel extends Model {

    protected static $table = 'myplugin_items';

    protected $fillable = [ 'name', 'email' ];

    /**
     * Override hook prefix to simulate a plugin using this library.
     *
     * @return string
     */
    protected static function getHookPrefix() {
        return 'myplugin';
    }
}
