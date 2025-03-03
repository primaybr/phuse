<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use Core\Controller;
use App\Models\Item;

/**
 * ItemController Class
 *
 * This controller handles operations related to items in the application
 * including listing, creating, updating, and deleting items.
 */
class ItemController extends Controller
{
    /**
     * Display a listing of all items
     *
     * @return void
     */
    public function index(): void
    {
        $itemModel = new Item();
        $data['items'] = $itemModel->getAllItems();
        if (!is_array($data['items'])) {
            $data['items'] = []; // Ensure it's an array
        }
        $this->render('items/index', $data);
    }
}