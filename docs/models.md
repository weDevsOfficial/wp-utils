# Models & ORM

An Eloquent-inspired ORM built for WordPress. Provides an object-oriented abstraction over `$wpdb` with models, a fluent query builder, collections, and reusable traits.

## Table of Contents

- [Defining Models](#defining-models)
- [Hook Prefix](#hook-prefix)
- [CRUD Operations](#crud-operations)
- [Query Builder](#query-builder)
- [Attributes](#attributes)
- [Collections](#collections)
- [Traits](#traits)
- [Lifecycle Hooks](#lifecycle-hooks)

---

## Defining Models

Extend the base `Model` class and define your table, fillable fields, casts, and hidden attributes:

```php
use WeDevs\WpUtils\Models\Model;

class Contact extends Model {

    protected static $table = 'crm_contacts';

    protected static $primary_key = 'id'; // default

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    protected $casts = [
        'id'       => 'int',
        'is_active' => 'bool',
        'metadata'  => 'json',
        'amount'    => 'float',
    ];

    protected $hidden = [
        'password',
    ];
}
```

The table name is automatically prefixed with `$wpdb->prefix`, so `crm_contacts` becomes `wp_crm_contacts`.

### Supported Cast Types

| Type | Aliases |
|------|---------|
| `int` | `integer` |
| `float` | `double`, `decimal` |
| `bool` | `boolean` |
| `json` | — |
| `datetime` | — |

---

## Hook Prefix

Override `getHookPrefix()` to namespace all WordPress hooks fired by your models. This prevents hook collisions when multiple plugins use wp-utils:

```php
use WeDevs\WpUtils\Models\Model;

abstract class BaseModel extends Model {

    protected static function getHookPrefix() {
        return 'myplugin';
    }
}

class Contact extends BaseModel {
    protected static $table = 'myplugin_contacts';
    // ...
}
```

With prefix `myplugin`, hooks become `myplugin_contact_saving`, `myplugin_contact_created`, etc. Without a prefix, hooks are unprefixed: `contact_saving`, `contact_created`.

---

## CRUD Operations

### Creating

```php
// Via static create (fills + saves)
$contact = Contact::create([
    'first_name' => 'John',
    'last_name'  => 'Doe',
    'email'      => 'john@example.com',
]);

// Via instance
$contact = new Contact([
    'first_name' => 'Jane',
    'email'      => 'jane@example.com',
]);
$contact->save(); // returns bool
```

### Reading

```php
// Find by primary key
$contact = Contact::find( 1 );
$contact = Contact::findOrFail( 1 ); // throws RuntimeException

// Find by column
$contact = Contact::findBy( 'email', 'john@example.com' );

// Get all
$contacts = Contact::all(); // returns Collection
```

### Updating

```php
// Update specific fields
$contact->update([
    'first_name' => 'Johnny',
    'phone'      => '555-1234',
]);

// Or set attributes and save
$contact->email = 'new@example.com';
$contact->save();
```

### Deleting

```php
// Hard delete
$contact->delete();
$contact->forceDelete();

// With SoftDeletes trait, delete() calls trash() instead
$contact->delete();    // soft-deletes
$contact->trash();     // same as above
$contact->restore();   // restores
$contact->forceDelete(); // permanent delete
```

### Refreshing

```php
$fresh = $contact->fresh(); // re-fetches from DB
```

---

## Query Builder

Start queries with `Model::query()` and chain methods fluently:

```php
$contacts = Contact::query()
    ->where( 'status', 'active' )
    ->where( 'age', '>=', 18 )
    ->orderBy( 'created_at', 'DESC' )
    ->limit( 10 )
    ->get();
```

### WHERE Clauses

```php
// Basic
->where( 'name', 'John' )          // name = 'John'
->where( 'age', '>', 25 )          // age > 25
->where( 'status', '!=', 'spam' )  // status != 'spam'

// OR
->orWhere( 'role', 'admin' )

// IN / NOT IN
->whereIn( 'status', [ 'active', 'pending' ] )
->whereNotIn( 'id', [ 1, 2, 3 ] )

// NULL checks
->whereNull( 'deleted_at' )
->whereNotNull( 'email' )

// BETWEEN
->whereBetween( 'age', 18, 65 )

// Raw (use with caution — bindings are prepared)
->whereRaw( 'YEAR(created_at) = %d', [ 2026 ] )
```

### Ordering, Limiting, Offsetting

```php
->orderBy( 'name', 'ASC' )
->orderBy( 'created_at', 'DESC' ) // multiple order clauses
->limit( 20 )
->offset( 40 )
```

### Retrieving Results

```php
$collection = Contact::query()->where( 'active', 1 )->get();     // Collection
$contact    = Contact::query()->where( 'email', $email )->first(); // Model|null
$contact    = Contact::query()->find( 42 );                        // Model|null
```

### Aggregates

```php
$count = Contact::query()->where( 'active', 1 )->count();
$exists = Contact::query()->where( 'email', $email )->exists();
$total = Contact::query()->sum( 'amount' );
$avg   = Contact::query()->avg( 'amount' );
$max   = Contact::query()->max( 'created_at' );
$min   = Contact::query()->min( 'amount' );
```

### Pluck

```php
$emails = Contact::query()->where( 'active', 1 )->pluck( 'email' );
// ['john@example.com', 'jane@example.com', ...]
```

### Pagination

```php
$result = Contact::query()
    ->where( 'active', 1 )
    ->orderBy( 'name' )
    ->paginate( 20, 2 ); // 20 per page, page 2

// $result = [
//     'data'         => Collection,
//     'total'        => 150,
//     'per_page'     => 20,
//     'current_page' => 2,
//     'last_page'    => 8,
// ]
```

### Bulk Operations

```php
// Bulk update
Contact::query()
    ->where( 'status', 'pending' )
    ->update( [ 'status' => 'active' ] );

// Bulk delete
Contact::query()
    ->where( 'created_at', '<', '2020-01-01' )
    ->delete();
```

---

## Attributes

### Accessors & Mutators

Define custom getters and setters using `get{StudlyKey}Attribute` / `set{StudlyKey}Attribute`:

```php
class Contact extends Model {

    // Accessor: $contact->full_name
    public function getFullNameAttribute( $value ) {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Mutator: $contact->email = 'JOHN@EXAMPLE.COM' -> stored lowercase
    public function setEmailAttribute( $value ) {
        $this->attributes['email'] = strtolower( $value );
    }
}
```

### Dirty Tracking

```php
$contact->isDirty();            // any attribute changed?
$contact->isDirty( 'email' );   // specific attribute changed?
$contact->getDirty();           // ['email' => 'new@example.com']
$contact->getOriginal( 'email' ); // value before changes
```

### Serialization

```php
$array = $contact->toArray();              // excludes $hidden fields
$subset = $contact->only( [ 'id', 'email' ] );
$rest = $contact->except( [ 'password' ] );
```

### Hydration

```php
// Create a model from a database row (marks as existing)
$contact = Contact::newFromRow( $row );

// Create a collection from multiple rows
$contacts = Contact::hydrate( $rows );
```

---

## Collections

Query results are returned as `Collection` instances, which implement `Countable`, `IteratorAggregate`, `ArrayAccess`, and `JsonSerializable`:

```php
$contacts = Contact::query()->where( 'active', 1 )->get();

// Iteration
foreach ( $contacts as $contact ) {
    echo $contact->name;
}

// Count
count( $contacts );       // or $contacts->count()
$contacts->isEmpty();
$contacts->isNotEmpty();

// Access
$first = $contacts->first();
$last  = $contacts->last();
$third = $contacts[2];    // ArrayAccess

// Extract
$names = $contacts->pluck( 'name' );    // ['John', 'Jane', ...]
$ids   = $contacts->ids();              // [1, 2, 3, ...]
$keyed = $contacts->keyBy( 'email' );   // ['john@...' => Contact, ...]

// Transform
$mapped   = $contacts->map( fn( $c ) => $c->toArray() );
$filtered = $contacts->filter( fn( $c ) => $c->age > 18 );
$contacts->each( fn( $c ) => $c->sendWelcomeEmail() );

// Serialize
$array = $contacts->toArray();
$json  = json_encode( $contacts ); // uses jsonSerialize()
```

---

## Traits

### SoftDeletes

Adds soft-delete functionality via a `deleted_at` column. Queries automatically exclude soft-deleted rows.

```php
use WeDevs\WpUtils\Models\Traits\SoftDeletes;

class Contact extends Model {
    use SoftDeletes;

    // Your table needs a nullable `deleted_at` datetime column
}
```

**Usage:**

```php
$contact->trash();     // sets deleted_at, fires trashing/trashed hooks
$contact->trashed();   // true
$contact->restore();   // clears deleted_at, fires restoring/restored hooks
$contact->forceDelete(); // permanent delete, bypasses soft-delete

// Queries
Contact::query()->get();          // excludes trashed (automatic)
Contact::withTrashed()->get();    // includes trashed
Contact::onlyTrashed()->get();    // only trashed
```

### HasTimestamps

Auto-manages `created_at` and `updated_at` columns:

```php
use WeDevs\WpUtils\Models\Traits\HasTimestamps;

class Contact extends Model {
    use HasTimestamps;

    // Your table needs `created_at` and `updated_at` datetime columns
}
```

- On **create**: sets both `created_at` and `updated_at` to `current_time('mysql')`
- On **update**: sets `updated_at` to `current_time('mysql')`

### HasHash

Auto-generates a UUID v4 hash on creation for URL-safe identifiers:

```php
use WeDevs\WpUtils\Models\Traits\HasHash;

class Contact extends Model {
    use HasHash;

    // Your table needs a `hash` varchar column (e.g., VARCHAR(36))
}
```

**Usage:**

```php
$contact = Contact::create( [ 'name' => 'John' ] );
echo $contact->hash; // 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'

// Lookup by hash
$contact = Contact::findByHash( 'abc-123' );
$contact = Contact::findByHashOrFail( 'abc-123' ); // throws RuntimeException

// Route key for REST APIs
$key = $contact->getRouteKey(); // returns the hash
```

---

## Lifecycle Hooks

Models fire WordPress actions at key lifecycle points. If you set a hook prefix (e.g., `myplugin`), hooks are prefixed accordingly:

| Event | Hook Name | Fired When |
|-------|-----------|------------|
| Saving | `{prefix}_{entity}_saving` | Before any save (insert or update) |
| Creating | `{prefix}_{entity}_creating` | Before insert |
| Created | `{prefix}_{entity}_created` | After insert |
| Updating | `{prefix}_{entity}_updating` | Before update |
| Updated | `{prefix}_{entity}_updated` | After update |
| Saved | `{prefix}_{entity}_saved` | After any save |
| Deleting | `{prefix}_{entity}_deleting` | Before hard delete |
| Deleted | `{prefix}_{entity}_deleted` | After hard delete |
| Trashing | `{prefix}_{entity}_trashing` | Before soft delete |
| Trashed | `{prefix}_{entity}_trashed` | After soft delete |
| Restoring | `{prefix}_{entity}_restoring` | Before restore |
| Restored | `{prefix}_{entity}_restored` | After restore |

The entity name is derived from the class name in `snake_case` (e.g., `Contact` → `contact`, `DocumentItem` → `document_item`).

### Filters

| Filter | Description |
|--------|-------------|
| `{prefix}_{entity}_fillable` | Modify the fillable fields array |
| `{prefix}_{entity}_insert_data` | Modify attributes before INSERT |
| `{prefix}_{entity}_update_data` | Modify dirty attributes before UPDATE |
| `{prefix}_{entity}_attributes` | Modify toArray() output |
| `{prefix}_{entity}_query` | Modify the QueryBuilder before execution |
| `{prefix}_{entity}_query_results` | Modify the Collection after query |

### Example: Hooking into Model Events

```php
// Log every contact creation
add_action( 'myplugin_contact_created', function ( $contact ) {
    error_log( 'Contact created: ' . $contact->email );
} );

// Modify data before saving
add_filter( 'myplugin_contact_insert_data', function ( $attributes, $contact ) {
    $attributes['created_by'] = get_current_user_id();
    return $attributes;
}, 10, 2 );

// Add computed fields to API responses
add_filter( 'myplugin_contact_attributes', function ( $attributes, $contact ) {
    $attributes['full_name'] = $contact->first_name . ' ' . $contact->last_name;
    return $attributes;
}, 10, 2 );
```
