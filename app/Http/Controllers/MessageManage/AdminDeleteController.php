<?php

namespace App\Http\Controllers\MessageManage;

use App\Http\Controllers\Controller;
use App\Models\chat_records;
use App\Utils\Logs;
use Illuminate\Http\Request;

class AdminDeleteController extends Controller
{
    public function adminDelete(Request $request)
    {
        $user_id = $request->input("user_id");
        try
        {
          chat_records::where("from_user_name","=",$user_id)->orWhere("to_user_id","=",$user_id)->delete();
          return response()->json(["code"=>200,"删除成功","data"=>[]],200);
        }catch (\Exception $exception)
        {
            Logs::logError("数据库删除错误",[$exception->getMessage()]);
            return response()->json(["code"=>100,"删除失败","data"=>[]],100);
        }

    }

}
