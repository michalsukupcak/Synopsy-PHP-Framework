<div id="contentWrapper" class="container">
    
    <div class="text-center" id="icon">
        <h3><i class="fa fa-edit"></i> {getString key="form.title"}</h3>
    </div>
    
    <div class="text-center">
        {$form->openFormHtml()}
            <br>
            <div class="row">
                {bootstrap element=$form->getElement('inputText') label={getString key="form.inputText"} labelClass="col-sm-3 col-xs-12" divClass="col-sm-9 col-xs-12"}
            </div>
            <div class="row">
                {bootstrap element=$form->getElement('inputInteger') label={getString key="form.inputInteger"} labelClass="col-sm-3 col-xs-12" divClass="col-sm-9 col-xs-12"}
            </div>
            <div class="row">
                {bootstrap element=$form->getElement('select') label={getString key="form.select"} labelClass="col-sm-3 col-xs-12" divClass="col-sm-9 col-xs-12"}
            </div>
            <div class="row">
                {bootstrap element=$form->getElement('checkbox') divClass="col-sm-9 col-sm-offset-3 col-xs-9 text-sm-left text-xs-center"}
            </div>
            <br>
            <div class="row">
                {bootstrap element=$form->getElement('submitSync')}
                {bootstrap element=$form->getElement('submitAjax')}
            </div>
        {$form->closeFormHtml()}
        <hr>
        <div id="formResponse"><h3>[{getString key="form.ajaxResponse"}]</h3></div>
    </div>
        
</div>