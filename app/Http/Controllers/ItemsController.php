<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function showItems(Request $request)
    {
        $query = Item::query();

        // カテゴリで絞り込み
        // requestインスタンスのfilledメソッドでパラメータが指定されているかを調べることができる
        if($request->filled('category')){
          // 区切り文字の':'でexplodeし[categoryType=>7]のような連想配列を作成
          list($categoryType,$categoryID) = explode(':',$request->input('category'));

          if($categoryType === 'primary'){
            // Itemはsecondary_categoriesとしか紐付いていないため、whereHasを用いてそのリレーション先であるprimary_categoriesをとる
            // whereHasの第一引数でItemのリレーション定義のメソッド、第二引数で無名関数を定義しその先のテーブルに対する絞り込みを記述する
            $query->whereHas('secondaryCategory',function($query) use ($categoryID) {
              $query->where('primary_category_id',$categoryID);
            });
          } else if ($categoryID === 'secondary') {
            $query->where('secondary_category_id',$categoryID);
          }
        }

        // キーワードで絞り込み
        if($request->filled('keyword')) {
          // escapeで%や_をなくしてから%にくっつけてLIKE検索
          $keyword = '%' . $this->escape($request->input('keyword')) . '%';
          $query->where(function($query)use($keyword) {
            $query->where('name','LIKE',$keyword);
            $query->orWhere('description','LIKE',$keyword);
          });
        }

        // ORDER BY句のSQLを直接記述　
        // state,'selling','bought'  第一引数の次に第二引数で並べ替え
        $items = $query->orderByRaw( "FIELD(state, '" . Item::STATE_SELLING . "', '" . Item::STATE_BOUGHT . "')" )
          ->orderBy('id','DESC')
          ->paginate(52);

        return view('items.items')
          ->with('items',$items);
    }

    public function escape(string $value)
    {
        return str_replace(
          ['\\', '%', '_'],
          ['\\\\', '\\%', '\\_'],
          $value
        );
    }

    // ルートパラメータのitems/1からそのままModelからIDでデータ取得しそれをコントローラーに渡す
    // Route->Model->Controllerというながれ、ルートモデルバインディング
    public function showItemDetail(Item $item)
    {
      return view('items.item_detail')
        ->with('item',$item);
    }
}
