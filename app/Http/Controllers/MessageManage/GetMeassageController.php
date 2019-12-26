<?php

namespace App\Http\Controllers\MessageManage;

use App\Http\Controllers\Controller;
use App\Models\chat_records;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetMeassageController extends Controller
{
    //
    public function adminGetmessage()//获取最后
    {
        $datas=[];
        $info_from=users::select("users.department_name","chat_records.*")->join("chat_records",function ($join){
            $join->on('users.id', '=','chat_records.from_user_id');
        })->orderBy('created_at','desc')->get()->groupBy("from_user_id")->toArray();
        $info_to=users::select("users.department_name","chat_records.*")->join("chat_records",function ($join){
            $join->on('users.id', '=','chat_records.to_user_id');
        })->orderBy('created_at','desc')->where("to_user_id","<>",1)->get()->groupBy("to_user_id")->toArray();

      dd($info_to);
        foreach ($info_from as $key_from)
        {
            $reply=null;
            $reply_time=null;
            foreach ($info_to as $key_to)
            {
                if($key_from[0]["from_user_id"]==$key_to[0]["to_user_id"])
                {
                    if(strtotime($key_to[0]["created_at"])>strtotime($key_from[0]["created_at"]))
                    {
                        $reply_time=$key_from[0]["created_at"];
                        $reply="true";
                    }
                    else
                    {
                        $reply_time=$key_to[0]["created_at"];
                        $reply="false";
                    }
                    break;
                }
            }
            array_push($datas,[
                "team"=>$key_from[0]["department_name"],
                "reply"=>$reply,
                "from"=>$key_from[0]["from_user_name"],
                "reply_time"=>$reply_time,
            ]);
        }
        return response()->json(["code"=>200,"msg"=>"success","data"=>$datas]);
    }
    public function getmessage($search=[])
    {
        if ($search=null)
        {
            $info=chat_records::adminGetInfo(1);
            dd($info);
        }
        else
        {
            $info = chat_records::adminGetInfo(1);
        }
    }
}
