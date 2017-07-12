<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Ftp_upload extends Model
{
    protected $host = 'img.mxyes.com';
    protected $user = 'img';
    protected $pass = '1q3e2w4r-';

    /**
     * @param $destDir  ftp路径  2017/06/12
     * @param $newName  ftp文件名称
     * @param $file   本地文件全路径
     * @return bool|string
     */
    public function ftp_upload($destDir,$newName,$file)
    {
        $conn = ftp_connect($this->host) or die ("Cannot initiate connection to host");
        ftp_login($conn, $this->user, $this->pass) or die("Cannot login");
        $arr = explode('/',$destDir);
        foreach ($arr as $v)
        {
            if(!@ftp_chdir($conn, $v)){
                ftp_mkdir($conn, $v);
                ftp_chmod($conn , 0777, $v);
                ftp_chdir($conn, $v);
            }
        }
        $upload = ftp_put($conn, $newName, $file, FTP_BINARY);
        ftp_close($conn);
        if (!$upload) {
            return false;
        } else {
            return 'http://'.$this->host.'/'.$destDir.'/'.$newName;
        }
    }

    public function ftp_del($file)
    {
        $file = str_replace('http://','',$file);
        $file = substr($file,strpos($file,'/'));
        $conn = ftp_connect($this->host) or die ("Cannot initiate connection to host");
        ftp_login($conn, $this->user, $this->pass) or die("Cannot login");
        if (ftp_size($conn,ftp_pwd($conn).$file) == -1) return true;
        $del = ftp_delete($conn,ftp_pwd($conn).$file);
        ftp_close($conn);
        return $del;
    }

}