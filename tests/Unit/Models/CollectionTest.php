<?php

namespace WeDevs\WpUtils\Tests\Unit\Models;

use WeDevs\WpUtils\Models\Collection;
use WeDevs\WpUtils\Tests\TestCase;

class CollectionTest extends TestCase {

    public function test_constructor_reindexes_items(): void {
        $items = [ 2 => 'a', 5 => 'b', 9 => 'c' ];
        $collection = new Collection( $items );

        $this->assertSame( [ 'a', 'b', 'c' ], $collection->all() );
    }

    public function test_all_returns_items_array(): void {
        $collection = new Collection( [ 'x', 'y' ] );

        $this->assertSame( [ 'x', 'y' ], $collection->all() );
    }

    public function test_first_returns_first_item(): void {
        $collection = new Collection( [ 'a', 'b', 'c' ] );

        $this->assertSame( 'a', $collection->first() );
    }

    public function test_first_returns_null_when_empty(): void {
        $collection = new Collection();

        $this->assertNull( $collection->first() );
    }

    public function test_last_returns_last_item(): void {
        $collection = new Collection( [ 'a', 'b', 'c' ] );

        $this->assertSame( 'c', $collection->last() );
    }

    public function test_last_returns_null_when_empty(): void {
        $collection = new Collection();

        $this->assertNull( $collection->last() );
    }

    public function test_count_returns_item_count(): void {
        $collection = new Collection( [ 'a', 'b', 'c' ] );

        $this->assertCount( 3, $collection );
        $this->assertSame( 3, $collection->count() );
    }

    public function test_count_returns_zero_when_empty(): void {
        $collection = new Collection();

        $this->assertSame( 0, $collection->count() );
    }

    public function test_is_empty_returns_true_when_empty(): void {
        $collection = new Collection();

        $this->assertTrue( $collection->isEmpty() );
    }

    public function test_is_empty_returns_false_when_not_empty(): void {
        $collection = new Collection( [ 'a' ] );

        $this->assertFalse( $collection->isEmpty() );
    }

    public function test_is_not_empty_returns_true_when_not_empty(): void {
        $collection = new Collection( [ 'a' ] );

        $this->assertTrue( $collection->isNotEmpty() );
    }

    public function test_is_not_empty_returns_false_when_empty(): void {
        $collection = new Collection();

        $this->assertFalse( $collection->isNotEmpty() );
    }

    public function test_pluck_extracts_attribute_values(): void {
        $items = [
            (object) [ 'name' => 'Alice', 'age' => 30 ],
            (object) [ 'name' => 'Bob', 'age' => 25 ],
        ];

        $collection = new Collection( $items );

        $this->assertSame( [ 'Alice', 'Bob' ], $collection->pluck( 'name' ) );
    }

    public function test_key_by_keys_collection_by_attribute(): void {
        $a = (object) [ 'id' => 10, 'name' => 'Alice' ];
        $b = (object) [ 'id' => 20, 'name' => 'Bob' ];

        $collection = new Collection( [ $a, $b ] );
        $keyed = $collection->keyBy( 'id' );

        $this->assertArrayHasKey( 10, $keyed );
        $this->assertArrayHasKey( 20, $keyed );
        $this->assertSame( $a, $keyed[10] );
        $this->assertSame( $b, $keyed[20] );
    }

    public function test_map_transforms_each_item(): void {
        $collection = new Collection( [ 1, 2, 3 ] );
        $mapped = $collection->map(
            function ( $item ) {
                return $item * 2;
            },
        );

        $this->assertInstanceOf( Collection::class, $mapped );
        $this->assertSame( [ 2, 4, 6 ], $mapped->all() );
    }

    public function test_filter_removes_non_matching_items(): void {
        $collection = new Collection( [ 1, 2, 3, 4, 5 ] );
        $filtered = $collection->filter(
            function ( $item ) {
                return $item > 3;
            },
        );

        $this->assertInstanceOf( Collection::class, $filtered );
        $this->assertSame( [ 4, 5 ], $filtered->all() );
    }

