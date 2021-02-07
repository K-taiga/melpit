<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // 出品中
    const STATE_SELLING = 'selling';
    // 購入済み
    const STATE_BOUGHT  = 'bought';

    public function secondaryCategory()
    {
        return $this->belongsTo(SecondaryCategory::class);
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
