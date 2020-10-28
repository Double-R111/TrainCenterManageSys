<?php

namespace App\Models;

use App\Http\Requests\ShowAllRequest;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Models\LaboratoryLoan;
use App\Models\Laboratory;
use App\Models\Clas;

class Form extends Model
{
    protected $table = "form";
    public $timestamps = true;
    protected $guarded = [];
    public $primaryKey="form_id";


    /**
     * 获取表单种类
     * @author Dujingwen <github.com/DJWKK>
     * @param $form_id
     * @return json
     *
     */
    public static function findType($form_id){
        try{
            $data = self::where('form_id',$form_id)
                ->select('type_id')
                ->get();
            return $data[0]->type_id;
        }catch(\Exception $e){
            logError('获取表单'.$form_id.'种类失败',[$e->getMessage()]);
            return null;
        }
    }

    /**
     * 得到所有表单展示数据
     * @param $request
     * @return false
     * @author yangsiqi <github.com/Double-R111>
     */
    public static function ysq_getAll($code)
    {
        try {
            $info = getDinginfo($code);
            $role = $info->role;
            $name = $info->name;
            if ($role == "借用部门负责人") {
                $lev = 1;
            } elseif ($role == "实验室借用管理员") {
                $lev = 3;
            } elseif ($role == "实验室中心主任") {
                $lev = 5;
            } elseif ($role == "设备管理员") {
                $lev = 7;
            }
            $data = Form::join('form_type', 'form.type_id', 'form_type.type_id')
                        ->join('form_status', 'form.form_status', 'form_status.status_id')
                        ->select('form.form_id', 'form.applicant_name', 'form_status.status_name', 'form_type.type_name')
                        ->where('form.form_status', '=', $lev)
                        ->where('form.applicant_name', '!=', $name)
                        ->orderby('form.created_at', 'desc')
                        ->get();
            return $data ? $data : false;
        } catch (\Exception $e) {
            logError('表单信息展示错误', [$e->getMessage()]);
             return false;

        }
    }



    /**
     * 获取表单状态
     * @author Dujingwen <github.com/DJWKK>
     * @param $form_id
     * @return json
     */
    public static function findStatus($form_id){
        try{
            $data = self::where('form_id',$form_id)
                ->select('form_status')
                ->get();
            return $data[0]->form_status;
        }catch(\Exception $e){
            logError('获取表单'.$form_id.'状态失败',[$e->getMessage()]);
            return null;
         }
    }

