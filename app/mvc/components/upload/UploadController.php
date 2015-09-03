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

use Synopsy\Files\Extension;
use Synopsy\Form\Elements\ButtonElement;
use Synopsy\Form\Elements\FileElement;
use Synopsy\Form\Form;
use Synopsy\Lang\Strings;
use Synopsy\Mvc\Controller;
use Synopsy\Routing\Route;

/**
 * @CONTROLLER
 * @Route-en upload
 * @Route-sk nahravanie
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk>
 */
final class UploadController extends Controller {
    
    /* ---------------------------------------------------------------------- */
    /* Constants */
    
    // No constants
    
    /* ---------------------------------------------------------------------- */
    /* Attributes */    
    
    private $uploadForm = null;
    
    /* ---------------------------------------------------------------------- */
    /**
     * Controller::onLoad()
     * 
     * Initial method of the controller, called immediately after creating new
     * controller instance.
     */
    protected function onLoad() {
        $this->uploadForm = new Form('uploadForm');
        $this->uploadForm->setElements([
            (new FileElement('images'))
                ->setMaxFileSize(2)
                ->setMaxFileCount(5)
                ->setAllowedExtensions([
                    Extension::JPG,
                    Extension::PNG,
                    Extension::GIF
                ])
            ,
            (new ButtonElement('upload'))
                ->setDefaultValue(Strings::get('upload.submit'))
                ->setCssClass('btn-primary')
                ->setIsSubmit(true)
                ->setSubmitType(ButtonElement::SYNC)
                ->setSubmitUrl(new Route('upload/UploadController:uploadImage'))
        ]);
    }
        
    /* ---------------------------------------------------------------------- */
    /* MVC methods */
    
    public function main() {
	$this->htmlView = $this->newView('UploadView');
        $this->htmlView->setData([
            'uploadForm' => $this->uploadForm
        ]);
    }
    
    /**
     * @METHOD
     * @Route-en upload-image
     * @Route-sk nahrat-obrazok
     */
    public function uploadImage() {
        $upload = new Upload('data/files/images',$this->uploadForm->getElement('images'));
        $upload->isImage();
        $upload->setImageProperties(200,200,true);
        $upload->setThumbnailProperties(50,50);
        $result = $upload->start();
        
	$this->htmlView = $this->newView('UploadImageView');
        $this->htmlView->setData([
            
        ]);
    }
    
    /* ---------------------------------------------------------------------- */
    /* Additional private methods */    
    
    // No methods
    
}