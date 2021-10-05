<?php
namespace App\Tools;

use App\Classes\AWSImageTag;

class AWSImageUtil
{
    /**
     * AWSの広告&画像データのタグを作成します
     * @param string $ASIN ASIN
     * @return AWSImageTag Imageタグデータ
     */
    public static function makeTags(string $ASIN) : AWSImageTag {
        $tag = new AWSImageTag();
        $tag->href = self::makeHref($ASIN);
        $tag->img_src = self::makeImageSrc($ASIN);
        $tag->img_src2 = self::makeImageSrc2($ASIN);
        return $tag;
    }

    /**
     * AWSの広告urlを取得します
     * @param string $ASIN AWS
     * @return string リンクパス
     */
    public static function makeHref(string $ASIN) : string{
        return 'https://www.amazon.co.jp/gp/product/'
            . $ASIN . '/ref=as_li_tf_il?ie=UTF8&camp=247&creative=1211'
            . '&creativeASIN=' . $ASIN
            . '&linkCode=as2'
            . '&tag=' . env('AWS_SHOP_ID');
    }

    /**
     * AWS画像データパスを取得します
     * @param string $ASIN ASIN
     * @return string 画像パス
     */
    public static function makeImageSrc(string $ASIN) : string{
        return 'http://ws-fe.amazon-adsystem.com/widgets/q?_encoding=UTF8'
            . '&ASIN=' .$ASIN
            . '&Format=_SL250_&ID=AsinImage&MarketPlace=JP&ServiceVersion=20070822&WS=1'
            . '&tag=' . env('AWS_SHOP_ID');
    }

    /**
     * AWS画像データパス2を取得します
     * @param string $ASIN ASIN
     * @return string 画像パス
     */
    public static function makeImageSrc2(string $ASIN) : string{
        return 'http://ir-jp.amazon-adsystem.com/e/ir?'
            . 't=' . env('AWS_SHOP_ID')
            . '&l=as2&o=9'
            . '&a=' . $ASIN ;
    }
}
