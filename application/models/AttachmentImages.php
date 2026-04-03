<?php

/**
 * class AttachmentdModel
 *
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $name 名称
 * @property string $progress_rate 进度
 * @property int $upload_type 0 普通 1分段
 * @property int $upload_status 0未上传 1上传完成
 * @property int $slice_status 0未切片 1切片中 2切片完成
 * @property string $cover 封面
 * @property string $mp4_url Mp4
 * @property string $m3u8_url M3u8 地址
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class AttachmentImagesModel extends BaseModel
{
    protected $table = 'attachment_images';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'name',
        'upload_status',
        'slice_status',
        'image_url',
        'created_at',
        'updated_at',
        'cid'
    ];
    protected $guarded = 'id';
    public $timestamps = false;


    public function getCoverAttribute()
    {
        if ($this->attributes['image_url']) {
            return url_image($this->attributes['image_url']);
        }
        return '';
    }


    public function getRawImageUrlAttribute()
    {
        return $this->attributes['image_url'] ?? '';
    }

    /**
     * 获取图片预览URL（使用 CDN 处理后的地址）
     */
    public function getPreviewUrlAttribute(): string
    {
        $imageUrl = $this->attributes['image_url'] ?? '';
        if (empty($imageUrl)) {
            return '';
        }
        return url_image($imageUrl);
    }
}