<?php

namespace App\Services;

use App\Models\AwsCache;
use App\Repositories\AwsCacheRepository;
use Illuminate\Support\Facades\Log;

/**
 * AWSキャッシュサービス
 */
class CachingService
{
    /**
     * @var AwsCacheRepository AWSキャッシュリポジトリ
     */
    private $awsCacheRepository;

    public function __construct(AwsCacheRepository $awsCacheRepository){
        $this->awsCacheRepository = $awsCacheRepository;
    }

    /**
     * キャッシュ情報を調査します
     * @param string $keyword キーワード
     * @param int $page ページ番号
     * @return mixed キャッシュ情報 (null : なし)
     */
    public function found(string $keyword , int $page) {
        return $this->awsCacheRepository->find($keyword , $page );
    }

    /**
     * キャッシュ情報を取得します
     * @param string $keyword キーワード
     * @param int $page ページ番号
     * @return \stdClass|null キャッシュ情報 JSON( null : なし)
     */
    public function get(string $keyword , int $page) : ?\stdClass {
        $cache = $this->found($keyword,$page);
        if($cache === null){
            return null;
        }
        return $cache?json_decode($cache->data):null;
    }

    /**
     * キャッシュ情報を格納します
     * @param string $keyword キーワード
     * @param int $page ページ番号
     * @param \stdClass $items キャッシュ情報(オクジェクト)
     */
    public function set(string $keyword , int $page , \stdClass $items) {
        $this->awsCacheRepository->save($keyword , $page , json_encode($items));
    }
}
