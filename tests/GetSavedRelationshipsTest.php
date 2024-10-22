<?php

use Illuminate\Database\Eloquent\Model;
use Vits\LaravelSaveRelationships\SaveRelationships;


it('returns empty array when no model properties are given', function () {
    $model = new class extends Model {
        use SaveRelationships;
    };

    $relationships = invokePrivateMethod($model, 'getSavedRelationships');

    expect($relationships)
        ->toBe([]);
});

it('returns relationships array from model properties', function () {
    $model = new class extends Model {
        use SaveRelationships;
        protected $saveRelationships = [
            'first' => 'callback',
            'second'
        ];
    };

    $relationships = invokePrivateMethod($model, 'getSavedRelationships');

    expect($relationships)
        ->toBe([
            'first' => 'callback',
            'second' => null,
        ]);
});

it('builds relationships array from string', function () {
    $model = new class extends Model {
        use SaveRelationships;
        protected $saveRelationships = ' first  , second';
    };

    $relationships = invokePrivateMethod($model, 'getSavedRelationships');

    expect($relationships)
        ->toBe([
            'first' => null,
            'second' => null,
        ]);
});
