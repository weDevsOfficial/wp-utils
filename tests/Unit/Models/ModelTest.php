<?php

namespace WeDevs\WpUtils\Tests\Unit\Models;

use Brain\Monkey;
use WeDevs\WpUtils\Models\Collection;
use WeDevs\WpUtils\Tests\TestCase;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\PrefixedModel;
use WeDevs\WpUtils\Tests\Unit\Models\Stubs\StubModel;

class ModelTest extends TestCase {

    // -------------------------------------------------------------------------
    // Attribute Access / Magic Methods
    // -------------------------------------------------------------------------

    public function test_get_attribute_returns_null_for_missing_key(): void {
        $model = new StubModel();

        $this->assertNull( $model->getAttribute( 'nonexistent' ) );
    }

    public function test_set_attribute_stores_value(): void {
        $model = new StubModel();
        $model->setAttribute( 'name', 'Alice' );

        $this->assertSame( 'Alice', $model->getAttribute( 'name' ) );
    }

    public function test_magic_get_delegates_to_get_attribute(): void {
        $model = new StubModel();
        $model->setAttribute( 'name', 'Bob' );

        $this->assertSame( 'Bob', $model->name );
    }

    public function test_magic_set_delegates_to_set_attribute(): void {
        $model = new StubModel();
        $model->name = 'Charlie';

        $this->assertSame( 'Charlie', $model->getAttribute( 'name' ) );
    }

    public function test_magic_isset_returns_true_for_set_attributes(): void {
        $model = new StubModel();
        $model->name = 'Dave';

        $this->assertTrue( isset( $model->name ) );
    }

    public function test_magic_isset_returns_false_for_unset_attributes(): void {
        $model = new StubModel();

        $this->assertFalse( isset( $model->name ) );
    }

    // -------------------------------------------------------------------------
    // Custom Accessors / Mutators
    // -------------------------------------------------------------------------

    public function test_get_attribute_calls_custom_accessor(): void {
        $model = new StubModel();
        $model->setAttribute( 'display_name', 'anything' );
        $model->setAttribute( 'name', 'alice' );

        $this->assertSame( 'ALICE', $model->getAttribute( 'display_name' ) );
    }

    public function test_set_attribute_calls_custom_mutator(): void {
        $model = new StubModel();
        $model->setAttribute( 'email', 'Alice@Example.COM' );

        $this->assertSame( 'alice@example.com', $model->getAttribute( 'email' ) );
    }

    // -------------------------------------------------------------------------
    // Casting
    // -------------------------------------------------------------------------

    public function test_cast_int_casts_string_to_integer(): void {
        $model = StubModel::newFromRow( [ 'id' => '42' ] );

        $this->assertSame( 42, $model->id );
    }

    public function test_cast_float_casts_string_to_float(): void {
        $model = StubModel::newFromRow( [ 'score' => '9.5' ] );

        $this->assertSame( 9.5, $model->score );
    }

    public function test_cast_bool_casts_to_boolean(): void {
        $model = StubModel::newFromRow( [ 'active' => '1' ] );

        $this->assertTrue( $model->active );

        $model2 = StubModel::newFromRow( [ 'active' => '0' ] );

        $this->assertFalse( $model2->active );
    }

    public function test_cast_json_decodes_json_string(): void {
        $model = StubModel::newFromRow( [ 'meta' => '{"key":"value"}' ] );

        $this->assertSame( [ 'key' => 'value' ], $model->meta );
    }

    public function test_cast_returns_value_unchanged_when_null(): void {
        $model = StubModel::newFromRow( [ 'id' => null ] );

        $this->assertNull( $model->id );
    }

    public function test_cast_returns_value_unchanged_when_no_cast_defined(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );

