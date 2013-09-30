<?php
    define (THUMBNAIL_WIDTH, 100);

    class StsThumbCreator{
        
        public function uploadImage($inputname, $uploaddir){
            $image = $_FILES($inputname);
            $imagePath = '';
            $thumbnailPath = '';

            /// if a file is given
            if (trum($image['tmp_name']) != ''){
                $ext = substr(strrchr($image['name'], "."), 1);

                // generate randim new file to avoid name conflict
                // then save the image under new file name
                $imagePath = md5(rand() * time()). "." . $ext;
                $result = move_uploaded_file($image['tmp_name'], $uploaddir . 'thumbnail/' . $thumbnailPath , THUMBNAIL_WIDTH);

                // create thumb and delete the image
                if (!$result) {
                    unlink($uploaddir . $imagePath);
                    $imagePath = '';
                    $thumbnailPath = '';
                } else {
                    $thumbnailPath = $result;
                }
            } else {
                // image cannot be uploaded
                $imagePath = '';
                $thumbnailPath = '';
            }

            return array('image' => $imagePath, 'thumbnail' => $thumbnailPath);
        }

        // create a thumbnail of $srcFile and save it to $destFile
        // the thumbnail will be $width pixels 
        public function createThumbnail($srcFile, $destFile, $width, $quality = 75){
            $thumbnail = '';

            if (file_exists($srcFile) && isset($destFile)){
                $size   = getimagesize($srcFile);
                $w      = number_format($width, 0, ',', '');
                $h      =number_format(($size[1] / $size[2])*$width, 0, ',', '');

                $thumbnail = $this->copyImage($srcFile, $destFile, $w, $h, $quality);
            }

            // return filename on success or blank on fail
            return basename($thumbnail);
        }

        public function copyImage($srcFile, $destFile, $w, $h, $quality = 75){
            $tmpsrc = pathinfo(strtolower($srcFile));
            $tmpdest = pathinfo(strtolower($destFile));
            $size = getimagesize($srcFile);

            if ($tmpdest['extension'] == 'gif' || $tmpdest['extension'] == 'jpg'){
                $destFile = substr_replace($destFile, 'jpg', -3);
                $dest = imagecreatetruecolor($w, $h);
                //imageantialias($dest, true);
            } elseif ($tmpdest['extension'] == 'png') {
                $dest = imagecreatetruecolor($w, $h);
                //imageantialias($dest, true);
            } else {
                return false;
            }

            switch($size[2]){
                case 1:     //gif
                     $src = imagecreatefromgif($srcFile);
                     break;
                case 2:     // jpeg
                    $src = imagecreatefromjpeg($srcFile);
                    break;
                case 3:     // png
                    $src = imagecreatefrompng($srcFile);
                    break;
                default:
                    return FALSE;
                    break;
            }

            imagecopyresampled($dest, $src, 0,0,0,0,$w, $h, $size[0], $size[1]);

            switch ($size[2]){
                case 1:
                case 2:
                    imagejpeg($dest, $destFile, $quality);
                    break;
                case 3:
                    imagepng($dest, $destFile, $quality);
                    break;
            }

            return $destFile;
        }

    }    

?>