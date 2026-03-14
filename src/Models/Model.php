<?php

namespace WeDevs\WpUtils\Models;

use WeDevs\WpUtils\Models\Traits\SoftDeletes;

/**
 * Abstract base model.
 *
 * Provides an OOP abstraction over $wpdb for WordPress entities.
 * Inspired by Laravel Eloquent but purpose-built for WordPress.
 *
 * @property int    $id
 * @property string $created_at
 * @property string $updated_at
 */
abstract class Model {

    /**
     * The table name without prefix.
     *
     * @var string
     */
    protected static $table = '';

    /**
     * The primary key column.
     *
     * @var string
     */
    protected static $primary_key = 'id';

    /**
     * Mass-assignable columns.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

    /**
     * Column to type cast map.
     *
     * Supported types: int, float, bool, datetime, json.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Columns hidden from toArray() output.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Current attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [];

    /**
     * Original attribute values as loaded from DB.
     *
     * @var array<string, mixed>
     */
    protected $original = [];

    /**
     * Whether this instance exists in the database.
     *
     * @var bool
     */
    protected $exists = false;

    /**
     * Cache of booted model classes.
     *
     * @var array<class-string, bool>
     */
    protected static $booted = [];

    /**
     * Registered trait boot methods.
     *
     * @var array<class-string, array<int, string>>
     */
    protected static $trait_initializers = [];

    /**
     * Model constructor.
     *
     * @param array<string, mixed> $attributes Initial attributes.
     */
    public function __construct( array $attributes = [] ) {
        $this->bootIfNotBooted();
        $this->fill( $attributes );
    }

    /**
     * Boot the model if it hasn't been booted yet.
     *
     * @return void
     */
    protected function bootIfNotBooted() {
        $class = static::class;

        if ( ! isset( static::$booted[ $class ] ) ) {
            static::$booted[ $class ] = true;
            static::boot();
        }
    }

    /**
     * Boot the model.
     *
     * Discovers and calls bootTrait() methods on all used traits.
     *
     * @return void
     */
    protected static function boot() {
        $class = static::class;

        static::$trait_initializers[ $class ] = [];

        foreach ( wputils_class_uses_recursive( $class ) as $trait ) {
            $short = ( new \ReflectionClass( $trait ) )->getShortName();
            $method = 'boot' . $short;

            if ( method_exists( static::class, $method ) ) {
                static::$method();
            }

            $init_method = 'initialize' . $short;

            if ( method_exists( static::class, $init_method ) ) {
                static::$trait_initializers[ $class ][] = $init_method;
            }
        }
    }

    /**
     * Initialize trait methods on a new instance.
     *
     * @return void
     */
    protected function initializeTraits() {
        $class = static::class;

        if ( isset( static::$trait_initializers[ $class ] ) ) {
            foreach ( static::$trait_initializers[ $class ] as $method ) {
                $this->{$method}();
            }
        }
    }

    /**
     * Get the hook prefix for WordPress actions and filters.
     *
     * Override this in your subclass to namespace hooks.
     * For example, returning 'flycrm' will produce hooks like 'flycrm_contact_saving'.
     *
     * @return string
     */
    protected static function getHookPrefix() {
        return '';
    }

    /**
     * Build a prefixed hook name.
     *
     * @param string $hook The hook suffix (e.g., 'contact_saving').
     *
     * @return string
     */
    public static function hookName( $hook ) {
        $prefix = static::getHookPrefix();

        if ( $prefix ) {
            return $prefix . '_' . $hook;
        }

        return $hook;
    }

