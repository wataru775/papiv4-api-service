<?php

namespace App\Classes\PAPIv4;

/**
 * 書籍タイトル取得APIレスポンス
 */
class GetTitleResponse
{
    /**
     * @var int 実行結果
     */
    public $status = 200;
    /**
     * @var GetItemsRequest リクエスト
     */
    public $request;
    /**
     * @var string 書籍タイトル
     */
    public $title;
    /**
     * @var string 書籍画像
     */
    public $img_src;
}
