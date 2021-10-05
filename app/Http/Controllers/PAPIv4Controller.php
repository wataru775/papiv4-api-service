<?php

namespace App\Http\Controllers;

use App\Services\AWSControllerService;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;

use App\Classes\PAPIv4\GetItemsRequest;
use App\Classes\PAPIv4\SearchItemsRequest;
use Illuminate\Support\Facades\Log;

/**
 * AWS書籍情報取得APIコントローラ
 */
class PAPIv4Controller
{
    /**
     * @var AWSControllerService コントローラサービス
     */
    private $controllerService;
    public function __construct(AWSControllerService $controllerService){
        $this->controllerService = $controllerService;
    }

    /**
     * 書籍情報取得アクション
     * @param Request $currentRequest HTTPリクエスト
     * @return JsonResponse 検索結果
     */
    public function getItems(Request $currentRequest): JsonResponse
    {
        $request = $this->makeGetItemsRequest($currentRequest);

        $response = $this->controllerService->getItems($request);

        // 解析リクエストをレスポンスに含める
        $response->request = $request;

        return response()->json($response);
    }

    /**
     * 書籍情報取得リクエストをHTTPリクエストから変換します
     * @param Request $currentRequest HTTPリクエスト
     * @return GetItemsRequest 書籍情報取得リクエスト
     */
    private function makeGetItemsRequest(Request $currentRequest): GetItemsRequest
    {
        $request = new GetItemsRequest();
        // 条件ASINを格納
        $request->asin = $currentRequest->asin;

        return $request;
    }

    /**
     * 書籍のバーコード検索アクション
     * @param Request $currentRequest HTTPリクエスト
     * @return JsonResponse
     */
    public function searchItems(Request $currentRequest): JsonResponse
    {
        $request = $this->makeSearchItemsRequest($currentRequest);

        $response = $this->controllerService->searchItems($request);

        // 解析リクエストをレスポンスに含める
        $response->request = $request;

        return response()->json($response);

    }

    /**
     * scanリクエストをHTTPリクエストから生成します
     * @param Request $currentRequest HTTPリクエスト
     * @return SearchItemsRequest scanリクエスト
     */
    private function makeSearchItemsRequest(Request $currentRequest): SearchItemsRequest
    {
        $request = new SearchItemsRequest();
        $request->keyword = $currentRequest->keyword;
        return $request;
    }
}
