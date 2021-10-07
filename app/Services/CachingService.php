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
    const LOG_PREFIX = '[CachingService]';
    /**
     * @var AwsCacheRepository AWSキャッシュリポジトリ
     */
    private $awsCacheRepository;

    public function __construct(AwsCacheRepository $awsCacheRepository){
        $this->awsCacheRepository = $awsCacheRepository;
    }

    /**
     * キャッシュ情報を調査します
     * @param string $uriPath URI
     * @param string $payload ペイロード
     * @param string $target ターゲット
     * @return mixed キャッシュ情報 (null : なし)
     */
    public function found(string $uriPath,string $payload,string $target) {
        return $this->awsCacheRepository->find($uriPath,$payload,$target );
    }

    /**
     * キャッシュ情報を取得します
     * @param string $uriPath URI
     * @param string $payload ペイロード
     * @param string $target ターゲット
     * @return \stdClass|null キャッシュ情報 JSON( null : なし)
     */
    public function get(string $uriPath,string $payload,string $target) : ?\stdClass {
        $cache = $this->found($uriPath,$payload,$target);
        if($cache === null){
            return null;
        }
        $json = json_decode($cache->data);
        Log::debug( self::LOG_PREFIX . ' in cache. ' . count($json->SearchResult->Items) . ' items.');
        return $cache?$json:null;
    }

    /**
     * キャッシュ情報を格納します
     * @param string $uriPath URI
     * @param string $payload ペイロード
     * @param string $target ターゲット
     * @param \stdClass $json キャッシュ情報(オクジェクト)
     */
    public function set(string $uriPath,string $payload,string $target , \stdClass $json) {
        // 実行結果を評価 (エラーかどうかの判断)
        if (property_exists($json, 'Errors')) {
            Log::debug( self::LOG_PREFIX .' not store cache. [ Errors ]');
            return ;
        }
        Log::debug( self::LOG_PREFIX . ' store. ' . count($json->SearchResult->Items) . ' items.');
        $this->awsCacheRepository->save($uriPath , $payload , $target , json_encode($json));
    }
}