    public function test_filter_reindexes_results(): void {
        $collection = new Collection( [ 'a', 'b', 'c' ] );
        $filtered = $collection->filter(
            function ( $item ) {
                return 'b' !== $item;
            },
        );

        $this->assertSame( [ 'a', 'c' ], $filtered->all() );
    }

    public function test_each_executes_callback_on_all_items(): void {
        $collection = new Collection( [ 1, 2, 3 ] );
        $results = [];

        $returned = $collection->each(
            function ( $item, $index ) use ( &$results ) {
                $results[] = "{$index}:{$item}";
            },
        );

        $this->assertSame( [ '0:1', '1:2', '2:3' ], $results );
        $this->assertSame( $collection, $returned );
    }

    public function test_ids_returns_id_values(): void {
        $items = [
            (object) [ 'id' => 1 ],
            (object) [ 'id' => 2 ],
            (object) [ 'id' => 3 ],
        ];

        $collection = new Collection( $items );

        $this->assertSame( [ 1, 2, 3 ], $collection->ids() );
    }

    public function test_push_appends_item(): void {
        $collection = new Collection( [ 'a' ] );
        $returned = $collection->push( 'b' );

        $this->assertSame( [ 'a', 'b' ], $collection->all() );
        $this->assertSame( $collection, $returned );
    }

    public function test_to_array_converts_stdclass_items(): void {
        $items = [
            (object) [ 'name' => 'Alice' ],
            (object) [ 'name' => 'Bob' ],
        ];

        $collection = new Collection( $items );
        $array = $collection->toArray();

        $this->assertSame( [ 'name' => 'Alice' ], $array[0] );
        $this->assertSame( [ 'name' => 'Bob' ], $array[1] );
    }

    public function test_json_serialize_returns_array(): void {
        $items = [
            (object) [ 'id' => 1 ],
        ];

        $collection = new Collection( $items );

        $this->assertSame( $collection->toArray(), $collection->jsonSerialize() );
    }

    public function test_array_access_offset_exists(): void {
        $collection = new Collection( [ 'a', 'b' ] );

        $this->assertTrue( isset( $collection[0] ) );
        $this->assertTrue( isset( $collection[1] ) );
        $this->assertFalse( isset( $collection[2] ) );
    }

    public function test_array_access_offset_get(): void {
        $collection = new Collection( [ 'a', 'b' ] );

        $this->assertSame( 'a', $collection[0] );
        $this->assertSame( 'b', $collection[1] );
        $this->assertNull( $collection[99] );
    }

    public function test_array_access_offset_set(): void {
        $collection = new Collection( [ 'a', 'b' ] );
        $collection[1] = 'z';

        $this->assertSame( 'z', $collection[1] );
    }

    public function test_array_access_offset_set_with_null_offset(): void {
        $collection = new Collection( [ 'a' ] );
        $collection[] = 'b';

        $this->assertSame( [ 'a', 'b' ], $collection->all() );
    }

    public function test_array_access_offset_unset_reindexes(): void {
        $collection = new Collection( [ 'a', 'b', 'c' ] );

        unset( $collection[1] );

        $this->assertSame( [ 'a', 'c' ], $collection->all() );
        $this->assertSame( 2, $collection->count() );
    }

    public function test_iterator_allows_foreach(): void {
        $collection = new Collection( [ 'a', 'b', 'c' ] );
        $results = [];

        foreach ( $collection as $index => $item ) {
            $results[ $index ] = $item;
        }

        $this->assertSame( [ 0 => 'a', 1 => 'b', 2 => 'c' ], $results );
    }

    public function test_empty_collection_is_countable(): void {
        $collection = new Collection();

        $this->assertCount( 0, $collection );
    }

    public function test_map_returns_new_collection_instance(): void {
        $collection = new Collection( [ 1, 2, 3 ] );
        $mapped = $collection->map( fn ( $i ) => $i );

        $this->assertNotSame( $collection, $mapped );
    }

    public function test_filter_returns_new_collection_instance(): void {
        $collection = new Collection( [ 1, 2, 3 ] );
        $filtered = $collection->filter( fn ( $i ) => true );

        $this->assertNotSame( $collection, $filtered );
    }
}
