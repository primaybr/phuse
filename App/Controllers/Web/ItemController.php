<?php

namespace App\Controllers\Web;

use Core\Controller;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        $itemModel = new Item();
        $data['items'] = $itemModel->getAllItems();
        if (!is_array($data['items'])) {
            $data['items'] = []; // Ensure it's an array
        }
        $this->render('items/index', $data);
    }
}