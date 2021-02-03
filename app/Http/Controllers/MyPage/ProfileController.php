<?php

namespace App\Http\Controllers\MyPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Mypage\Profile\EditRequest;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Image;

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

        if($request->has('avatar')){
            $fileName = $this->saveAvatar($request->file('avatar'));
            $user->avatar_file_name = $fileName;
        }

        $user->save();

        // 直前のページにリダイレクトする
        return redirect()->back()
        // statusに文字列を入れ返す
            ->with('status','プロフィールを変更しました。');
    }
    
    /**
     * saveAvatar アバター画像をリサイズして保存する
     *
     * @param  mixed $file
     * @return string ファイル名
     */
    private function saveAvatar(UploadedFile $file): string
    {
        // 一時ファイル生成時用のパスを取得
        $tempPath = $this->makeTempPath();

        // リサイズして一時保存
        Image::make($file)->fit(200,200)->save($tempPath);

        // Storageファサードで画像をpuclicのdiskに保存する
        // 他にlocal,S3のdiskが選べる
        $filePath = Storage::disk('public')->putFile('avatars',new File($tempPath));

        return basename($filepath);
    }
    
    /**
     * makeTempPath
     *
     * @return sring ファイルパス
     */
    private function makeTempPath(): string
    {
        // 一時ファイルを/tmpに生成しファイルポインタを返す
        $tmp_fp = tmpfile();
        // ファイルのメタ情報を取得
        $meta = stream_get_meta_data($tmp_fp);
        // メタ情報からURI(ファイルのパス)を取得
        return $meta["uri"];
    }
}
