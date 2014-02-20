<?php
//路径可以修改为自动获取
$rootpath=$_SERVER['DOCUMENT_ROOT'];
$domain_static='static.jiada.local';
function getImageInfo( $img ){
	$imageInfo = getimagesize($img);
	if( $imageInfo!== false) {
		$imageType = strtolower(substr(image_type_to_extension($imageInfo[2]),1));
		$info = array(
				"width"		=>$imageInfo[0],
				"height"	=>$imageInfo[1],
				"type"		=>$imageType,
				"mime"		=>$imageInfo['mime'],
		);
		return $info;
	}else {
		return false;
	}
}

function resize( $ori ){
	if( preg_match('/^http:\/\/[a-zA-Z0-9]+/', $ori ) ){
		return $ori;
	}
	$info = getImageInfo( ROOT_PATH . $ori );
	if( $info ){
        //上传图片后切割的最大宽度和高度
		$width = 500;
		$height = 500;
		$scrimg = ROOT_PATH . $ori;
        if( $info['type']=='jpg' || $info['type']=='jpeg' ){
            $im = imagecreatefromjpeg( $scrimg );
        }
		if( $info['type']=='gif' ){
			$im = imagecreatefromgif( $scrimg );
		}
		if( $info['type']=='png' ){
			$im = imagecreatefrompng( $scrimg );
		}
		if( $info['width']<=$width && $info['height']<=$height ){
			return;
		} else {
			if( $info['width'] > $info['height'] ){
				$height = intval( $info['height']/($info['width']/$width) );
			} else {
				$width = intval( $info['width']/($info['height']/$height) );
			}
		}
		$newimg = imagecreatetruecolor( $width, $height );
		imagecopyresampled( $newimg, $im, 0, 0, 0, 0, $width, $height, $info['width'], $info['height'] );
		imagejpeg( $newimg, ROOT_PATH . $ori );
		imagedestroy( $im );
	}
	return;
}

if (!empty($_FILES)) {
    $ext = pathinfo($_FILES['Filedata']['name']);
    $ext = strtolower($ext['extension']);
    $tempFile = $_FILES['Filedata']['tmp_name'];
    $targetPath   = 'uploads/';
    if( !is_dir($targetPath) ){
        mkdir($targetPath,0777,true);
    }
    $new_file_name = 'avatar_ori.'.$ext;
    $targetFile = $targetPath . $new_file_name;
    move_uploaded_file($tempFile,$targetFile);
    if( !file_exists( $targetFile ) ){
        $ret['result_code'] = 0;
        $ret['result_des'] = 'upload failure';
    } elseif( !$imginfo=getImageInfo($targetFile) ) {
        $ret['result_code'] = 101;
        $ret['result_des'] = 'File is not exist';
    } else {
        $img = 'uploads/'.$new_file_name;
        resize($img);
        $ret['result_code'] = 1;
        $ret['result_des'] = 'http://'.$domain_static.'/plugin/uploadify/'.$img;
    }
} else {
    $ret['result_code'] = 100;
    $ret['result_des'] = 'No File Given';
}
exit( json_encode( $ret ) );