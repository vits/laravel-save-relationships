<?php

namespace Vits\LaravelSaveRelationships\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Inertia\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Config::set('app.key', Str::random(32));

        $this->setupDatabase($this->app);
    }

    protected function setupDatabase($app): void
    {
        $builder = $app['db']->connection()->getSchemaBuilder();

        $builder->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name')->nullable();
        });

        $builder->create('related_models', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_model_id');
            $table->string('name');
            $table->timestamps();
        });

        $builder->create('other_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $builder->create('other_model_test_model', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_model_id');
            $table->integer('other_model_id');
        });
    }

    // /**
    //  * @param  \Illuminate\Foundation\Application  $app
    //  * @return array
    //  */
    // protected function getPackageProviders($app)
    // {
    //     $serviceProviders = [
    //         LaravelDataServiceProvider::class,
    //         ServiceProvider::class
    //     ];

    //     return $serviceProviders;
    // }
}
