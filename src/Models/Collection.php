<?php

namespace WeDevs\WpUtils\Models;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * A typed collection of model instances.
 *
 * Provides array-like access with convenient helper methods.
 *
 * @implements IteratorAggregate<int, Model>
 * @implements ArrayAccess<int, Model>
 */
class Collection implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable {

    /**
     * The items in the collection.
     *
     * @var array<int, Model|mixed>
     */
    protected $items = [];

    /**
     * Collection constructor.
     *
     * @param array<int, Model|mixed> $items Initial items.
     */
    public function __construct( array $items = [] ) {
        $this->items = array_values( $items );
    }

    /**
     * Get all items as a plain array.
     *
     * @return array<int, Model|mixed>
     */
    public function all() {
        return $this->items;
    }

    /**
     * Get the first item.
     *
     * @return Model|null
     */
    public function first() {
        return $this->items[0] ?? null;
    }

    /**
     * Get the last item.
     *
     * @return Model|null
     */
    public function last() {
        if ( empty( $this->items ) ) {
            return null;
        }

        return end( $this->items );
    }

    /**
     * Get the count of items.
     *
     * @return int
     */
    public function count(): int {
        return count( $this->items );
    }

    /**
     * Check if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty() {
        return empty( $this->items );
    }

    /**
     * Check if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty() {
        return ! $this->isEmpty();
    }

    /**
     * Pluck a single attribute from all items.
     *
     * @param string $key Attribute name.
     *
     * @return array<int, mixed>
     */
    public function pluck( $key ) {
        return array_map(
            function ( $item ) use ( $key ) {
                return $item->{$key};
            },
            $this->items,
        );
    }

    /**
     * Key the collection by an attribute.
     *
     * @param string $key Attribute name.
     *
     * @return array<mixed, Model|mixed> Associative array keyed by the attribute.
     */
    public function keyBy( $key ) {
        $result = [];

        foreach ( $this->items as $item ) {
            $result[ $item->{$key} ] = $item;
        }

        return $result;
    }

    /**
     * Map over each item.
     *
     * @param callable $callback Callback receiving ($item, $index).
     *
     * @return static
     */
    public function map( callable $callback ) {
        return new static( array_map( $callback, $this->items ) );
    }

    /**
     * Filter items with a callback.
     *
     * @param callable $callback Callback returning bool.
     *
     * @return static
     */
    public function filter( callable $callback ) {
        return new static( array_values( array_filter( $this->items, $callback ) ) );
    }

    /**
     * Execute a callback on each item.
     *
     * @param callable $callback Callback receiving ($item, $index).
     *
     * @return static
     */
    public function each( callable $callback ) {
        foreach ( $this->items as $index => $item ) {
            $callback( $item, $index );
        }

        return $this;
    }

    /**
     * Get all primary key values.
     *
     * @return array<int, mixed>
     */
    public function ids() {
        return $this->pluck( 'id' );
    }

    /**
     * Convert all items to arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray() {
        return array_map(
            function ( $item ) {
                return $item instanceof Model ? $item->toArray() : (array) $item;
            },
            $this->items,
        );
    }

    /**
     * Push an item onto the end.
     *
     * @param mixed $item The item to add.
     *
     * @return static
     */
    public function push( $item ) {
        $this->items[] = $item;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Interface Implementations
    // -------------------------------------------------------------------------

    /**
     * Get an iterator.
     *
     * @return ArrayIterator<int, Model|mixed>
     */
    public function getIterator(): ArrayIterator {
        return new ArrayIterator( $this->items );
    }

    /**
     * Check if an offset exists.
     *
     * @param mixed $offset Offset.
     *
     * @return bool
     */
    public function offsetExists( $offset ): bool {
        return isset( $this->items[ $offset ] );
    }

    /**
     * Get an item at offset.
     *
     * @param mixed $offset Offset.
     *
     * @return mixed
     */
    public function offsetGet( $offset ): mixed {
        return $this->items[ $offset ] ?? null;
    }

    /**
     * Set an item at offset.
     *
     * @param mixed $offset Offset.
     * @param mixed $value  Value.
     *
     * @return void
     */
    public function offsetSet( $offset, $value ): void {
        if ( null === $offset ) {
            $this->items[] = $value;
        } else {
            $this->items[ $offset ] = $value;
        }
    }

    /**
     * Unset an item at offset.
     *
     * @param mixed $offset Offset.
     *
     * @return void
     */
    public function offsetUnset( $offset ): void {
        unset( $this->items[ $offset ] );
        $this->items = array_values( $this->items );
    }

    /**
     * Serialize to JSON.
     *
     * @return array<int, array<string, mixed>>
     */
    public function jsonSerialize(): mixed {
        return $this->toArray();
    }
}
