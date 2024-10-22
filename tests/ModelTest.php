<?php

use Vits\LaravelSaveRelationships\SaveRelationships;
use Vits\LaravelSaveRelationships\Tests\Support\OtherModel;
use Vits\LaravelSaveRelationships\Tests\Support\TestModel;

it('uses SaveRelationships trait', function () {
    expect(SaveRelationships::class)
        ->toBeIn(class_uses_recursive(TestModel::class));
});

it('calls getSavedRelationships() when assigning attribute value', function () {
    $spy = Mockery::mock(TestModel::class)->makePartial();
    /** @disregard P1013 Undefined method */
    $spy->setSaveRelationships('first');
    $spy->first = [1, 2];

    $spy->shouldHaveReceived('getSavedRelationships');
});

it('keeps unsaved values only for known relationships', function () {
    $spy = Mockery::mock(TestModel::class)->makePartial();
    /** @disregard P1013 Undefined method */
    $spy->setSaveRelationships('first');
    $spy->first = [1, 2];
    $spy->second = [3, 4];

    /** @disregard P1013 Undefined method */
    expect($spy)
        ->getUnsavedRelationships()
        ->toBe([
            'first' => [1, 2]
        ]);
});

it('calls specified sync method on model save', function () {
    $mock = Mockery::mock(TestModel::class . '[syncRelated]');
    $mock->shouldReceive('syncRelated')
        ->with(['name' => 'sync'])
        ->once();

    $mock->sync = ['name' => 'sync'];
    /** @disregard P1013 Undefined method */
    $mock->save();
    $this->assertDatabaseCount('test_models', 1);
});

describe('with HasMany relationship', function () {
    test('creates related records', function () {
        $model = TestModel::create();
        $this->assertDatabaseCount('related_models', 0);

        $model->update([
            'related' => [
                ['name' => 'first',],
                ['name' => 'second',]
            ]
        ]);
        $this->assertDatabaseCount('related_models', 2);
        $this->assertDatabaseHas('related_models', ['name' => 'first']);
        $this->assertDatabaseHas('related_models', ['name' => 'second']);
    });

    test('deletes related records except with matching ids', function () {
        $model = TestModel::create();
        $related1 = $model->related()->create(['name' => 'rel1']);
        $related2 = $model->related()->create(['name' => 'rel2']);
        $related3 = $model->related()->create(['name' => 'rel3']);
        $related4 = $model->related()->create(['name' => 'rel4']);
        $this->assertDatabaseCount('related_models', 4);

        $model->update([
            'related' => [
                ['id' => $related2->id],
                $related3->id
            ]
        ]);

        $this->assertDatabaseCount('related_models', 2);
        $this->assertDatabaseHas('related_models', ['name' => 'rel2']);
        $this->assertDatabaseHas('related_models', ['name' => 'rel3']);
    });

    test('keeps existing records if assigned attributes value is null or undefined', function () {
        $model = TestModel::create();
        $model->related()->create(['name' => 'rel1']);
        $model->related()->create(['name' => 'rel2']);

        $model->update([
            'related' => null
        ]);

        $model->update([]);

        $this->assertDatabaseCount('related_models', 2);
    });

    test('updates related record values', function () {
        $model = TestModel::create();
        $related1 = $model->related()->create(['name' => 'rel1']);

        $model->update([
            'related' => [
                ['id' => $related1->id, 'name' => 'updated'],
            ]
        ]);

        $this->assertDatabaseCount('related_models', 1);
        $this->assertDatabaseHas('related_models', ['name' => 'updated']);
    });

    test('creates new related record if record with given id not found', function () {
        $model = TestModel::create();
        $related1 = $model->related()->create(['name' => 'rel1']);

        $model->update([
            'related' => [
                ['id' => 999, 'name' => 'created'],
            ]
        ]);

        $this->assertDatabaseCount('related_models', 1);
        $this->assertDatabaseHas('related_models', ['name' => 'created']);
    });
});

describe('with BelongsToMany relationship', function () {
    test('attaches only existing related records', function () {
        $other = OtherModel::create(['name' => 'exists']);
        $this->assertDatabaseCount('other_models', 1);
        $model = TestModel::create([
            'other' => [$other->id, 111, ['id' => 999]]
        ]);

        $this->assertDatabaseCount('other_models', 1);

        expect($model->refresh()->other->count())->toBe(1);
    });

    test('detaches removed related records but does not delete them', function () {
        $other1 = OtherModel::create(['name' => 'other1']);
        $other2 = OtherModel::create(['name' => 'other2']);
        $this->assertDatabaseCount('other_models', 2);

        $model = TestModel::create([
            'other' => [$other1->id, $other2->id]
        ]);

        expect($model->refresh()->other->count())->toBe(2);

        $model->update(['other' => [$other2->id]]);

        $this->assertDatabaseCount('other_models', 2);
        expect($model->refresh()->other->count())->toBe(1);
        expect($model->other->first()->id)->toBe($other2->id);
    });

    test('keeps existing records if assigned attribute value is null or undefined', function () {
        $other1 = OtherModel::create(['name' => 'other1']);
        $other2 = OtherModel::create(['name' => 'other2']);
        $this->assertDatabaseCount('other_models', 2);

        $model = TestModel::create([
            'other' => [$other1->id, $other2->id]
        ]);

        $model->update([
            'other' => null
        ]);

        $model->update([]);

        expect($model->refresh()->other->count())->toBe(2);
    });

    test('does not update related record values', function () {
        $other1 = OtherModel::create(['name' => 'other1']);
        $this->assertDatabaseCount('other_models', 1);

        $model = TestModel::create([
            'other' => [[
                'id' => $other1->id,
                'name' => 'update'
            ]]
        ]);

        expect($model->refresh()->other->count())->toBe(1);
        $this->assertDatabaseCount('other_models', 1);
        $this->assertDatabaseHas('other_models', ['name' => 'other1']);
    });
});
