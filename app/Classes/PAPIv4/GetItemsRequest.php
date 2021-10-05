<?php

namespace App\Classes\PAPIv4;

/**
 * 書籍タイトル取得APIリクエスト
 */
class GetItemsRequest
{
    /**
     * @var string ASINコード
     */
    public $asin;

    public function __toString(){
        return 'GetItemsRequest ( '
            . ' asin : ' . $this->asin
            . ')';
    }
}