        $this->assertSame( 'Alice', $model->name );
    }

    // -------------------------------------------------------------------------
    // Dirty Tracking
    // -------------------------------------------------------------------------

    public function test_get_dirty_returns_changed_attributes(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'email' => 'alice@test.com' ] );

        $model->setAttribute( 'name', 'Bob' );

        $dirty = $model->getDirty();

        $this->assertArrayHasKey( 'name', $dirty );
        $this->assertSame( 'Bob', $dirty['name'] );
        $this->assertArrayNotHasKey( 'email', $dirty );
    }

    public function test_get_dirty_returns_empty_when_no_changes(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );

        $this->assertEmpty( $model->getDirty() );
    }

    public function test_is_dirty_returns_true_when_any_change_exists(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );
        $model->setAttribute( 'name', 'Bob' );

        $this->assertTrue( $model->isDirty() );
    }

    public function test_is_dirty_returns_false_when_clean(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );

        $this->assertFalse( $model->isDirty() );
    }

    public function test_is_dirty_with_key_checks_specific_attribute(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'email' => 'a@b.com' ] );
        $model->setAttribute( 'name', 'Bob' );

        $this->assertTrue( $model->isDirty( 'name' ) );
        $this->assertFalse( $model->isDirty( 'email' ) );
    }

    public function test_get_original_returns_all_originals(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'email' => 'a@b.com' ] );
        $model->setAttribute( 'name', 'Bob' );

        $original = $model->getOriginal();

        $this->assertSame( 'Alice', $original['name'] );
        $this->assertSame( 'a@b.com', $original['email'] );
    }

    public function test_get_original_with_key_returns_specific_original(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );
        $model->setAttribute( 'name', 'Bob' );

        $this->assertSame( 'Alice', $model->getOriginal( 'name' ) );
    }

    public function test_get_original_returns_null_for_missing_key(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );

        $this->assertNull( $model->getOriginal( 'nonexistent' ) );
    }

    // -------------------------------------------------------------------------
    // Fill / Fillable
    // -------------------------------------------------------------------------

    public function test_fill_only_sets_fillable_attributes(): void {
        $model = new StubModel();
        $model->fill( [ 'name' => 'Alice', 'email' => 'a@b.com', 'score' => '9.5' ] );

        $this->assertSame( 'Alice', $model->getAttribute( 'name' ) );
        $this->assertSame( 'a@b.com', $model->getAttribute( 'email' ) );
    }

    public function test_fill_ignores_non_fillable_attributes(): void {
        $model = new StubModel();
        $model->fill( [ 'id' => 999, 'secret' => 'should not set' ] );

        $this->assertNull( $model->getAttribute( 'id' ) );
        $this->assertNull( $model->getAttribute( 'secret' ) );
    }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------

    public function test_to_array_returns_all_visible_attributes(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'email' => 'a@b.com' ] );
        $array = $model->toArray();

        $this->assertArrayHasKey( 'name', $array );
        $this->assertArrayHasKey( 'email', $array );
    }

    public function test_to_array_excludes_hidden_attributes(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'secret' => 'hidden_val' ] );
        $array = $model->toArray();

        $this->assertArrayNotHasKey( 'secret', $array );
        $this->assertArrayHasKey( 'name', $array );
    }

    public function test_to_array_applies_accessors(): void {
        $model = StubModel::newFromRow( [ 'display_name' => 'anything', 'name' => 'alice' ] );
        $array = $model->toArray();

        $this->assertSame( 'ALICE', $array['display_name'] );
    }

    public function test_only_returns_specified_keys(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'email' => 'a@b.com', 'score' => '10' ] );
        $result = $model->only( [ 'name', 'email' ] );

        $this->assertArrayHasKey( 'name', $result );
        $this->assertArrayHasKey( 'email', $result );
        $this->assertArrayNotHasKey( 'score', $result );
    }

    public function test_except_excludes_specified_keys(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice', 'email' => 'a@b.com', 'score' => '10' ] );
        $result = $model->except( [ 'score' ] );

        $this->assertArrayHasKey( 'name', $result );
        $this->assertArrayHasKey( 'email', $result );
        $this->assertArrayNotHasKey( 'score', $result );
    }

    // -------------------------------------------------------------------------
    // Hydration
    // -------------------------------------------------------------------------

    public function test_new_from_row_creates_instance_with_exists_true(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Alice' ] );

        $this->assertTrue( $model->exists() );
    }

    public function test_new_from_row_sets_attributes_and_originals(): void {
        $model = StubModel::newFromRow( [ 'name' => 'Alice' ] );

        $this->assertSame( 'Alice', $model->getAttribute( 'name' ) );
        $this->assertSame( 'Alice', $model->getOriginal( 'name' ) );
        $this->assertFalse( $model->isDirty() );
    }

    public function test_new_from_row_accepts_stdclass_object(): void {
        $model = StubModel::newFromRow( (object) [ 'id' => '1', 'name' => 'Alice' ] );

        $this->assertSame( 1, $model->id );
        $this->assertSame( 'Alice', $model->name );
    }

    public function test_hydrate_creates_collection_from_rows(): void {
        $rows = [
            (object) [ 'id' => '1', 'name' => 'Alice' ],
            (object) [ 'id' => '2', 'name' => 'Bob' ],
        ];

        $collection = StubModel::hydrate( $rows );

        $this->assertInstanceOf( Collection::class, $collection );
        $this->assertCount( 2, $collection );
        $this->assertSame( 1, $collection->first()->id );
    }

    // -------------------------------------------------------------------------
    // Entity Name
    // -------------------------------------------------------------------------

    public function test_get_entity_name_converts_class_to_snake_case(): void {
        $this->assertSame( 'stub_model', StubModel::getEntityName() );
    }

    public function test_get_entity_name_for_prefixed_model(): void {
        $this->assertSame( 'prefixed_model', PrefixedModel::getEntityName() );
    }

    // -------------------------------------------------------------------------
    // Table / Primary Key
    // -------------------------------------------------------------------------

    public function test_get_table_returns_prefixed_table_name(): void {
        $this->assertSame( 'wp_utils_stubs', StubModel::getTable() );
    }

    public function test_get_primary_key_returns_primary_key_column(): void {
        $this->assertSame( 'id', StubModel::getPrimaryKey() );
    }

    // -------------------------------------------------------------------------
    // Hook Name / Hook Prefix
    // -------------------------------------------------------------------------

    public function test_hook_name_without_prefix(): void {
        $this->assertSame( 'stub_model_saving', StubModel::hookName( 'stub_model_saving' ) );
    }

    public function test_hook_name_with_prefix(): void {
        $this->assertSame( 'myplugin_prefixed_model_saving', PrefixedModel::hookName( 'prefixed_model_saving' ) );
    }

    // -------------------------------------------------------------------------
    // Exists
    // -------------------------------------------------------------------------

    public function test_exists_returns_false_for_new_instance(): void {
        $model = new StubModel();

        $this->assertFalse( $model->exists() );
    }

    public function test_exists_returns_true_after_hydration(): void {
        $model = StubModel::newFromRow( [ 'id' => '1' ] );

        $this->assertTrue( $model->exists() );
    }

    // -------------------------------------------------------------------------
    // CRUD with mocked $wpdb
    // -------------------------------------------------------------------------

    public function test_save_inserts_new_model(): void {
        $model = new StubModel();
        $model->fill( [ 'name' => 'Alice', 'email' => 'a@b.com' ] );

        $this->wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );
        $this->wpdb->insert_id = 42;

        $result = $model->save();

        $this->assertTrue( $result );
        $this->assertTrue( $model->exists() );
        $this->assertSame( 42, $model->id );
    }

    public function test_save_updates_existing_model(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Alice' ] );
        $model->setAttribute( 'name', 'Bob' );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        $result = $model->save();

        $this->assertTrue( $result );
    }

    public function test_save_returns_true_when_nothing_dirty(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Alice' ] );

        $result = $model->save();

        $this->assertTrue( $result );
    }

    public function test_save_returns_false_on_insert_failure(): void {
        $model = new StubModel();
        $model->fill( [ 'name' => 'Alice' ] );

        $this->wpdb->shouldReceive( 'insert' )->once()->andReturn( false );

        $result = $model->save();

        $this->assertFalse( $result );
        $this->assertFalse( $model->exists() );
    }

    public function test_save_returns_false_on_update_failure(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Alice' ] );
        $model->name = 'Bob';

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( false );

        $result = $model->save();

        $this->assertFalse( $result );
    }

    public function test_save_syncs_originals_after_successful_update(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Alice' ] );
        $model->name = 'Bob';

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        $model->save();

        $this->assertFalse( $model->isDirty() );
        $this->assertSame( 'Bob', $model->getOriginal( 'name' ) );
    }

    public function test_create_static_method_inserts_and_returns_model(): void {
        $this->wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );
        $this->wpdb->insert_id = 10;

        $result = StubModel::create( [ 'name' => 'Alice', 'email' => 'alice@test.com' ] );

        $this->assertInstanceOf( StubModel::class, $result );
        $this->assertTrue( $result->exists() );
        $this->assertSame( 10, $result->id );
        $this->assertSame( 'Alice', $result->name );
    }

    public function test_update_method_fills_and_saves(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Alice', 'email' => 'a@b.com' ] );

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        $result = $model->update( [ 'name' => 'Bob' ] );

        $this->assertTrue( $result );
        $this->assertSame( 'Bob', $model->name );
    }

    public function test_delete_calls_force_delete_when_no_soft_deletes(): void {
        $model = StubModel::newFromRow( [ 'id' => '1' ] );

        $this->wpdb->shouldReceive( 'delete' )->once()->andReturn( 1 );

        $result = $model->delete();

        $this->assertTrue( $result );
        $this->assertFalse( $model->exists() );
    }

    public function test_force_delete_removes_model(): void {
        $model = StubModel::newFromRow( [ 'id' => '1' ] );

        $this->wpdb->shouldReceive( 'delete' )->once()->andReturn( 1 );

        $result = $model->forceDelete();

        $this->assertTrue( $result );
        $this->assertFalse( $model->exists() );
    }

    public function test_force_delete_returns_false_on_wpdb_failure(): void {
        $model = StubModel::newFromRow( [ 'id' => '1' ] );

        $this->wpdb->shouldReceive( 'delete' )->once()->andReturn( false );

        $result = $model->forceDelete();

        $this->assertFalse( $result );
        $this->assertTrue( $model->exists() );
    }

    public function test_fresh_reloads_model_from_database(): void {
        $model = StubModel::newFromRow( [ 'id' => '1', 'name' => 'Old Name' ] );

        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn( [ (object) [ 'id' => '1', 'name' => 'New Name' ] ] );

        $fresh = $model->fresh();

        $this->assertNotNull( $fresh );
        $this->assertSame( 'New Name', $fresh->name );
    }

    public function test_fresh_returns_null_when_not_found(): void {
        $model = StubModel::newFromRow( [ 'id' => '999' ] );

        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn( [] );

        $result = $model->fresh();

        $this->assertNull( $result );
    }

    public function test_find_or_fail_throws_when_not_found(): void {
        $this->mockWpdbPrepare();
        $this->wpdb->shouldReceive( 'get_results' )->andReturn( [] );

        $this->expectException( \RuntimeException::class );

        StubModel::findOrFail( 999 );
    }

    // -------------------------------------------------------------------------
    // Action Hooks — No Prefix
    // -------------------------------------------------------------------------

    public function test_save_fires_hooks_without_prefix(): void {
        $this->wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );
        $this->wpdb->insert_id = 1;

        Monkey\Actions\expectDone( 'stub_model_saving' )->once();
        Monkey\Actions\expectDone( 'stub_model_creating' )->once();
        Monkey\Actions\expectDone( 'stub_model_created' )->once();
        Monkey\Actions\expectDone( 'stub_model_saved' )->once();

        $model = new StubModel();
        $model->fill( [ 'name' => 'Alice' ] );
        $model->save();
    }

    // -------------------------------------------------------------------------
    // Action Hooks — With Prefix
    // -------------------------------------------------------------------------

    public function test_save_fires_hooks_with_prefix(): void {
        $this->wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );
        $this->wpdb->insert_id = 1;

        Monkey\Actions\expectDone( 'myplugin_prefixed_model_saving' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_creating' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_created' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_saved' )->once();

        $model = new PrefixedModel();
        $model->fill( [ 'name' => 'Alice' ] );
        $model->save();
    }

    public function test_update_fires_hooks_with_prefix(): void {
        $model = PrefixedModel::newFromRow( [ 'id' => '1', 'name' => 'Alice' ] );
        $model->name = 'Bob';

        $this->wpdb->shouldReceive( 'update' )->once()->andReturn( 1 );

        Monkey\Actions\expectDone( 'myplugin_prefixed_model_saving' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_updating' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_updated' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_saved' )->once();

        $model->save();
    }

    public function test_force_delete_fires_hooks_with_prefix(): void {
        $model = PrefixedModel::newFromRow( [ 'id' => '1' ] );

        $this->wpdb->shouldReceive( 'delete' )->once()->andReturn( 1 );

        Monkey\Actions\expectDone( 'myplugin_prefixed_model_deleting' )->once();
        Monkey\Actions\expectDone( 'myplugin_prefixed_model_deleted' )->once();

        $model->forceDelete();
    }
}
