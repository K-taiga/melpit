<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // 出品中
    const STATE_SELLING = 'selling';
    // 購入済み
    const STATE_BOUGHT  = 'bought';

    // $castsフィールドで取り出したDBの型を変更できる
    // bought_atをtimestampからdatetaime(Carbonクラス)に変換
    protected $casts = [
        'bought_at' => 'datetime',
    ];

    public function secondaryCategory()
    {
        return $this->belongsTo(SecondaryCategory::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class,'seller_id');
    }

    public function condition()
    {
        return $this->belongsTo(ItemCondition::class,'item_condition_id');
    }

    // get***AttributeでDBから取得した値を加工した結果を***で参照できる
    // Booleanを返す
    public function getIsStateSellingAttribute()
    {
        return $this->state === self::STATE_SELLING;
    }

    public function getIsStateBoughtAttribute()
    {
        return $this->state === self::STATE_BOUGHT;
    }
}
