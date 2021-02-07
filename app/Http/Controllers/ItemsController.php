<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
    public function showItems(Request $request)
    {
        // ORDER BY句のSQLを直接記述　
        // state,'selling','bought'  第一引数の次に第二引数で並べ替え
        $items = Item::orderByRaw("FIELD(state,'" .Item::STATE_SELLING. "', '".Item::STATE_BOUGHT . "')" )
          ->orderBy('id','DESC')
          ->paginate(52);

        return view('items.items')
          ->with('items',$items);
    }

    // ルートパラメータのitems/1からそのままModelからIDでデータ取得しそれをコントローラーに渡す
    // Route->Model->Controllerというながれ、ルートモデルバインディング
    public function showItemDetail(Item $item)
    {
      return view('items.item_detail')
        ->with('item',$item);
    }
}
