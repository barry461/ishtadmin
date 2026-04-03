<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class OptionsModel
 *
 * @property string $name
 * @property int $user
 * @property string $value
 * @author xiongba
 * @date 2022-10-21 07:12:24
 * @mixin \Eloquent
 */
class OptionsModel extends Model
{
    protected $keyType = 'string';

    protected $table = "options";

    protected $primaryKey = 'name';

    protected $fillable = ['user', 'value', 'name'];

    protected $guarded = 'name';

    public $timestamps = false;


    public static function getPhototech()
    {
        return array_fill(0, 5, '');
    }
}
