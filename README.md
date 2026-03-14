# WordPress Utils

[![License: LGPL v3](https://img.shields.io/badge/License-GPL_v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Packagist](https://img.shields.io/packagist/v/wedevs/wp-utils.svg)](https://packagist.org/packages/wedevs/wp-utils)
![GitHub all releases](https://img.shields.io/github/downloads/weDevsOfficial/wp-utils/total?label=GitHub%20Downloads)
![Packagist Downloads](https://img.shields.io/packagist/dt/wedevs/wp-utils?label=Packagist)

A collection of useful utilities for WordPress plugin development. Includes an Eloquent-inspired ORM, reusable traits, and common helpers.

## Installation

```bash
composer require wedevs/wp-utils
```

## Models & ORM

An Eloquent-inspired ORM built for WordPress. Define models, query with a fluent builder, and get results as typed collections.

```php
use WeDevs\WpUtils\Models\Model;
use WeDevs\WpUtils\Models\Traits\HasTimestamps;
use WeDevs\WpUtils\Models\Traits\SoftDeletes;

class Contact extends Model {
    use HasTimestamps, SoftDeletes;

    protected static $table = 'crm_contacts';

    protected $fillable = [ 'first_name', 'last_name', 'email', 'phone' ];

    protected $casts = [
        'id' => 'int',
        'is_active' => 'bool',
    ];

    protected static function getHookPrefix() {
        return 'myplugin';
    }
}
```

### Quick Examples

```php
// Create
$contact = Contact::create( [ 'first_name' => 'John', 'email' => 'john@example.com' ] );

// Find
$contact = Contact::find( 1 );
$contact = Contact::findBy( 'email', 'john@example.com' );

// Query
$contacts = Contact::query()
    ->where( 'status', 'active' )
    ->where( 'age', '>=', 18 )
    ->orderBy( 'created_at', 'DESC' )
    ->limit( 10 )
    ->get();

// Update
$contact->update( [ 'phone' => '555-1234' ] );

// Soft delete & restore
$contact->trash();
$contact->restore();

// Aggregates
$count = Contact::query()->where( 'active', 1 )->count();
$total = Contact::query()->sum( 'amount' );

// Pagination
$result = Contact::query()->paginate( 20, 1 );
// [ 'data' => Collection, 'total' => 150, 'per_page' => 20, ... ]
```

### Available Traits

| Trait | Description |
|-------|-------------|
| `HasTimestamps` | Auto-manages `created_at` and `updated_at` columns |
| `SoftDeletes` | Soft-delete via `deleted_at` with auto-scoping |
| `HasHash` | Auto-generates UUID v4 hash on creation |

### Full Documentation

See **[docs/models.md](docs/models.md)** for the complete guide covering:

- Model definition and configuration
- Hook prefix system for namespaced WordPress hooks
- Query builder (WHERE, ORDER, LIMIT, aggregates, pagination, bulk operations)
- Accessors, mutators, and dirty tracking
- Collections API
- Trait details (SoftDeletes, HasTimestamps, HasHash)
- Lifecycle hooks and filters reference

---

## Traits

### Container

Dynamic property storage via `__get`, `__set`, `__isset`, and `__unset` magic methods.

```php
use WeDevs\WpUtils\Container;

class MyPlugin {
    use Container;

    public function __construct() {
        $this->my_service = new MyService();
        $this->my_service->doSomething();
    }
}
```

### Hooks

Convenience methods for WordPress action and filter hooks.

```php
use WeDevs\WpUtils\Hooks;

class MyPlugin {
    use Hooks;

    public function __construct() {
        $this->add_action( 'init', 'on_init' );
        $this->add_filter( 'the_title', 'filter_title' );
    }

    public function on_init() {
        // ...
    }

    public function filter_title( $title ) {
        return $title . ' - Modified';
    }
}
```

### Logger

Simple logging with level support and optional context data. Debug messages only log when `WP_DEBUG` is enabled.

```php
use WeDevs\WpUtils\Logger;

class MyPlugin {
    use Logger;

    public function some_method() {
        $this->log_info( 'User logged in', [ 'user_id' => 5 ] );
        $this->log_error( 'Payment failed', [ 'order_id' => 123 ] );
        $this->log_warning( 'Rate limit approaching' );
        $this->log_debug( 'Query executed' ); // only when WP_DEBUG is on
    }
}

// Output: [INFO][MyPlugin] User logged in {"user_id":5}
```

### Singleton

Singleton pattern with proper per-class instance isolation.

```php
use WeDevs\WpUtils\Singleton;

class MySingletonClass {
    use Singleton;
}

$instance = MySingletonClass::instance();
```

## License

This project is licensed under the GPL 2.0 or Later License.
