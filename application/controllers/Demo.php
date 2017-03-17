<?php

use Yaf\Registry as R;

class DemoController extends \kongfz\Controller {

    public function init() {
        parent::init();
    }

    /**
     * 图片上传示例
     * 
     * 前端调用 url:http://newpc.kfz.com/demo/upload
     * 返回值: {"status":true|flase,"data":[],"error":""}
     * 成功上传的图片以数组的方式返回
     */
    public function uploadAction() {
        $status = false;
        $data   = [];
        $error  = '';
        $site   = R::get('g_config')->site->toArray();
        $webdav = new \kongfz\WebDav($site['dav'] . 'newpc');
        $files  = $this->_request->getFiles();
        if (!empty($files)) {
            foreach ($files as $file) {
                $md5 = md5_file($file['tmp_name']);
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if ($ext) {
                    $ext = '.' . $ext;
                } else {
                    $ext = '.jpg';
                }
                $filename = "{$md5[0]}{$md5[1]}/{$md5[2]}{$md5[3]}/{$md5}" . $ext;
                $status   = $webdav->put($file['tmp_name'], $filename);
                $data    = [
                    'name'   => $file['name'],
                    'status' => $status
                ];
                if ($status) {
                    $data['prefix'] = $site['dav'] . 'newpc/';
                    $data['imgurl'] = $filename;
                } else {
                    $error = '图片上传失败';
                }
                //删除临时图片
                unlink($file['tmp_name']);
                break;
            }
        } else {
            $query = $this->_request->getPost();
            if (empty($query['imgurl'])) {
                $error = '图片路径不能为空，请重试！';
                exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
            }
            if (!preg_match('#^http(s?)|^/#', $query['imgurl'])) {
                $error = '图片路径格式不正确，应该以http或https开头，请重试！';
                exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
            }
            $imgBuffer = file_get_contents($query['imgurl']);
            if (!$imgBuffer) {
                $error = '原图片获取失败，请稍后重试！';
                exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
            }
            $tmp_file = '/tmp/img_' . md5($imgBuffer);
            if (!file_put_contents($tmp_file, $imgBuffer)) {
                $error = '临时图片保存失败，请稍后重试！';
                exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
            }
            $md5 = md5_file($tmp_file);
            $ext = pathinfo($query['imgurl'], PATHINFO_EXTENSION);
            if ($ext) {
                $ext = '.' . $ext;
            } else {
                $ext = '.jpg';
            }
            $filename = "{$md5[0]}{$md5[1]}/{$md5[2]}{$md5[3]}/{$md5}" . $ext;
            $status   = $webdav->put($tmp_file, $filename);
            $data    = [
                'name'   => $query['imgurl'],
                'status' => $status,
            ];
            if ($status) {
                $data['prefix'] = $site['dav'] . 'newpc/';
                $data['imgurl'] = $filename;
            } else {
                $error = '图片上传失败';
            }
            //删除临时图片
            unlink($tmp_file);
        }
        exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
    }

    /**
     * 图片裁剪示例
     * 
     * 前端调用 url:http://newpc.kfz.com/demo/crop
     * 
     * 参数：
     *      imgurl:图片的相对地址
     *      w:图片裁剪区的宽
     *      h:图片裁剪区的高
     *      x:图片裁剪区的起点x坐标
     *      y:图片裁剪区的起点y坐标
     *      dst_x:裁剪后要保存的图片宽度(可选参数)
     * 
     * 返回值: {"status":true|flase,"data":[],"error":""}
     * 成功上传的图片以数组的方式返回
     */
    public function cropAction() {
        $status = false;
        $data   = [];
        $error  = '';
        $query  = $this->_request->getPost();
        if (empty($query['imgurl'])) {
            $error = '图片保存失败（原图片路径不能为空），请稍后重试！';
            exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
        }
        $src_w = $query['w'];
        $src_h = $query['h'];
        $src_x = $query['x'];
        $src_y = $query['y'];
        $dst_w = isset($query['dst_w']) ? $query['dst_w'] : 0;
        if (empty($dst_w)) {
            if ($src_w / $src_h > 1) {
                $dst_w   = $src_w > 1600 ? 1600 : $src_w;
                $sdScale = $dst_w / $src_w;
                $dst_h   = $src_h * $sdScale;
            } else {
                $dst_h   = $src_h > 1600 ? 1600 : $src_h;
                $sdScale = $dst_h / $src_h;
                $dst_w   = $src_w * $sdScale;
            }
        } else {
            $sdScale = $dst_w / $src_w;
            $dst_h   = $src_h * $sdScale;
        }

        $jpeg_quality = 90;
        $site         = R::get('g_config')->site->toArray();
        $webdav       = new \kongfz\WebDav($site['dav'] . 'newpc');
        $imgBuffer    = $webdav->get($query['imgurl']);
        if (!$imgBuffer) {
            $error = '原图片获取失败，请稍后重试！';
            exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
        }
        $tmp_file = '/tmp/img_' . md5($imgBuffer);
        if (!file_put_contents($tmp_file, $imgBuffer)) {
            $error = '临时图片保存失败，请稍后重试！';
            exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
        }
        $status = \kongfz\Image::crop($tmp_file, $src_w, $src_h, $src_x, $src_y, $jpeg_quality, $tmp_file, $dst_w, $dst_h);
        if (!$status) {
            $error = '裁剪的图片保存失败，请稍后重试！';
            exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
        }
        $status = $webdav->put($tmp_file, $query['imgurl']);
        if (!$status) {
            $error = '裁剪的图片上传失败，请稍后重试！';
            exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
        }
        //删除临时图片
        unlink($tmp_file);
        $data['prefix'] = $site['dav'] . 'newpc/';
        $data['imgurl'] = $query['imgurl'];
        exit(json_encode(['status' => $status, 'data' => $data, 'error' => $error]));
    }

}
