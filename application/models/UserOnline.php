<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class UserOnlineModel
 * 
 * 
 * @property int $id  
 * @property string $date  
 * @property int $t0  
 * @property int $t1  
 * @property int $t2  
 * @property int $t3  
 * @property int $t4  
 * @property int $t5  
 * @property int $t6  
 * @property int $t7  
 * @property int $t8  
 * @property int $t9  
 * @property int $t10  
 * @property int $t11  
 * @property int $t12  
 * @property int $t13  
 * @property int $t14  
 * @property int $t15  
 * @property int $t16  
 * @property int $t17  
 * @property int $t18  
 * @property int $t19  
 * @property int $t20  
 * @property int $t21  
 * @property int $t22  
 * @property int $t23  
 * 
 * 
 *
 * @mixin \Eloquent
 */
class UserOnlineModel extends Model
{
    protected $table = 'user_online';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'date', 't0', 't1', 't2', 't3', 't4', 't5', 't6', 't7', 't8', 't9', 't10', 't11', 't12', 't13', 't14', 't15', 't16', 't17', 't18', 't19', 't20', 't21', 't22', 't23'];
    protected $guarded = 'id';
    public $timestamps = false;
}