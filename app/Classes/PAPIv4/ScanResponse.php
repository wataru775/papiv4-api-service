<?php

namespace App\Classes\PAPIv4;

/**
 * ./admin/api/aws/scanの実行結果
 */
class ScanResponse
{
    /**
     * @var int 実行結果 200:OK
     */
    public $status = 200;

    public $items = [];

}
