<div id="contentWrapper" class="container">
    
    <div class="text-center" id="icon">
        <h3><i class="fa fa-upload"></i> {getString key="upload.title"}</h3>
    </div>
    
    <div class="text-center">
        Max. file size: {$uploadForm->getElement('images')->getMaxFileSize()}MB<br>
        Max. file count: {$uploadForm->getElement('images')->getMaxFileCount()}<br>
        Allowed extensions: {implode(', ',$uploadForm->getElement('images')->getAllowedExtensions())}<br>
        <br>
        {$uploadForm->openFormHtml()}
            {bootstrap element=$uploadForm->getElement('images') name={getString key="upload.input"}}
            {bootstrap element=$uploadForm->getElement('upload')}
        {$uploadForm->closeFormHtml()}
    </div>
    
</div>