    /**
     * 展示所有待审批表单
     * @auther ZhongChun <github.com/RobbEr929>
     * @param $request
     * return [string]
     */
    public static function zc_show($code)
    {
        try {
            $info  = getDinginfo($code);
            $role = $info->role;
            $name = $info->name;
            if($role=="借用部门负责人"){
                $rule = 1;
            }elseif($role=="实验室借用管理员"){
                $rule = 3;
            }elseif($role=="实验室中心主任"){
                $rule = 5;
            }elseif($role=="设备管理员"){
                $rule = 7;
            }
            $res = Form::join('form_type','form.type_id','form_type.type_id')
                ->select('form.form_id','form.applicant_name','form_type.type_name')
                ->where('form.applicant_name','!=',$name)
                ->where('form.form_status','=',$rule)
                ->orderBy('form.created_at','desc')
                ->get();
            return $res?
                $res:
                false;
        } catch (\Exception $e) {
            logError('搜索错误', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 审核通过 更新表单状态（5之前的状态）
     * @author Dujingwen <github.com/DJWKK>
     * @param $form_status
     * @return json
     */
    public static function updatedStatus($role,$form_id,$form_status){
        try{
            if ($form_status == 1 && $role == '借用部门负责人'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',2);
                return $data;
            }else if( $form_status == 3 && $role == '实验室借用管理员'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',2);
                return $data;
            }
        }catch(\Exception $e){
            logError('获取表单'.$form_status.'种类失败',[$e->getMessage()]);
            return null;
        }
    }

    /**
     * 审核通过 更新表单状态（5之后的状态）
     * @author Dujingwen <github.com/DJWKK>
     * @param $form_type
     * @param $role
     * @param $form_id
     * @param $form_status
     * @return json
     */
    public static function updatedStatuss($form_type,$role,$form_id,$form_status){
        try{
            if(($form_type == 1 || $form_type ==5)  && $form_status == 5 && $role == '实验室中心主任'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',6);
                return $data;
            }else if($form_type == 3 && $form_status == 5 && $role == '实验室中心主任'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',2);
                return $data;
            }else if($form_type == 3 && ($form_status == 7 || $form_status == 9)&& $role == '设备管理员'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',2);
                return $data;
            }
        }catch(\Exception $e){
            logError('获取表单'.$form_status.'种类失败',[$e->getMessage()]);
            return null;
        }
    }
    /**
     * 审核不通过 更新表单状态（5之前的状态）
     * @author Dujingwen <github.com/DJWKK>
     * @param $role
     * @param $form_id
     * @param $form_status
     * @return json
     */
    public static function noUpdateStatus($role,$form_id,$form_status){
        try{
            if ($form_status == 1 && $role == '借用部门负责人'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',1);
                return $data;
            }else if( $form_status == 3 && $role == '实验室借用管理员'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',1);
                return $data;
            }else if($form_status == 5 && $role == '实验室中心主任'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',1);
                return $data;
            }
        }catch(\Exception $e){
            logError('获取表单'.$form_status.'种类失败',[$e->getMessage()]);
            return null;
        }
    }
    /**
     * 审核不通过 更新表单状态（5之后的状态）
     * @author Dujingwen <github.com/DJWKK>
     * @param $role
     * @param $form_id
     * @param $form_status
     * @return json
     */
    public static function npUpdatedStatuss($role,$form_id,$form_status){
        try{
            if($form_status == 7 && $role == '设备管理员'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',1);
                return $data;
            }else if($form_status == 9 && $role == '设备管理员'){
                $data = self::where('form_id',$form_id)
                    ->increment('form_status',0);
                return $data;
            }
        }catch(\Exception $e){
            logError('获取表单'.$form_status.'种类失败',[$e->getMessage()]);
            return null;
        }
    }

    /* 通过申请人姓名和表单编号模糊查询表单
     * @author yangsiqi <github.com/Double-R111>
     * @param $request
     * @return false
     */
    public static function ysq_Query($infos,$code)
    {
        try {
            $info = getDinginfo($code);
            $role = $info->role;
            $name = $info->name;
            if ($role == '借用部门负责人') {
                $lev = 1;
            } elseif ($role == '实验室借用管理员') {
                $lev = 3;
            } elseif ($role == '实验室中心主任') {
                $lev = 5;
            } elseif ($role == '设备管理员') {
                $lev = 7;
            }

            $data = Form::join('form_type', 'form.type_id', 'form_type.type_id')
                ->join('form_status', 'form.form_status', 'form_status.status_id')
                ->select('form.form_id', 'form.applicant_name', 'form_type.type_name', 'form_status.status_name')
                ->where('form.applicant_name', '!=', $name)
                ->where('form.form_status', '=', $lev)
                ->where('form.form_id', '=', $infos)
                ->orWhere('form.applicant_name', '=', $infos)
                ->orderBy('form.created_at', 'desc')
                ->get();
            return $data ? $data : false;
 } catch (\Exception $e) {
            logError('搜索错误', [$e->getMessage()]);
            return false;
        }
    }
     /* 分类查询待审批表单
     * @auther ZhongChun <github.com/RobbEr929>
     * @param $request
     * return [string]
     */
    public static function zc_classify($code,$type_name)
    {
        try {
            $info  = getDinginfo($code);
            $role = $info->role;
            $name = $info->name;
            if($role=="借用部门负责人"){
                $rule = 1;
            }elseif($role=="实验室借用管理员"){
                $rule = 3;
            }elseif($role=="实验室中心主任"){
                $rule = 5;
            }elseif($role=="设备管理员"){
                $rule = 7;
            }
            $res = Form::join('form_type','form.type_id','form_type.type_id')
                ->select('form.form_id','form.applicant_name','form_type.type_name')
                ->where('form.applicant_name','!=',$name)
                ->where('form.form_status','=',$rule)
                ->where('type_name','=',$type_name)
                ->orderBy('form.created_at','desc')
                ->get();
            return $res?
                $res:
                false;

        } catch (\Exception $e) {
            logError('搜索错误', [$e->getMessage()]);
            return false;
        }
    }

    /**

     * 通过类别查询表单详情
     * @param $request
     * @return false
     * @author yangsiqi <github.com/Double-R111>
     */
    public static function ysq_searchType($type_name,$code)
    {
        $info = getDinginfo($code);
        $role = $info->role;
        $name = $info->name;
        if ($role == '借用部门负责人') {
            $lev = 1;
        } elseif ($role == '实验室借用管理员') {
            $lev = 3;
        } elseif ($role == '实验室中心主任') {
            $lev = 5;
        } elseif ($role == '设备管理员') {
            $lev = 7;
        }
        try {
            $data = Form::join('form_type', 'form.type_id', 'form_type.type_id')
                ->join('form_status', 'form.form_status', 'form_status.status_id')
                ->select('form.applicant_name', 'form.form_id', 'form_type.type_name', 'form_status.status_name')
                ->where('form.form_status', '=', $lev)
                ->where('form.applicant_name', '!=', $name)
                ->where('type_name', '=', $type_name)
                ->orderby('form.created_at', 'desc')
                ->get();
            return $data ? $data : false;
        } catch (\Exception $e) {
            logError('类型查询表单错误'[$e->getMessage()]);
          return false;
             }
    }

     /* 分类查询待审批表单
     * @auther ZhongChun <github.com/RobbEr929>
     * @param $request
     * return [string]
     */
    public static function zc_select($code,$data)
    {
        try {
            $info  = getDinginfo($code);
            $role = $info->role;
            $name = $info->name;
            if($role=="借用部门负责人"){
                $rule = 1;
            }elseif($role=="实验室借用管理员"){
                $rule = 3;
            }elseif($role=="实验室中心主任"){
                $rule = 5;
            }elseif($role=="设备管理员"){
                $rule = 7;
            }
            $res = Form::join('form_type','form.type_id','form_type.type_id')
                ->select('form.form_id','form.applicant_name','form_type.type_name')
                ->where('form.applicant_name','!=',$name)
                ->where('form.form_status','=',$rule)
                ->where('form.form_id','=',$data)
                ->orWhere('form.applicant_name','=',$data)
                ->orderBy('form.created_at','desc')
                ->get();
            return $res?
                $res:
                false;
        } catch (\Exception $e) {
            logError('搜索错误', [$e->getMessage()]);
            return false;

        }
    }

    /**
     * 分类查询展示各类表单详情
     * @author yangsiqi <github.com/Double-R111>
     * @param $request
     * @return false
     */
    public static function ysq_reshowAll($form_id)
    {
        try {
            $data = Form::where('form_id', '=', $form_id)
                ->value('type_id');
            return $data ? $data : false;
        } catch (\Exception $e) {
            logError('分类搜索失败', [$e->getMessage()]);
            return false;
        }
    }
    /**
     * 分类查询待审批表单
     * @auther ZhongChun <github.com/RobbEr929>
     * @param $request
     * return [string]
     */
    public static function zc_reShow($form_id)
    {
        try {
            $zc = Form::where('form_id','=',$form_id)
                ->value('type_id');
            return $zc?
                $zc:
                false;
        } catch (\Exception $e) {
            logError('搜索错误', [$e->getMessage()]);
            return false;

        }
    }
}


