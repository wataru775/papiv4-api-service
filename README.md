# AWSのAPI Service

AmazonのPAPIv5を実装します

## 使用方法

### 公開パス
 https://api.mmpp.org/papiv5/

|  パス  |  使用  |
| ---- | ---- |
|  getItems  |  アイテムを取得します  |
|  searchItems  |  キーワードからアイテムを取得します  |

### 環境設定

| 環境キー | 解説 | 値 |
| ---- | ---- | ---- |
|  AWS_SHOP_ID | ショップID (パートナーtag?) | mmppwataru01-22みたいなの |
| AWS_ACCESS_KEY | AWSへのアクセスキー | 自前のを作成してください |
| AWS_SECRET_KEY | AWSへのシークレットキー | 自前のを作成してください |
| ACTIVE_AWS_CACHE | AWSリクエストの結果をDBに保存する | true (default:false) |
| DB_CONNECTION | キャッシュ用のDB | sqlite |

アクセスキーなどはこちらにて作成できます
https://webservices.amazon.com/paapi5/documentation/register-for-pa-api.html

この辺りで発行できます
https://affiliate.amazon.co.jp/assoc_credentials/home

### 参照

- https://webservices.amazon.com/paapi5/documentation/

