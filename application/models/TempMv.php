<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class TempMvModel
 *
 * @property int $id
 * @property string $cid
 * @property int $ucid 用户发布的视频id
 * @property int $status
 * @property string $url
 * @property string $m3u8
 * @property string $cover
 * @property int $duration
 *
 * @mixin \Eloquent
 */
class TempMvModel extends Model
{
    protected $table = 'temp_mv';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'cid','ucid', 'status', 'url', 'm3u8', 'cover', 'duration'];
    protected $guarded = 'id';
    public $timestamps = false;
    const STATUS_INIT = 0;
    const STATUS_SLICE = 1;

    public static function makeAndSlice(array $ary, UserContentsModel $userContent, ContentsModel $content)
    {
        foreach ($ary[1] as $url) {
            if (strpos($url , '.mp4')){
                $object = TempMvModel::make();
                $object->cid = $content->cid;
                $object->ucid = $userContent->id;
                $object->url = $url;
                $object->cover = '';
                $object->status = TempMvModel::STATUS_INIT;
                $object->duration = 0;
                $object->save();
            }
        }
        $cid = $content->cid;
        $aff = $userContent->aff;
//        jobs(function () use ($ary, $aff, $cid) {
            /** @var array<TempMvModel> $list */
            $list = TempMvModel::useWritePdo()->where('cid',$cid)->get();
            foreach ($list as $object){
                $data = [
                    'uuid'    => $aff,
                    'm_id'    => $object->id,
                    'playUrl' => $object->url,
                    'needMp3' => 0,
                    'needImg' => 1,
                ];
                \tools\mp4Upload::accept($data, 'mv_callback');
            }
//        });
    }


}