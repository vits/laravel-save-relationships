<?php

namespace Vits\LaravelSaveRelationships\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Vits\LaravelSaveRelationships\SaveRelationships;

class TestModel extends Model
{
    use SaveRelationships;

    protected $table = 'test_models';

    protected $fillable = ['first', 'second', 'related', 'other', 'sync'];

    protected $saveRelationships = [
        'related',
        'other',
        'sync' => 'syncRelated'
    ];

    public function setSaveRelationships($relationships)
    {
        $this->saveRelationships = $relationships;
    }


    public function getUnsavedRelationships()
    {
        return $this->_unsaved_relationships;
    }

    public function related()
    {
        return $this->hasMany(RelatedModel::class);
    }

    public function other()
    {
        return $this->belongsToMany(OtherModel::class);
    }

    public function sync()
    {
        return $this->hasMany(RelatedModel::class);
    }

    public function syncRelated($data) {}
}
