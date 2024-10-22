<?php

namespace Vits\LaravelSaveRelationships\Tests\Support;

use Illuminate\Database\Eloquent\Model;

class OtherModel extends Model
{
    protected $table = 'other_models';

    protected $fillable = ['name'];
}
