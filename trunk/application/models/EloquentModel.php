<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model 基础类
 * @package App\Models
 * @author chenchen16@leju.com
 * @date 2015/11/27
 */
class EloquentModel extends Model
{
    /** @var bool 默认情况下，Eloquent 会预计你的数据表中有 created_at 和 updated_at 字段。如果你不希望让 Eloquent 来自动维护这两个字段，可在模型内将 $timestamps 属性设置为 false */
    public $timestamps = false;

    /**
     * 执行sql查询
     * @param   string $sql
     * @return  array
     * @author  tinglei
     */
    public function getDataBySql($sql = '')
    {
        $return = array();

        $res = \DB::select($sql);
        if (!empty($res)) {
            $return = to_array($res);
        }

        return $return;
    }


    /**
     * 禁止克隆
     */
    private function __clone()
    {

    }
}