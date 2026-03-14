<?php

namespace WeDevs\WpUtils\Tests\Unit\Models\Stubs;

use WeDevs\WpUtils\Models\Model;

/**
 * Concrete stub model for testing the abstract Model class.
 *
 * Uses no hook prefix (default behavior for the base Model).
 */
class StubModel extends Model {

    protected static $table = 'utils_stubs';

    protected $fillable = [ 'name', 'email', 'score', 'active', 'meta' ];

    protected $casts = [
        'id' => 'int',
        'score' => 'float',
        'active' => 'bool',
        'meta' => 'json',
    ];

    protected $hidden = [ 'secret' ];

    /**
     * Custom accessor: getDisplayNameAttribute.
     */
    public function getDisplayNameAttribute( $value ) {
        return strtoupper( $this->attributes['name'] ?? '' );
    }

    /**
     * Custom mutator: setEmailAttribute.
     */
    public function setEmailAttribute( $value ) {
        $this->attributes['email'] = strtolower( $value );
    }
}
