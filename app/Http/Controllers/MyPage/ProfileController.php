<?php

namespace App\Http\Controllers\MyPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Mypage\Profile\EditRequest;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showProfileEditForm()
    {
        return view('mypage.profile_edit_form')
        // bladeにAuth::userでログインしているユーザーをuserで渡す
            ->with('user',Auth::user());
    }

    // メソッドインジェクション 引数のクラスを生成し渡してくれる
    public function editProfile(EditRequest $request) {
        $user = Auth::user();

        $user->name = $request->input('name');
        $user->save();

        // 直前のページにリダイレクトする
        return redirect()->back()
        // statusに文字列を入れ返す
            ->with('status','プロフィールを変更しました。');
    }
}
