<?php

namespace Vits\LaravelSaveRelationships\Tests\Support;

use Illuminate\Database\Eloquent\Model;

class RelatedModel extends Model
{
    protected $table = 'related_models';

    protected $fillable = ['name'];
}
