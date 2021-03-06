<?php
namespace App\Services\PAPIv4;

use App\Classes\PAPIv4\objects\Item;
use App\Services\CachingService;
use Illuminate\Support\Facades\Log;

use \Exception;
use \stdClass;
use function Psy\debug;

/**
 * サンプルより
 * @see https://webservices.amazon.co.jp/paapi5/scratchpad/index.html
 */
class AWSApiService
{
    /* Copyright 2018 Amazon.com, Inc. or its affiliates. All Rights Reserved. */
    /* Licensed under the Apache License, Version 2.0. */

    const LOG_PREFIX = '[AWSApiService]';

    private $cachingService;
    public function __construct(CachingService $cachingService){
        $this->cachingService = $cachingService;
    }

    private const AWS_HOST = "webservices.amazon.co.jp";

    /**
     * ASINから書籍タイトルを取得します
     * @param $asin string ASIN
     * @return string|null 書籍タイトル
     * @throws Exception
     */
    function getItems(string $asin): ?string
    {
        Log::debug(self::LOG_PREFIX . '@getitems try asin: ' . $asin);
        $partnerTag = env('AWS_SHOP_ID');

        $payload="{"
            ." \"ItemIds\": ["
            ."  \"" . $asin . "\""
            ." ],"
            ." \"Resources\": ["
            ."  \"ItemInfo.Title\""
            ." ],"
            ." \"PartnerTag\": \"" . $partnerTag . "\","
            ." \"PartnerType\": \"Associates\","
            ." \"Marketplace\": \"www.amazon.co.jp\""
            ."}";
        $uriPath="/paapi5/getitems";
        $target = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems';

        // AWSに接続します
        $json = $this->connectAwsPAPIv4($uriPath , $payload , $target);

        // 実行結果を評価 (エラーかどうかの判断)
        if(property_exists($json,'Errors')){
            Log::debug(self::LOG_PREFIX .'@searchBarcodeItems result: ' . 'NoResults');
            return null;
        }

        // 結果の引き継ぎ
        foreach ($json->ItemsResult->Items as $item){
            Log::debug(self::LOG_PREFIX .'@getitems result: '
                . 'Items [ '
                . 'ASIN : ' . $item->ASIN
                . ' title : ' . $item->ItemInfo->Title->DisplayValue
                . ' url : ' . $item->DetailPageURL
            );
        }

        $item = $json->ItemsResult->Items[0];
        return $item->ItemInfo->Title->DisplayValue;
    }

    /**
     * キーワードを検索条件にASINとタイトルを検索します
     * @param $keyword string キーワード
     * @return array|null 検索結果
     * @throws Exception
     */
    function searchItems(string $keyword): ?array
    {
        Log::debug(self::LOG_PREFIX . '@searchItems try $keyword: ' . $keyword);
        $partnerTag = env('AWS_SHOP_ID');

        $items = [];
        $uriPath="/paapi5/searchitems";
        $target = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems';

        for($i = 1 ; $i <= 10 ; $i++) {

            $payload = "{"
                . " \"Keywords\": \"" . $keyword . "\","
                . " \"PartnerTag\": \"" . $partnerTag . "\","
                . " \"PartnerType\": \"Associates\","
                . " \"SearchIndex\": \"Books\","
                . " \"ItemPage\": " . $i . ","
                . " \"Marketplace\": \"www.amazon.co.jp\""
                . "}";

            Log::debug(self::LOG_PREFIX . '@searchItems connect $payload: ' . $payload);

            $json = [];
            try {
                // AWSに接続します
                $json = $this->connectAwsPAPIv4($uriPath, $payload, $target);
            } catch (Exception $e) {
                if (count($items) === 0) {
                    throw $e;
                }
            }

            // 実行結果を評価 (エラーかどうかの判断)
            if (property_exists($json, 'Errors')) {
                if (count($items) !== 0) {
                    // 結果が含まれている場合はループを終了
                    break;
                }
                Log::debug(self::LOG_PREFIX . '@searchItems result: ' . 'NoResults');
                return null;
            }
            // 結果の引き継ぎ
            foreach ($json->SearchResult->Items as $item) {
                $makeItem = $this->makeItem($item);
                $items[] = $makeItem;
                Log::debug(self::LOG_PREFIX . ' ASIN: ' . $makeItem->asin . ' TITLE: ' . $makeItem->title);
            }

            // 1リクエストが10件以内の場合は処理を終了
            if (count($items) < 10) {
                break;
            }

        }
        Log::debug(self::LOG_PREFIX . '@searchItems result: count ' . count($items));

        return $items;
    }

