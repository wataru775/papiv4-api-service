<?php

namespace App\Services;

use App\Classes\PAPIv4\objects\Item;
use App\Services\PAPIv4\AWSApiService;

use App\Classes\PAPIv4\GetItemsRequest;
use App\Classes\PAPIv4\GetTitleResponse;
use App\Classes\PAPIv4\SearchItemsRequest;
use App\Classes\PAPIv4\ScanResponse;

use App\Tools\AWSImageUtil;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * 書籍情報取得コントローラサービス
 */
class AWSControllerService
{
    /**
     * @var AWSApiService awsリポジトリ
     */
    private $apiService;

    public function __construct(AWSApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * 書籍タイトルを取得する
     * @param $request GetItemsRequest readTitleの解析リクエスト
     * @return GetTitleResponse getItems実行結果
     */
    public function getItems(GetItemsRequest $request): GetTitleResponse
    {
        Log::debug('AWSControllerService@getItems ' . $request);
        $response = new GetTitleResponse();

        try{
            $response->title = $this->apiService->getItems($request->asin);

            if($response->title === null){
                $response->status = 404;
            }
            $response->img_src = AWSImageUtil::makeImageSrc($request->asin);
        }catch (Exception $e){
            $response->status = 500;
        }

        return $response;
    }

    /**
     * バーコードからの書籍情報取得する
     * @param SearchItemsRequest $request Scanリクエスト情報
     * @return ScanResponse 実行結果
     */
    public function searchItems(SearchItemsRequest $request): ScanResponse
    {
        $response = new ScanResponse();

        try {
            // AWSサービス通信へ
            $items = $this->apiService->searchItems($request->keyword);

            // 検索結果を解析して値がある場合は引き継ぎ
            if ($items) {
                foreach ($items as $item){
//                    Log::debug('BookControllerService@scan $item: ' . print_r($item,true));
                    $makeItem = new Item();
                    $makeItem->asin = $item->asin;
                    $makeItem->title = $item->title;
                    $makeItem->img_src = AWSImageUtil::makeImageSrc($item->asin);
                    $response->items[] = $makeItem;
                }
            } else {
                // アイテムが見つからない場合
                $response->status = 404;
            }
        } catch (Exception $e) {
            $response->status = 500;
            Log::debug($e);
        }
        return $response;
    }
}
