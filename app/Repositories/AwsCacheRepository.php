<?php

namespace App\Repositories;

use App\Models\AwsCache;

/**
 * AWSキャッシュリポジトリ
 */
class AwsCacheRepository
{

    /**
     * AWSキャッシュを調査します
     * @param string $keyword キーワード
     * @param int $page ページ番号
     * @return mixed キャッシュ情報 (null : なし)
     */
    public function find(string $keyword , int $page) {
        return AwsCache::where([['keyword' , '=' , $keyword],['page' , '=' , $page]])
            ->first();
    }

    /**
     * AWSキャッシュを保存します
     * @param string $keyword キーワード
     * @param int $page ページ番号
     * @param string $items 保存アイテム
     */
    public function save(string $keyword , int $page ,string $items) {
        $cache = new AwsCache();

        $cache->keyword = $keyword;
        $cache->page = $page;
        $cache->data = $items;
        $cache->save();
    }
}
