<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Payjp\Charge;

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

    // ルートモデルバインディング
    public function showBuyItemForm(Item $item)
    {
      if(!$item->isStateSelling) {
        abort(404);
      }

      return view('items.item_buy_form')
        ->with('item',$item);
    }
    
    public function buyItem(Request $request, Item $item)
    {
      $user = Auth::user();

      if (!$item->isStateSelling) {
        abort(404);
      }

      $token = $request->input('card-token');

      try {
        $this->settlement($item->id,$item->seller->id,$user->id,$token);
      } catch (\Exception $e) {
        Log::error($e);
        return redirect()->back()
          ->with('type', 'danger')
          ->with('message', '購入処理が失敗しました。');
      }
      
      return redirect()->route('item',[$item->id])
        ->with('message','商品を購入しました。');
    }

    private function settlement($itemID, $sellerID, $buyerID, $token)
    {
      DB::beginTransaction();

      try {
        $seller = User::lockForUpdate()->find($sellerID);
        $item = Item::lockForUpdate()->find($itemID);

        if ($item->isStateBought) {
          throw new \Exception('多重決済');
        }

        $item->state = Item::STATE_BOUGHT;
        $item->bought_at = Carbon::now();
        $item->buyer_id  = $buyerID;
        $item->save();

        $seller->sales += $item->price;
        $seller->save();

        $charge = Charge::create([
          'card' => $token,
          'amount' => $item->price,
          'currency' => 'jpy'
        ]);

        if(!$charge->captured) {
          throw new \Exception('支払い確定失敗');
        }
      } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
      }

      DB::commit();
    }
}
