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
     * @param string $uriPath URI
     * @param string $payload ペイロード
     * @param string $target ターゲット
     * @return mixed キャッシュ情報 (null : なし)
     */
    public function find(string $uriPath,string $payload,string $target) {
        return AwsCache::where([['uri' , '=' , $uriPath],['payload' , '=' , $payload],['target',$target]])
            ->first();
    }

    /**
     * AWSキャッシュを保存します
     * @param string $uriPath URI
     * @param string $payload ペイロード
     * @param string $target ターゲット
     * @param string $items 保存アイテム
     */
    public function save(string $uriPath,string $payload,string $target ,string $items) {
        $cache = new AwsCache();

        $cache->uri = $uriPath;
        $cache->payload = $payload;
        $cache->target = $target;
        $cache->data = $items;
        $cache->save();
    }
}
