<?php

namespace App\Http\Controllers\MessageManage;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageManage\Search;
use App\Models\chat_records;
use App\Models\users;
use App\Utils\Logs;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class GetMeassageController extends Controller
{
    //
    public function adminGetmessage(Request $request)//
    {
        try
        {
            $datas = [];
            $info_admin = users::select("users.department_name", "chat_records.*")->join("chat_records", function ($join) {
            $join->on('users.id', '=', 'chat_records.from_user_id');
        })->where("from_user_id", "=", Auth::id())->orderBy('created_at', 'desc')->get()->groupBy("to_user_id")->toArray();//管理员发送消息分组
            $info_users = users::select("users.department_name", "chat_records.*")->join("chat_records", function ($join) {
                $join->on('users.id', '=', 'chat_records.to_user_id');
            })->where("to_user_id", "=", Auth::id())->orderBy('created_at', 'desc')->get()->groupBy("from_user_id")->toArray();//用户发送信息分组，最新信息在顶部
            $datas=$this->compara_1($this->disposeData($info_admin),$this->disposeData($info_users));
            $paginator = $this->paginator($request,$datas);//分页
            return response()->json(["code" => 200, "msg" => "success", "data" => $paginator]);
        }catch (\Exception $exception)
        {
            Logs::logError("获取数据失败",[$exception->getMessage()]);
            return response()->json(["code"=>100,"msg"=>'获取信息失败',"data"=>[]],100);
        }

    }
    public function compara_1($array_1, $array_2)
    {
        $datas = [];
        $info_admin = $array_1;
        $info_users = $array_2;
        $list_admin = array_pad([],count($info_admin),0);//保存未比较的下标
        $list_users = array_pad([],count($info_users),0);//保存未比较的下标
        $reply_time = null;
        $reply = null;
        $x = 0;//标记 user
        foreach ($info_users as $key_user)//比较时间
        {
            $i = 0;//标记 admin
            while($i <count($info_admin))
            {
                if($key_user["from_user_id"]==$info_admin[$i]["to_user_id"])
                {
                    $list_admin[$i]=1;
                    $list_users[$x]=1;
                    if (strtotime($key_user["created_at"]) < strtotime($info_admin[$i]["created_at"]))
                    {
                        $reply_time = $info_admin[$i]["created_at"];
                        $reply = "true";
                    }
                    else
                    {
                        $reply_time = $key_user["created_at"];
                        $reply = "false";
                    }
                    array_push($datas,[
                        "team" =>$key_user["department_name"],
                        "reply" => $reply,
                        "from" => $key_user["from_user_name"],
                        "reply_time" => $reply_time,
                    ]);
                }
                $i++;
            }
            $x++;
        }
        for($i=0; $i < count($list_admin); $i++)
        {
            if($list_admin[$i] == 0)
            {
                array_push($datas,[
                    "team" =>$info_admin[$i]["department_name"],
                    "reply" => "false",
                    "from" => $info_admin[$i]["from_user_name"],
                    "reply_time" => $info_admin[$i]["created_at"],
                ]);
            }
        }
        for($i=0; $i < count($list_users); $i++)
        {
            if($list_users[$i] == 0)
            {
                array_push($datas,[
                    "team" =>$info_users[$i]["department_name"],
                    "reply" => "false",
                    "from" => $info_users[$i]["from_user_name"],
                    "reply_time" => $info_users[$i]["created_at"],
                ]);
            }
        }
        return $datas;
    }
    public function disposeData($array)//处理数据（取首条数据）
    {
        $datas = [];
        foreach ($array as $key)
        {
            array_push($datas,$key[0]);
        }
        return $datas;
    }
    public function paginator($request,$datas)//分页
    {
        $perPage = 10;
        if ($request->has('page')) {
            $current_page = $request->input('page');
            $current_page = $current_page <= 0 ? 1 : $current_page;
        } else {
            $current_page = 1;
        }
        $item = array_slice($datas, ($current_page - 1) * $perPage, $perPage); //$Array为要分页的数组
        $totals = count($datas);
        $paginator = new LengthAwarePaginator($item, $totals, $perPage, $current_page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        return $paginator;
    }
    public function getMessage_search(Search $request)
    {
        try
        {
            $datas = [];
            $department_name = $request->input("department_name");
            $info_admin = chat_records::select("users.department_name", "chat_records.*")->join("users", function ($join) {
                $join->on('users.id', '=', 'chat_records.to_user_id')->where("users.id",'<>',Auth::id());
            })->where("department_name",$department_name)->where("from_user_id", "=", 1)->orderBy('created_at', 'desc')->get()->groupBy("to_user_id")->toArray();//管理员发送消息分组
            $info_users = chat_records::select("users.department_name", "chat_records.*")->join("users", function ($join) {
                $join->on('users.id', '=', 'chat_records.from_user_id')->where("users.id",'<>',Auth::id());
            })->where("to_user_id", "=", Auth::id())->where("department_name",$department_name)->orderBy('created_at', 'desc')->get()->groupBy("from_user_id")->toArray();//用户发送信息分组，最新信息在顶部
           // dd($info_users);
            $datas=$this->compara_1($this->disposeData($info_admin),$this->disposeData($info_users));
            $paginator = $this->paginator($request,$datas);//分页
            return response()->json(["code" => 200, "msg" => "success", "data" => $paginator]);
        }catch (\Exception $exception)
        {
            Logs::logError("获取数据失败",[$exception->getMessage()]);
            return response()->json(["code"=>100,"msg"=>'获取信息失败',"data"=>[]],100);
        }
    }


}
