<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AWSリクエストのキャッシュデータベースモデル
 */
class AwsCache extends Model
{
    use HasFactory;

    /**
     * @var string テーブル名
     */
    public $table = 'aws_cache';

}
