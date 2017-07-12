<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Img_upload extends Model
{
    protected $size = 1048576;
    protected $filetype = array('image/jpeg', 'image/png', 'image/jpg', 'image/gif');
    protected $system;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @param $file  上传的文件
     * @param $path 上传路径
     * @return bool|string
     */
    public function img_upload($file,$path)
    {
//		 print_r($file);die();
        if ($file)
        {
            if($file -> isValid())
            {
                $clientName = $file -> getClientOriginalName();
                $entension = $file -> getClientOriginalExtension();
                $mimeTye = $file -> getMimeType();
                $filesize = $file-> getSize();
                $newName = md5(time()).rand(1,999).".".$entension;
                if(!in_array($mimeTye, $this->filetype))
                {
                    echo "文件类型不符"; die;
                }
                if($filesize > $this->size)
                {
                    echo "文件过大"; die;
                }
                // echo public_path();
                $info = $file-> move(public_path().'/'.$path, $newName);
                $res[] = $newName;
                $res[] =  $path.'/'.$newName;
                return $res;
            }
        }
        return false;
    }

    /**
     * 对象去反斜杠
     * @param $data
     */
    public function setData($data) {
        if (!empty($data)) {
            return json_decode(stripslashes($data));
        }
    }
    /**
     * @param $src 原图路径
     * @param $dst  要保存的路径
     * @param $type 图片类型
     * @param $data 对象数据 必须先去反斜杠{x:117.36263736263737,y:62.41758241758243,height:620.2197802197802,width:620.2197802197802,rotate: 1}
     * @param int $w 需求宽度
     * @param int $h 需求高度
     */
    public function crop($src, $dst, $type, $data, $w = 200, $h =200) {
        if (!empty($src) && !empty($dst) && !empty($data)) {
            switch ($type) {
                case 'image/gif':
                    $src_img = imagecreatefromgif($src);
                    break;
                case 'image/jpeg':
                    $src_img = imagecreatefromjpeg($src);
                    break;
                case 'image/png':
                    $src_img = imagecreatefrompng($src);
                    break;
            }
            if (!$src_img) {
                return;
            }

            $size = getimagesize($src);
            $size_w = $size[0]; // natural width 原始的
            $size_h = $size[1]; // natural height

            $src_img_w = $size_w;
            $src_img_h = $size_h;

            $degrees = $data -> rotate;

            // Rotate the source image
            if (is_numeric($degrees) && $degrees != 0) {
                // PHP's degrees is opposite to CSS's degrees
                $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

                imagedestroy($src_img);
                $src_img = $new_img;

                $deg = abs($degrees) % 180;
                $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

                $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
                $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

                // Fix rotated image miss 1px issue when degrees < 0
                $src_img_w -= 1;
                $src_img_h -= 1;
            }

            $tmp_img_w = $data -> width;
            $tmp_img_h = $data -> height;
            $dst_img_w = $w;
            $dst_img_h = $h;

            $src_x = $data -> x;
            $src_y = $data -> y;

            if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
                $src_x = $src_w = $dst_x = $dst_w = 0;
            } else if ($src_x <= 0) {
                $dst_x = -$src_x;
                $src_x = 0;
                $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
            } else if ($src_x <= $src_img_w) {
                $dst_x = 0;
                $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
            }

            if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
                $src_y = $src_h = $dst_y = $dst_h = 0;
            } else if ($src_y <= 0) {
                $dst_y = -$src_y;
                $src_y = 0;
                $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
            } else if ($src_y <= $src_img_h) {
                $dst_y = 0;
                $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
            }
            // Scale to destination position and size
            $ratio = $tmp_img_w / $dst_img_w;
            $dst_x /= $ratio;
            $dst_y /= $ratio;
            $dst_w /= $ratio;
            $dst_h /= $ratio;
            $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);
            // Add transparent background to destination image
            imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
            imagesavealpha($dst_img, true);
            imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
            imagepng($dst_img, $dst);
            imagedestroy($src_img);
            imagedestroy($dst_img);
        }
    }

}