    /**
     * Get the entity name for hooks (e.g., 'contact', 'company').
     *
     * @return string
     */
    public static function getEntityName() {
        $class = ( new \ReflectionClass( static::class ) )->getShortName();

        // Convert CamelCase to snake_case.
        return strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $class ) );
    }

    /**
     * Get the full prefixed table name.
     *
     * @return string
     */
    public static function getTable() {
        global $wpdb;

        return $wpdb->prefix . static::$table;
    }

    /**
     * Get the primary key column name.
     *
     * @return string
     */
    public static function getPrimaryKey() {
        return static::$primary_key;
    }

    // -------------------------------------------------------------------------
    // Static Factories
    // -------------------------------------------------------------------------

    /**
     * Start a new query builder for this model.
     *
     * @return QueryBuilder
     */
    public static function query() {
        $builder = new QueryBuilder( new static() );

        // Auto-exclude soft-deleted rows if the model uses SoftDeletes.
        if ( in_array( SoftDeletes::class, wputils_class_uses_recursive( static::class ), true ) ) {
            $builder->whereNull( 'deleted_at' );
        }

        return $builder;
    }

    /**
     * Find a model by its primary key.
     *
     * @param int $id Primary key value.
     *
     * @return static|null
     */
    public static function find( $id ) {
        return static::query()->find( $id );
    }

    /**
     * Find a model by its primary key or throw.
     *
     * @param int $id Primary key value.
     *
     * @return static
     *
     * @throws \RuntimeException If not found.
     */
    public static function findOrFail( $id ) {
        $model = static::find( $id );

        if ( ! $model ) {
            throw new \RuntimeException(
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                sprintf( '%s with ID %d not found.', esc_html( static::getEntityName() ), absint( $id ) ),
            );
        }

        return $model;
    }

    /**
     * Find a model by a specific column value.
     *
     * @param string $column Column name.
     * @param mixed  $value  Value to match.
     *
     * @return static|null
     */
    public static function findBy( $column, $value ) {
        return static::query()->where( $column, $value )->first();
    }

    /**
     * Create a new model and persist it.
     *
     * @param array<string, mixed> $attributes Attributes to set.
     *
     * @return static
     */
    public static function create( array $attributes ) {
        $model = new static( $attributes );
        $model->save();

        return $model;
    }

    /**
     * Get all models.
     *
     * @return Collection
     */
    public static function all() {
        return static::query()->get();
    }

    // -------------------------------------------------------------------------
    // Instance CRUD
    // -------------------------------------------------------------------------

    /**
     * Fill attributes from an array, respecting $fillable.
     *
     * @param array<string, mixed> $attributes Attributes to set.
     *
     * @return static
     */
    public function fill( array $attributes ) {
        $entity = static::getEntityName();
        $fillable = apply_filters( static::hookName( $entity . '_fillable' ), $this->fillable );

        foreach ( $attributes as $key => $value ) {
            if ( in_array( $key, $fillable, true ) ) {
                $this->setAttribute( $key, $value );
            }
        }

        return $this;
    }

    /**
     * Save the model (INSERT or UPDATE).
     *
     * @return bool
     */
    public function save() {
        global $wpdb;

        $entity = static::getEntityName();

        do_action( static::hookName( $entity . '_saving' ), $this );

        if ( $this->exists ) {
            return $this->performUpdate( $wpdb, $entity );
        }

        return $this->performInsert( $wpdb, $entity );
    }

    /**
     * Perform an INSERT operation.
     *
     * @param \wpdb  $wpdb   WordPress database object.
     * @param string $entity Entity name for hooks.
     *
     * @return bool
     */
    protected function performInsert( $wpdb, $entity ) {
        do_action( static::hookName( $entity . '_creating' ), $this );

        $attributes = $this->attributes;

        /**
         * Filter attributes before inserting.
         *
         * @param array  $attributes The attributes to insert.
         * @param static $model      The model instance.
         */
        $attributes = apply_filters( static::hookName( $entity . '_insert_data' ), $attributes, $this );

        $result = $wpdb->insert( static::getTable(), $attributes ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

        if ( false === $result ) {
            return false;
        }

        $this->setAttribute( static::$primary_key, (int) $wpdb->insert_id );
        $this->exists = true;
        $this->original = $this->attributes;

        do_action( static::hookName( $entity . '_created' ), $this );
        do_action( static::hookName( $entity . '_saved' ), $this );

        return true;
    }

    /**
     * Perform an UPDATE operation.
     *
     * @param \wpdb  $wpdb   WordPress database object.
     * @param string $entity Entity name for hooks.
     *
     * @return bool
     */
    protected function performUpdate( $wpdb, $entity ) {
        $dirty = $this->getDirty();

        if ( empty( $dirty ) ) {
            return true;
        }

        do_action( static::hookName( $entity . '_updating' ), $this );

        /**
         * Filter attributes before updating.
         *
         * @param array  $dirty The changed attributes.
         * @param static $model The model instance.
         */
        $dirty = apply_filters( static::hookName( $entity . '_update_data' ), $dirty, $this );

        $result = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            static::getTable(),
            $dirty,
            [ static::$primary_key => $this->getAttribute( static::$primary_key ) ],
        );

        if ( false === $result ) {
            return false;
        }

        $this->original = $this->attributes;

        do_action( static::hookName( $entity . '_updated' ), $this );
        do_action( static::hookName( $entity . '_saved' ), $this );

        return true;
    }

    /**
     * Update the model with the given attributes and save.
     *
     * @param array<string, mixed> $attributes Attributes to update.
     *
     * @return bool
     */
    public function update( array $attributes ) {
        $this->fill( $attributes );

        return $this->save();
    }

    /**
     * Delete the model from the database.
     *
     * If the model uses SoftDeletes, it will soft-delete.
     * Otherwise it performs a hard delete.
     *
     * @return bool
     */
    public function delete() {
        if ( method_exists( $this, 'trash' ) ) {
            return $this->trash();
        }

        return $this->forceDelete();
    }

    /**
     * Hard delete the model from the database.
     *
     * @return bool
     */
    public function forceDelete() {
        global $wpdb;

        $entity = static::getEntityName();

        do_action( static::hookName( $entity . '_deleting' ), $this );

        $result = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            static::getTable(),
            [ static::$primary_key => $this->getAttribute( static::$primary_key ) ],
        );

        if ( false === $result ) {
            return false;
        }

        $this->exists = false;

        do_action( static::hookName( $entity . '_deleted' ), $this );

        return true;
    }

    /**
     * Reload the model from the database.
     *
     * @return static|null
     */
    public function fresh() {
        return static::find( $this->getAttribute( static::$primary_key ) );
    }

    // -------------------------------------------------------------------------
    // Attribute Accessors
    // -------------------------------------------------------------------------

    /**
     * Get an attribute value.
     *
     * @param string $key Attribute name.
     *
     * @return mixed
     */
    public function getAttribute( $key ) {
        // Check for a custom accessor: get{StudlyKey}Attribute().
        $accessor = 'get' . str_replace( '_', '', ucwords( $key, '_' ) ) . 'Attribute';

        if ( method_exists( $this, $accessor ) ) {
            $value = $this->attributes[ $key ] ?? null;

            return $this->{$accessor}( $value );
        }

        if ( ! array_key_exists( $key, $this->attributes ) ) {
            return null;
        }

        return $this->castAttribute( $key, $this->attributes[ $key ] );
    }

    /**
     * Set an attribute value.
     *
     * @param string $key   Attribute name.
     * @param mixed  $value Attribute value.
     *
     * @return static
     */
    public function setAttribute( $key, $value ) {
        // Check for a custom mutator: set{StudlyKey}Attribute().
        $mutator = 'set' . str_replace( '_', '', ucwords( $key, '_' ) ) . 'Attribute';

        if ( method_exists( $this, $mutator ) ) {
            $this->{$mutator}( $value );

            return $this;
        }

        $this->attributes[ $key ] = $value;

        return $this;
    }

    /**
     * Magic getter.
     *
     * @param string $key Property name.
     *
     * @return mixed
     */
    public function __get( $key ) {
        return $this->getAttribute( $key );
    }

    /**
     * Magic setter.
     *
     * @param string $key   Property name.
     * @param mixed  $value Property value.
     *
     * @return void
     */
    public function __set( $key, $value ) {
        $this->setAttribute( $key, $value );
    }

    /**
     * Magic isset.
     *
     * @param string $key Property name.
     *
     * @return bool
     */
    public function __isset( $key ) {
        return null !== $this->getAttribute( $key );
    }

    /**
     * Get the original value of an attribute.
     *
     * @param string|null $key Attribute name, or null for all.
     *
     * @return mixed
     */
    public function getOriginal( $key = null ) {
        if ( null === $key ) {
            return $this->original;
        }

        return $this->original[ $key ] ?? null;
    }

    /**
     * Get dirty (changed) attributes.
     *
     * @return array<string, mixed>
     */
    public function getDirty() {
        $dirty = [];

        foreach ( $this->attributes as $key => $value ) {
            if ( ! array_key_exists( $key, $this->original ) || $value !== $this->original[ $key ] ) {
                $dirty[ $key ] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Check if an attribute or the model is dirty.
     *
     * @param string|null $key Attribute name, or null to check any.
     *
     * @return bool
     */
    public function isDirty( $key = null ) {
        if ( null === $key ) {
            return ! empty( $this->getDirty() );
        }

        return array_key_exists( $key, $this->getDirty() );
    }

    /**
     * Check if the model exists in the database.
     *
     * @return bool
     */
    public function exists() {
        return $this->exists;
    }

    // -------------------------------------------------------------------------
    // Casting
    // -------------------------------------------------------------------------

    /**
     * Cast an attribute to its defined type.
     *
     * @param string $key   Attribute name.
     * @param mixed  $value Raw value.
     *
     * @return mixed
     */
    protected function castAttribute( $key, $value ) {
        if ( null === $value || ! isset( $this->casts[ $key ] ) ) {
            return $value;
        }

        switch ( $this->casts[ $key ] ) {
            case 'int':
            case 'integer':
                return (int) $value;

            case 'float':
            case 'double':
            case 'decimal':
                return (float) $value;

            case 'bool':
            case 'boolean':
                return (bool) $value;

            case 'json':
                return json_decode( $value, true );

            case 'datetime':
                return $value;

            default:
                return $value;
        }
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    /**
     * Convert the model to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray() {
        $attributes = [];

        foreach ( $this->attributes as $key => $value ) {
            if ( in_array( $key, $this->hidden, true ) ) {
                continue;
            }

            $attributes[ $key ] = $this->getAttribute( $key );
        }

        $entity = static::getEntityName();

        return apply_filters( static::hookName( $entity . '_attributes' ), $attributes, $this );
    }

    /**
     * Get only the specified attributes.
     *
     * @param array<int, string> $keys Attribute names.
     *
     * @return array<string, mixed>
     */
    public function only( array $keys ) {
        return array_intersect_key( $this->toArray(), array_flip( $keys ) );
    }

    /**
     * Get all attributes except the specified ones.
     *
     * @param array<int, string> $keys Attribute names to exclude.
     *
     * @return array<string, mixed>
     */
    public function except( array $keys ) {
        return array_diff_key( $this->toArray(), array_flip( $keys ) );
    }

    // -------------------------------------------------------------------------
    // Hydration
    // -------------------------------------------------------------------------

    /**
     * Create a new model instance from a database row.
     *
     * @param object|array<string, mixed> $row    Database row.
     * @param bool                        $exists Whether the row exists in DB.
     *
     * @return static
     */
    public static function newFromRow( $row, $exists = true ) {
        $attrs = (array) $row;

        $instance = new static();
        $instance->attributes = $attrs;
        $instance->original = $attrs;
        $instance->exists = $exists;

        $instance->initializeTraits();

        return $instance;
    }

    /**
     * Create a collection of models from database rows.
     *
     * @param array<int, object|array<string, mixed>> $rows Database rows.
     *
     * @return Collection
     */
    public static function hydrate( array $rows ) {
        $models = array_map(
            function ( $row ) {
                return static::newFromRow( $row );
            },
            $rows,
        );

        return new Collection( $models );
    }
}
