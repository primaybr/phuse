<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Item Model Class
 *
 * This model handles the storage and retrieval of items in the application.
 * Currently uses a static array for demonstration purposes.
 */
class Item
{
    /**
     * Array of sample items
     *
     * @var array
     */
    private $items = [
        ['id' => 1, 'name' => 'Item 1'],
        ['id' => 2, 'name' => 'Item 2'],
        ['id' => 3, 'name' => 'Item 3'],
    ];

    /**
     * Retrieve all items from the data store
     *
     * @return array Array of all items
     */
    public function getAllItems(): array
    {
        return $this->items;
    }
}
