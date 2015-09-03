<script type="text/javascript">

    /**
     * PHP-to-javascript constants transfer.
     */

    /* URL */
    var __URL = '{$smarty.const.URL}';

    /* Link */
    var __AJAX_LINK = '{Route::AJAX_LINK}';
        
    /* Form */
    var __FORM = {
        HTML_FORM: '{Form::HTML_FORM}',
        DATA_MULTIPART: '{Form::DATA_MULTIPART}'
    };
    
    var __ELEMENT = {
        IS_BOOTSTRAP: '{Element::IS_BOOTSTRAP}',
        DATA_SUBMIT_TYPE: '{Element::DATA_SUBMIT_TYPE}',
        DATA_SUBMIT_URL: '{Element::DATA_SUBMIT_URL}',
        DATA_SUBMIT_TARGET: '{Element::DATA_SUBMIT_TARGET}',
        DATA_DATATYPE: '{Element::DATA_DATATYPE}',
        DATA_VALIDATE_REGEX: '{Element::DATA_VALIDATE_REGEX}',
        DATA_UPLOAD_MAX_FILE_SIZE: '{Element::DATA_UPLOAD_MAX_FILE_SIZE}',
        DATA_UPLOAD_MAX_FILE_COUNT: '{Element::DATA_UPLOAD_MAX_FILE_COUNT}',
        DATA_UPLOAD_ALLOWED_EXTENSIONS: '{Element::DATA_UPLOAD_ALLOWED_EXTENSIONS}'
    };
    
    var __BUTTON = {
        SUBMIT: '{ButtonElement::SUBMIT}',
        SYNC: '{ButtonElement::SYNC}',
        AJAX: '{ButtonElement::AJAX}',
    };
    
    /* Form/Datatype */
    var __DATATYPE = {        
        STRING: '{Datatype::STRING}',
        BOOLEAN: '{Datatype::BOOLEAN}',
        INTEGER: '{Datatype::INTEGER}',
        DATATYPE_FLOAT: '{Datatype::FLOAT}'
    };
    
    /* Verify */
    var __VALIDATE = {
        REQUIRED: '{Validate::REQUIRED}',
        EMAIL: '{Validate::EMAIL}',
        EMAIL_REGEX: '{Validate::EMAIL_REGEX}',
        DATE: '{Validate::DATE}',
        DATE_REGEX: '{Validate::DATE_REGEX}',
        TIME: '{Validate::TIME}',
        TIME_REGEX: '{Validate::TIME_REGEX}',
        LOGIN: '{Validate::LOGIN}',
        LOGIN_REGEX: '{Validate::LOGIN_REGEX}',
        PASSWORD: '{Validate::PASSWORD}',
        PASSWORD_REGEX: '{Validate::PASSWORD_REGEX}'
    };
    
</script>
<script type="text/javascript" src="{$smarty.const.URL}cache/script.js"></script>