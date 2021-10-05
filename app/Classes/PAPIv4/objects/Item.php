<?php

namespace App\Classes\PAPIv4\objects;

use App\Classes\PAPIv4\SearchItemsRequest;

class Item
{
    /**
     * @var SearchItemsRequest
     */
    public $request;
    /**
     * @var string ASIN
     */
    public $asin;
    /**
     * @var string 書籍タイトル
     */
    public $title;
    /**
     * @var string 画像URL
     */
    public $img_src;
}
