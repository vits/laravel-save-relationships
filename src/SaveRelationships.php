<?php

namespace Vits\LaravelSaveRelationships;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait SaveRelationships
{
    protected $_unsaved_relationships = [];

    public static function bootSaveRelationships()
    {
        static::saved(
            function ($model) {
                foreach ($model->getSavedRelationships() as $name => $method) {
                    if (!array_key_exists($name, $model->_unsaved_relationships)) {
                        continue;
                    }

                    $data = $model->_unsaved_relationships[$name];
                    if ($data === null) {
                        continue;
                    }

                    if ($method) {
                        $model->{$method}($data);
                    } else if ($model->{$name}() instanceof HasMany || $model->{$name}() instanceof MorphMany) {
                        $model->_saveHasManyRelationship($name, $data);
                    } else if ($model->{$name}() instanceof BelongsToMany) {
                        $model->_saveBelongsToManyRelationship($name, $data);
                    }
                }
            }
        );
    }

    public function setAttribute($name, $data)
    {
        // TODO improve performance
        if (!array_key_exists($name, $this->getSavedRelationships())) {
            return parent::setAttribute($name, $data);
        }

        $this->_unsaved_relationships[$name] = $data;

        return $this;
    }

    /**
     * Return array of defined attributes.
     *
     * @return array
     */
    protected function getSavedRelationships(): array
    {
        $relationships = $this->saveRelationships ?? [];

        if (is_string($relationships)) {
            $relationships = array_values(
                array_filter(array_map('trim', explode(',', $relationships)))
            );
        }

        $relationships = array_reduce(
            array_keys($relationships),
            function ($carry, $key) use ($relationships) {
                if (is_string($key)) {
                    $carry[$key] = $relationships[$key];
                } else {
                    $carry[$relationships[$key]] = null;
                }
                return $carry;
            },
            []
        );

        return $relationships;
    }

    private function _saveHasManyRelationship($relationship, $data)
    {
        $existing = $this->refresh()->{$relationship};
        $new = collect($data)->map(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            return [
                'id' => $item
            ];
        })->where('_delete', false);

        foreach ($existing->pluck('id')->diff($new->pluck('id')) as $id) {
            $delete = $existing->find($id);
            $delete->delete();
        }

        $updatedIds = $existing->pluck('id')->intersect($new->pluck(['id']))->toArray();

        foreach ($new as $item) {
            if (array_key_exists('id', $item) && in_array($item['id'], $updatedIds)) {
                $updated = $existing->find($item['id']);
                $updated->update($item);
            } else {
                $this->{$relationship}()->create($item);
            }
        }
    }

    private function _saveBelongsToManyRelationship($relationship, $data)
    {
        $relatedClass = $this->{$relationship}()->getRelated();
        $new = collect($data)->map(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            return [
                'id' => $item
            ];
        })->where('_delete', false);

        $existing = $relatedClass->whereIn('id', $new->pluck('id'))->get();
        $newIds = [];
        foreach ($new as $item) {
            if (!($item['id'] ?? null)) {
                $created = $relatedClass::create($item);
                $newIds[] = $created->id;
            }
        }
        $this->{$relationship}()->sync([
            ...$newIds,
            ...($existing->pluck('id')->toArray())
        ]);
    }
}