    /**
     * AWS通信クラスを生成します
     * @param $uriPath string アクセス　URI
     * @param $payload string ペイロード
     * @param $target string サービス種別
     * @return AwsV4
     */
    private function makeAwsv4(string $uriPath ,string $payload , string $target) : AwsV4{
        $accessKey = env('AWS_ACCESS_KEY');
        $secretKey = env('AWS_SECRET_KEY');

        $serviceName="ProductAdvertisingAPI";
        $region="us-west-2";

        $awsv4 = new AwsV4 ($accessKey, $secretKey);
        $awsv4->setRegionName($region);
        $awsv4->setServiceName($serviceName);
        $awsv4->setRequestMethod ("POST");

        $awsv4->addHeader ('x-amz-target', $target);
        $awsv4->addHeader ('content-encoding', 'amz-1.0');
        $awsv4->addHeader ('content-type', 'application/json; charset=utf-8');
        $awsv4->addHeader ('host', self::AWS_HOST);

        $awsv4->setPath ($uriPath);
        $awsv4->setPayload ($payload);

        return $awsv4;
    }

    /**
     * AWSのバーコード取得結果を返却クラスに変換します
     * @param $awsResponse stdClass AWS通信結果のItem
     * @return Item 実行結果Item
     */
    private function makeItem(stdClass $awsResponse) : Item
    {
        $item = new Item();

        $item->asin = $awsResponse->ASIN;
        $item->title = $awsResponse->ItemInfo->Title->DisplayValue;


        return $item;
    }

    /**
     * AWSへ接続します
     * @param string $uriPath URI
     * @param string $payload 要求
     * @param string $target サービス種別
     * @return mixed 実行結果
     * @throws Exception
     */
    private function connectAwsPAPIv4(string $uriPath, string $payload, string $target)
    {
        Log::debug(self::LOG_PREFIX . " target : " . $target);
        // キャッシュをチェック
        Log::debug(self::LOG_PREFIX . "[cache] mode " . ((env('ACTIVE_AWS_CACHE') === true )?'true':'false'));

        // キャッシュ機能の有無チェック
        if(env('ACTIVE_AWS_CACHE')) {
            // キャッシュをチェック
            $cache =  $this->cachingService->get($uriPath, $payload,$target);
            if($cache){
                Log::debug(self::LOG_PREFIX . "[cache] connect... result in cache ");
                return $cache;
            }
        }

        $awsv4 = $this->makeAwsv4( $uriPath , $payload , $target);
        $headers = $awsv4->getHeaders();
        $headerString = "";
        foreach ( $headers as $key => $value ) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }
        $params = array (
            'http' => array (
                'header' => $headerString,
                'method' => 'POST',
                'content' => $payload
            )
        );
        $stream = stream_context_create ( $params );

        $url = 'https://' . self::AWS_HOST . $uriPath;
        Log::debug(self::LOG_PREFIX . ' connecting ... url : ' . $url . ' payload : ' . $payload);
        $fp = @fopen ($url , 'rb', false, $stream );

        if (! $fp) {
            // retry ...
            Log::debug(self::LOG_PREFIX . ' connecting retry ... url : ' . $url . ' payload : ' . $payload);
            sleep(1);

            $fp = @fopen ($url , 'rb', false, $stream );

            if (! $fp) {
                throw new Exception("Exception Occured");
            }
        }
        $response = @stream_get_contents ( $fp );
        if ($response === false) {
            throw new Exception( "Exception Occured" );
        }

        $json = json_decode($response);

        // 実行結果を評価 (エラーかどうかの判断)
        if (!property_exists($json, 'Errors')) {

            $items = [];
            // getItemsとsearchItemsの結果により表示パラメタが違う
            if (property_exists($json, 'ItemsResult')) {
                $items = $json->ItemsResult->Items;
            } elseif (property_exists($json, 'SearchResult')) {
                $items = $json->SearchResult->Items;
            } else {
                throw new Exception('不明な結果');
            }
            // 結果をキャッシング
            // キャッシュ機能の有無チェック
            if (env('ACTIVE_AWS_CACHE')) {
                // キャッシュを保存
                $this->cachingService->set($uriPath, $payload, $target, $json);
            }
            Log::debug(self::LOG_PREFIX . " connected. " . count( $items ) . ' items.');
        }else{
            Log::debug(self::LOG_PREFIX . " connected. = ERROR = ");
        }

        return $json;
    }
}
