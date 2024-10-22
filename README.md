# Save Laravel model relationships

Saves one or more Laravel model `HasMany`, `MorphsMany` or `BelongsToMany` relationships by assigning relationhip id values as model attributes. Saving ir done in model's `saved` event. Relationship data must be validated before saving. If transactional integrity is needed, it must be implemented in calling code.

## Installation

```shell
composer require vits/laravel-save-relationships
```

## Usage

```php
use Vits\LaravelSaveRelationships\SaveRelationships;

class Author extends Model
{
    use SaveRelationships;

    protected $saveRelationships = 'books';

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
...
$author->books = [1, 2, 3];
$author->save();
```

Multiple relationship names may be given as comma separated string or as array.

```php
protected $saveRelationships = 'books,series';
// or
protected $saveRelationships = ['books', 'series'];
```

Relationship may be saved by custom method. In this case all relationshop saving logic must be implemented in this method.

```php
protected $saveRelationships = ['books' => 'saveBooks'];
protected function saveBooks($books)
{
    //...
}
```
