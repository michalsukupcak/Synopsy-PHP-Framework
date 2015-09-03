<?php
/*
 * Synopsy PHP Framework (c) by Webdesign Studio s.r.o.
 * 
 * Synopsy PHP Framework is licensed under a
 * Creative Commons Attribution 4.0 International License.
 *
 * You should have received a copy of the license along with this
 * work. If not, see <http://creativecommons.org/licenses/by/4.0/>.
 *
 * Any files in this application that are NOT marked with this disclaimer are
 * not part of the framework's open-source implementation, the CC 4.0 licence
 * does not apply to them and are protected by standard copyright laws!
 */

namespace Synopsy\Files;

use Imagick;
use Synopsy\Common\Lib;
use Synopsy\Exceptions\SynopsyException;
use Synopsy\Exceptions\UploadException;

/**
 * Class handling upload of files to the server.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class Upload {
    
    /**
     * Available file upload types.
     * 
     */
    const UPLOAD_FILE = 1;
    const UPLOAD_IMAGE = 2;
    
    /**
     * Folder to which files will be uploaded.
     *
     * @var String
     */
    private $folder = null;
            
    /**
     * List of files to be uploaded.
     *
     * @var Array 
     */
    private $files = [];
    
    /**
     *
     * @var Integer
     */
    private $uploadType = null;
    
    /**
     * Properties for images.
     *
     * @var Array
     */
    private $imageProperties = [];
    
    /**
     * 
     * @param String $folder
     * @param FileElement $fileElement
     * @throws SynopsyException
     * @throws UploadException
     */
    public function __Construct($folder,$fileElement) {
        $this->folder = DIR.$folder;
        if (!file_exists($this->folder)) {
            throw new SynopsyException("Folder '$this->folder' doesn't exist!");
        }
        if (!$fileElement instanceof FileElement) {
            throw new SynopsyException("Parameter \$fileElement must be instance of class FileElement!");
        }
        $file = $_FILES[$fileElement->getName()];
        if (!is_array($file['name'])) {
            $name = $file['name'];
            $file['name'] = [];
            $file['name'][0] = $name;
            $tmpName = $file['tmp_name'];
            $file['tmp_name'] = [];
            $file['tmp_name'][0] = $tmpName;
            $size = $file['size'];
            $file['size'] = [];
            $file['size'][0] = $size;
        }
        if ($file['name'][0]) {
            $fileCount = count($file['name']);
            // Verify file count
            if ($fileCount > $fileElement->getMaxFileCount()) {
                throw new UploadException('FILE_LIMIT_EXCEEDED',UploadException::FILE_LIMIT_EXCEEDED);
            }
            // Verify file extensions and sizes
            for ($i = 0; $i < $fileCount; $i++) {
                // Extension
                $f = explode('.',$file['name'][$i]);
                $last = (count($f) - 1);
                $extension = $f[$last];
                if (!in_array($extension,$fileElement->getAllowedExtensions())) {
                    throw new UploadException('INVALID_FILE_EXTENSIONS',UploadException::INVALID_FILE_EXTENSION);
                }
                // Size
                if ($file['size'][$i]/1024/1024 > $fileElement->getMaxFileSize()) {
                    throw new UploadException('FILE_SIZE_EXCEEDED',UploadException::FILE_SIZE_EXCEEDED);
                }
                unset($f[$last]);
                // Save file
                $this->files[$i] = [
                    'name' => Lib::removeSpecialChars(implode('.',$f)),
                    'extension' => $extension,
                    'tmp' => $file['tmp_name'][$i]
                ];
            }
        }
    }
    
    /**
     * Sets upload type as file.
     * 
     */
    public function isFile() {
	$this->uploadType = self::UPLOAD_FILE;
    }
 
    /**
     * Sets upload type as image.
     * 
     */
    public function isImage() {
	$this->uploadType = self::UPLOAD_IMAGE;
    }
    
    /**
     * 
     * @param type $imageWidth
     * @param type $imageHeight
     * @param type $cropImage
     */
    public function setImageProperties($imageWidth,$imageHeight,$cropImage=false) {
	$this->imageProperties['init'] = true;
	$this->imageProperties['imageWidth'] = $imageWidth;
	$this->imageProperties['imageHeight'] = $imageHeight;
	$this->imageProperties['cropImage'] = $cropImage;
    }
    
    /**
     * 
     * @param type $thumbnailWidth
     * @param type $thumbnailHeight
     */
    public function setThumbnailProperties($thumbnailWidth,$thumbnailHeight) {
	$this->imageProperties['thumbnail'] = true;
	$this->imageProperties['thumbnailWidth'] = $thumbnailWidth;
	$this->imageProperties['thumbnailHeight'] = $thumbnailHeight;
    }
    
    /**
     * Verifies and uploads file to server.
     * 
     * @param String $fileName Desired file name on the server.
     * @return Array File attributes
     * @throws UploadException Thrown if move_uploaded_file function fails.
     */
    public function start() {
	if ($this->uploadType == self::UPLOAD_IMAGE) {
	    if (!isset($this->imageProperties['init'])) {
		throw new SynopsyException('Missing image properties configuration! Please call method Upload::setImageProperties() before calling method Upload::start()!');
	    }
	}
        $result = [];
        foreach ($this->files as $file) {
            $path = $this->folder.'/'.$file['name'].'.'.$file['extension'];
            if (file_exists($path)) {
                $newName = uniqid('file-').'.'.$file['extension'];
                $path = $this->folder.'/'.$newName;
            } else {
                $newName = $file['name'].'.'.$file['extension'];
            }
            switch ($this->uploadType) {
                case self::UPLOAD_FILE:
                    $this->uploadFile($file['tmp'],$path);
                    break;
                case self::UPLOAD_IMAGE:
                    $this->uploadImage($file['tmp'],$path);
                    break;
                default:
                    throw new SynopsyException("Invalid upload type: '$this->uploadType'!");
            }
            $result[] = [
                'path' => $path,
                'originalName' => $file['name'].'.'.$file['extension'],
                'uploadedName' => $newName
            ];
        }
        return $result;
    }
    
    /**
     * Uploads file to server.
     * 
     * @return Array
     * @throws UploadException
     */
    private function uploadFile($tmp,$path) {
	if (move_uploaded_file($tmp,$path)) {
	    return true;
	} else {
	    throw new UploadException('UPLOAD_FAILED',UploadException::UPLOAD_FAILED);
	}
    }
    
    /**
     * Uploads image to server.
     * 
     */
    private function uploadImage($tmp,$path) {
	if (move_uploaded_file($tmp,$path)) {
	    //$this->resizeImage($path);
            
            
            
            $image = new Imagick($path);
            
            
            
            
            
            
	    return true;
	} else {
	    throw new UploadException('UPLOAD_FAILED',UploadException::UPLOAD_FAILED);
	}
    }
    
    /**
     * Resizes uploaded image to fit desired image properties.
     * 
     */
    private function resizeImage($path) {
	$image = new Imagick($path);
	$originalWidth = $image->getImageWidth();
	$originalHeight = $image->getImageHeight();
	$targetWidth = $this->imageProperties['imageWidth'];
	$targetHeight = $this->imageProperties['imageHeight'];
	$widthRatio = ($originalWidth / $targetWidth);
	$heightRatio = ($originalHeight / $targetHeight);
	$crop = null;
	if ($widthRatio > $heightRatio) {
	    $newWidth = round($originalWidth / $heightRatio);
	    $newHeight = $targetHeight;	
	    $crop = 'width';
	} else {
	    $newWidth = $targetWidth;
	    $newHeight = round($originalHeight / $widthRatio);	
	    $crop = 'height';
	}
	$image->resizeImage($newWidth,$newHeight,Imagick::FILTER_UNDEFINED,1);
	if ($this->imageProperties['cropImage'] == true) {
	    if ($crop == 'width') {
		$x = round(($newWidth - $targetWidth) / 2);
		$y = 0;
	    } else {
		$x = 0;
		$y = round(($newHeight - $targetHeight) / 2);
	    }
	    $image->cropImage($targetWidth,$targetHeight,$x,$y);
	}
	$image->writeImage();
	if (isset($this->imageProperties['thumbnail'])) {
	    $this->createThumbnail($path,$image,$originalWidth,$originalHeight);
	}
    }
    
    /**
     * Creates thumbnail image from supplied original image to fit desired
     * thumbnail properies.
     * 
     * @param Imagick $image
     * @param Integer $originalWidth
     * @param Integer $originalHeight
     */
    private function createThumbnail($path,$image,$originalWidth,$originalHeight) {
	$thumbnailFolder = $this->folder.'/thumbnail';
	if (!file_exists($thumbnailFolder)) {
	    mkdir($thumbnailFolder);
	}
        $t = explode('/',$path);
        $thumbnailPath = $thumbnailFolder.'/'.$t[count($t)-1];
	$targetWidth = $this->imageProperties['thumbnailWidth'];
	$targetHeight = $this->imageProperties['thumbnailHeight'];
	$widthRatio = ($originalWidth / $targetWidth);
	$heightRatio = ($originalHeight / $targetHeight);
	$crop = null;
	if ($widthRatio > $heightRatio) {
	    $newWidth = round($originalWidth / $heightRatio);
	    $newHeight = $targetHeight;	
	    $crop = 'width';
	} else {
	    $newWidth = $targetWidth;
	    $newHeight = round($originalHeight / $widthRatio);	
	    $crop = 'height';
	}
	$newImage = clone $image;
	$newImage->resizeImage($newWidth,$newHeight,Imagick::FILTER_UNDEFINED,1);	
	if ($crop == 'width') {
	    $x = round(($newWidth - $targetWidth) / 2);
	    $y = 0;
	} else {
	    $x = 0;
	    $y = round(($newHeight - $targetHeight) / 2);
	}
	$newImage->cropImage($targetWidth,$targetHeight,$x,$y);
	$newImage->setImageFilename($thumbnailPath);
	$newImage->writeImage();
    }
    
}
