<?php

namespace App\Http\Controllers;

use App\models\TapTin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function guiTin(Request $request)
    {
        $data = $request->all();
        $validator =  Validator::make($data, [
            'nhan_vien' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        if (count($data['nhan_vien'])) {
            return response(['message' => 'Người nhận không thể bỏ trống !'], 422);
        }
    }

    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $Files = $request->file('file');
            $file_id = [];
            $isTooBig = false;
            $maxSize = 20971520;
            DB::beginTransaction();
            foreach ($Files as $file) {
                try {
                    if ($file->getSize() > $maxSize) {
                        $isTooBig = true;
                    }
                    $name = rand(100000, 9999999) . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/files/', $name);
                    $taptin = TapTin::create(['message_id' => null, 'link' => 'storage/images/avatar/' . $name, 'name' => $name, 'size' => $file->getSize()]);
                    $file_id[] =  $taptin->id;
                } catch (\Exception $e) {
                    DB::rollBack();
                    return $e;
                    return response()->json(['message' => 'File không hợp lệ'], 403);
                }
            }
            if ($isTooBig) {
                DB::rollBack();
                return response(['message' => 'File dung lượng tối đa 20 Mb'], 422);
            }
            DB::commit();
            return $file_id;
        } else {
            return response()->json(['message' => 'File không tồn tại'], 404);
        }
    }
}
