<?php
/*
 * ����ˮӡͼƬ.��
 */
class Watermark {

    var $input_image_file = "";        //����ͼƬ���ļ���  
    var $output_image_file = "";       //�����ļ�������  
    var $logo_image_name = "";         //�������·����ˮӡͼƬ���ļ���  
    var $logo_text = "";               //ˮӡ����  
    var $logo_text_size = 18;          //ˮӡ���ִ�С  
    var $logo_text_angle = 4;          //ˮӡ���ֽǶ�  
    var $logo_text_pos = 6;            //ˮӡ���ַ���λ��  
    var $logo_text_font = "";          //ˮӡ���ֵ�����  
    var $logo_text_color = "#ffffff"; //ˮӡ�������ɫֵ  
    var $logo_image_pos = 6;           //ˮӡͼƬ���õ�λ��  
    //1 = ��������2 = �������У�3 = �������ң�4 = �ײ�����  
    //5 = �ײ����У�6 = �ײ����ң�7 = �м����8 = ���У�9 = �м����  
    var $logo_image_transition = 25;   //͸��������,ˮӡͼƬ��ԭͼƬ���ں϶�(1=100)  
    var $jpeg_quality = 75;            //jpegͼƬ������  

//����ˮӡͼƬ  

    function create($filename="") {
        if ($filename)
            $this->input_image_file = strtolower(trim($filename));
        $src_image_type = $this->get_type($this->input_image_file);
        $src_image = $this->createImage($src_image_type, $this->input_image_file);
        if (!$src_image)
            return;
        $src_image_w = imagesx($src_image);
        $src_image_h = imagesy($src_image);
        //��ʼ����ˮӡlogoͼƬ��Ϣ��������ͼƬ�ϳ�Ϊһ��ͼƬ  
        if ($this->logo_image_name) {
            $this->logo_image_name = strtolower(trim($this->logo_image_name));
            $logo_image_type = $this->get_type($this->logo_image_name);
            $logo_image = $this->createImage($logo_image_type, $this->logo_image_name);
            $logo_image_w = imagesx($logo_image);
            $logo_image_h = imagesy($logo_image);
            $temp_logo_image = $this->getPos($src_image_w, $src_image_h, $this->logo_image_pos, $logo_image);
            $logo_image_x = $temp_logo_image["dest_x"];
            $logo_image_y = $temp_logo_image["dest_y"];
            imagecopymerge($src_image, $logo_image, $logo_image_x, $logo_image_y, 0, 0, $logo_image_w, $logo_image_h, $this->logo_image_transition);
        }
        //ˮӡΪ���ı�  
        if ($this->logo_text) {
            //ָ��������ת��ΪUTF-8��ʽ��ʹ��gb2312��֤���ĵ�������ʾ
            //$this->logo_text = iconv('GB2312', 'UTF-8', $this->logo_text);
            $temp_logo_text = $this->getPos($src_image_w, $src_image_h, $this->logo_text_pos);
            $logo_text_x = $temp_logo_text["dest_x"];
            $logo_text_y = $temp_logo_text["dest_y"];
            if (preg_match("/([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])([a-f0-9][a-f0-9])/i", $this->logo_text_color, $color)) {
                $red = hexdec($color[1]);
                $green = hexdec($color[2]);
                $blue = hexdec($color[3]);
                $logo_text_color = imagecolorallocate($src_image, $red, $green, $blue);
            } else {
                $logo_text_color = imagecolorallocate($src_image, 255, 255, 255);
            }
            //��TrueType������ͼ��д���ı�  
            imagettftext($src_image, $this->logo_text_size, $this->logo_angle, $logo_text_x, $logo_text_y, $logo_text_color, $this->logo_text_font, $this->logo_text);
        }
        //�������ɵ�ͼƬΪ�µ��ļ�  
        if ($this->output_image_file) {
            switch ($this->get_type($this->output_image_file)) {
                case 'gif':$src_img = imagegif($src_image, $this->output_image_file);
                    break;
                case 'jpeg':$src_img = imagejpeg($src_image, $this->output_image_file, $this->jpeg_quality);
                    break;
                case 'png':$src_img = imagepng($src_image, $this->output_image_file);
                    break;
                default:$src_img = imagejpeg($src_image, $this->output_image_file, $this->jpeg_quality);
                    break;
            }
        } else { //��ԭ��ͼƬ�Ļ����������µĺϳ�ͼƬ  
            if ($src_image_type = "jpg")
                $src_image_type = "jpeg";
            header("Content-type: image/{$src_image_type}");
            switch ($src_image_type) {
                case 'gif':$src_img = imagepng($src_image);
                    break;
                case 'jpg':$src_img = imagejpeg($src_image, "", $this->jpeg_quality);
                    break;
                case 'png':$src_img = imagepng($src_image);
                    break;
                default:$src_img = imagejpeg($src_image, "", $this->jpeg_quality);
                    break;
            }
        }
        imagedestroy($src_image);
    }

//�����ļ��������ʹ���ͼƬ  
    function createImage($type, $img_name) {
        if (!$type) {
            $type = $this->get_type($img_name);
        }
        switch ($type) {
            case 'gif':
                if (function_exists('imagecreatefromgif'))
                    $tmp_img = @imagecreatefromgif($img_name);
                break;
            case 'jpg':
                $tmp_img = imagecreatefromjpeg($img_name);
                break;
            case 'png':
                $tmp_img = imagecreatefrompng($img_name);
                break;
            default:
                $tmp_img = imagecreatefromstring($img_name);
                break;
        }
        return $tmp_img;
    }

//����Դͼ��ĳ�����λ�ô��룬ˮӡͼƬid�����ɰ�ˮӡ���õ�Դͼ���е�λ��  
    function getPos($sourcefile_width, $sourcefile_height, $pos, $logo_image="") {
        if ($logo_image) {
            $insertfile_width = imagesx($logo_image);
            $insertfile_height = imagesy($logo_image);
        } else {
            $lineCount = explode("\r\n", $this->logo_text);
            $fontSize = imagettfbbox($this->logo_text_size, $this->logo_text_angle, $this->logo_text_font, $this->logo_text);
            $insertfile_width = $fontSize[2] - $fontSize[0];
            $insertfile_height = count($lineCount) * ($fontSize[1] - $fontSize[3]);
        }
        switch ($pos) {
            case 1://��������  
                $dest_x = 0;
                if ($this->logo_text) {
                    $dest_y = $insertfile_height;
                } else {
                    $dest_y = 0;
                }
                break;
            case 2://��������  
                $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
                if ($this->logo_text) {
                    $dest_y = $insertfile_height;
                } else {
                    $dest_y = 0;
                }
                break;
            case 3://��������  
                $dest_x = $sourcefile_width - $insertfile_width;
                if ($this->logo_text) {
                    $dest_y = $insertfile_height;
                } else {
                    $dest_y = 0;
                }
                break;
            case 4://�ײ�����  
                $dest_x = 0;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 5://�ײ�����  
                $dest_x = ( ( $sourcefile_width - $insertfile_width ) / 2 );
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 6://�ײ�����  
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
            case 7://�м����  
                $dest_x = 0;
                $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
                break;
            case 8://����  
                $dest_x = ( $sourcefile_width / 2 ) - ( $insertfile_width / 2 );
                $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
                break;
            case 9://�м����  
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = ( $sourcefile_height / 2 ) - ( $insertfile_height / 2 );
                break;
            default://�ײ�����  
                $dest_x = $sourcefile_width - $insertfile_width;
                $dest_y = $sourcefile_height - $insertfile_height;
                break;
        }
        return array("dest_x" => $dest_x, "dest_y" => $dest_y);
    }

//��ȡͼƬ�ĸ�ʽ����Ҫ����jpg,png��gif  
    function get_type($img_name) {
        $name_array = explode(".", $img_name);
        if (preg_match("/\.(gif|jpg|jpeg|png)$/", $img_name, $matches)) {
            $type = strtolower($matches[1]);
        } else {
            $type = "string";
        }
        return $type;
    }

}

/*
//$imglogo = new Watermark();
//$imglogo->logo_text = "��Һ�,phpbook";
//$imglogo->logo_text_font = "./simsun.ttc"; //����  
//$imglogo->create("upload/2011/11/2011112303191370.jpg");



//include_once("Watermark.class.php");  
$imglogo = new Watermark(); 
$imglogo->logo_image_name = 'logo.jpg';//ˮӡͼƬ
$imglogo->output_image_file ="./result.jpg";//����ˮӡ���ͼƬ
$imglogo->input_image_file = "upload/2011/11/2011112303191370.jpg";//ԭͼƬ;
$imglogo->create();  
 */
