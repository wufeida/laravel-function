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

}