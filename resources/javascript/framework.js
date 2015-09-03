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

/**
 * Main functions that are part of the core framework functionality.
 * 
 * @author Michal Sukupčák <sukupcak@webdesign-studio.sk> 
 */

/* ---------------------------------------------------------------------- */
/* --- Variables --- */

/**
 * Holds array of all validated fileinputs. Each fileinput has its own
 * key with own true/false value.
 * 
 * @type Array
 */
var __validFiles = {};

/* ---------------------------------------------------------------------- */
/* --- HTTP Request functions --- */

/**
 * Creates <form> copy from content of div <div class="FORM__FORM_CSS"> and
 * submits new form according to div's attributes.
 * 
 * @param t Submit button jquery object
 * @param e Button click event
 * @returns {undefined}
 */
function __syncPostRequest(t,e) {
    var form = t.parents('.' + __FORM['HTML_FORM']);
    var multipart = form.data(__FORM['DATA_MULTIPART']);
    if (__validateForm(form) === true) { // Submit valid form
        form.attr({
            method: 'post',
            action: t.data(__ELEMENT['DATA_SUBMIT_URL']),
            enctype: (multipart !== undefined ? 'multipart/form-data' : '')
        }).submit();
    } else { // 
        e.preventDefault();
    }
}

/**
 * Creates POST AJAX call after a form submit button has been clicked
 * 
 * @param t Submit button jquery object
 * @param e Button click event
 * @returns {undefined}
 */
function __ajaxPostRequest(t,e) {
    e.preventDefault();
    var buttonText = t.html();
    var form = t.parents('.' + __FORM['HTML_FORM']);
    if (__validateForm(form)) {
        var data = form.find('input, select, textarea').serialize();
        var url = t.data(__ELEMENT['DATA_SUBMIT_URL']);
        if (url === '') {
            url = window.location.href;
        }
        var loading = $('#loading');
        loading.fadeIn();
        t.removeClass(__BUTTON['SUBMIT']);
        $.ajax({
            url: __URL + 'ajax/' + url.split(__URL)[1],
            type: 'POST',
            data: data
        }).done(function (response) {
            $('#' + t.data(__ELEMENT['DATA_SUBMIT_TARGET'])).html('').html(response);
        }).fail(function (xhr) {
            alert('AJAX POST error! See js console for details');
            console.log(xhr);
        }).always(function (data) {
            loading.fadeOut();
            t.html(buttonText);
            t.addClass(__BUTTON['SUBMIT']);
        });
    }
}

/**
 * Creates GET AJAX call after a <a href> link has been clicked.
 *
 * @param t
 * @returns {undefined} */
function __ajaxGetRequest(t) {
    /*var data = t.attr('href').split('#')[1];*/
    var data = t.attr('href');
    var target = t.data('target');
    var loading = $('#loading');
    loading.fadeIn();
    $.ajax({
	url: __URL + 'ajax/' + data.split(__URL)[1],
	type: 'GET'
    }).done(function (response) {
	$('#' + target).empty().html(response);
    }).fail(function (xhr) {
	alert('AJAX GET error! See js console for details.');
	console.log(xhr);
    }).always(function (data) {
        loading.fadeOut();
    });
    return false;
}

/**
 * Validates form supplied as parameter.
 * 
 * @param {type} form
 * @returns {cloneForm.newForm}
 */
function __validateForm(form) {
    var isValid = true;
    
    /* --- Text, password & hidden inputs & textarea ------------------------ */
    form.find('input[type="text"],input[type="password"],input[type="hidden"],textarea').each(function () {
	var t = $(this);
        var value = t.val();
        var validate = t.data(__ELEMENT['DATA_VALIDATE_REGEX']);
        
        var validValue = true;
        if (validate !== undefined) {
            if (validate === __VALIDATE['REQUIRED']) {
                var datatype = t.data(__ELEMENT['DATA_DATATYPE']);
                if (datatype !== undefined) {
                    if (datatype === __DATATYPE['STRING'] || datatype === __DATATYPE['BOOLEAN']) {
                        if (value === '') {
                            validValue = false;
                        }
                    } else if (datatype === __DATATYPE['INTEGER']) {
                        var i = parseInt(value);
                        if (i <= 0 || isNaN(i)) {
                            validValue = false;
                        }
                    } else { // __DATATYPE['FLOAT']
                        var f = parseFloat(value);
                        if (f <= 0.00 || isNaN(f)) {
                            validValue = false;
                        }
                    }
                } else {
                    console.log('[ERROR] Missing data-validate-datatype attribute for element: ' + t);
                    isValid = false;
                }
            } else {
                var regex;
                if (validate === __VALIDATE['EMAIL']) {
                    regex = __VALIDATE['EMAIL_REGEX'];
                } else if (validate === __VALIDATE['DATE']) {
                    regex = __VALIDATE['DATE_REGEX'];
                } else if (validate === __VALIDATE['TIME']) {
                    regex = __VALIDATE['TIME_REGEX'];
                } else if (validate === __VALIDATE['LOGIN']) {
                    regex = __VALIDATE['LOGIN_REGEX'];
                } else if (validate === __VALIDATE['PASSWORD']) {
                    regex = __VALIDATE['PASSWORD_REGEX'];
                } else {
                    regex = validate;
                }
                if (!(new RegExp(regex)).test(value)) {
                    validValue = false;
                }
            }
            if (validValue) {
                if (t.hasClass(__ELEMENT['IS_BOOTSTRAP'])) {
                    t.parents('.form-group').removeClass('has-error');
                } else {
                    t.removeClass('has-error');
                }
            } else {
                if (t.hasClass(__ELEMENT['IS_BOOTSTRAP'])) {
                    t.parents('.form-group').addClass('has-error');
                } else {
                    t.addClass('has-error');
                }
                isValid = false;
            }
        }
    });
    
    /* --- Select ----------------------------------------------------------- */
    form.find('select').each(function () {
	var t = $(this);
        var selected = t.find('option:selected').val();
        var validate = t.data(__ELEMENT['DATA_VALIDATE_REGEX']);
        if (validate !== undefined) {
            selected = parseInt(selected);
            if (selected > 0) {
                if (t.hasClass(__ELEMENT['IS_BOOTSTRAP'])) {
                    t.parents('.form-group').removeClass('has-error');
                } else {
                    t.removeClass('has-error');
                }
            } else {
                if (t.hasClass(__ELEMENT['IS_BOOTSTRAP'])) {
                    t.parents('.form-group').addClass('has-error');
                } else {
                    t.addClass('has-error');
                }
                isValid = false;
            }
        }
    });
    
    /* --- Checkbox -------------------------------------------------------- */
    form.find('input[type="checkbox"]').each(function () {
	var t = $(this);
        var checked = t.is(':checked');
        var validate = t.data(__ELEMENT['DATA_VALIDATE_REGEX']);
        if (validate !== undefined) {
            if (checked) {
                if (t.hasClass(__ELEMENT['IS_BOOTSTRAP'])) {
                    t.parents('.form-group').removeClass('has-error');
                } else {
                    t.removeClass('has-error');
                }
            } else {
                if (t.hasClass(__ELEMENT['IS_BOOTSTRAP'])) {
                    t.parents('.form-group').addClass('has-error');
                } else {
                    t.addClass('has-error');
                }
                isValid = false;
            }
        }
    });
    
    /* --- Radio ------------------------------------------------------------ */
    form.find('input[type="radio"]').each(function () {
	var t = $(this);
        var validate = t.data(__ELEMENT['DATA_VALIDATE_REGEX']);
        if (validate !== undefined) {
            /* @todo Validation missing. */
        }
    });
    
    // Scroll to top of the form on validation fail
    if (!isValid) {
        var id = form.attr('id');
        $('html, body').animate({
            scrollTop: $('#' + id).offset().top
        },750);
    }
    
    // Check fileinputs
    for (var key in __validFiles) {
        if (!__validFiles[key]) {
            isValid = false;
        }
    }   
    
    return isValid;
}

/**
 * Validates fileinput on fileinput change event (document.on)
 * 
 */
function __validateFile() {
    // Load variables
    var input = $(this);
    var output = input.parents('.input-group').find(':text');
    var hasOutput = (output.length !== 0);
    var outputParent = (hasOutput ? output.parents('.form-group') : null);
    var maxFileCount = input.data(__ELEMENT['DATA_UPLOAD_MAX_FILE_COUNT']);
    var maxFileSize = input.data(__ELEMENT['DATA_UPLOAD_MAX_FILE_SIZE']);
    var allowedExtensions = input.data(__ELEMENT['DATA_UPLOAD_ALLOWED_EXTENSIONS']).split('|');
    var files = this.files;
    var fileCount = this.files.length;
    var valid = true;
    var modal = $('#modal');

    // Clear output error formatting
    if (output.length) {
        outputParent.removeClass('has-error');
    }

    // Check file count
    if (fileCount > maxFileCount) {
        valid = false;
        if (hasOutput) {
            outputParent.addClass('has-error');
            output.val('ERROR: You\'ve selected too many files for upload!');
        }
        modal.find('.modal-header').removeClass().addClass('modal-header has-error');
        modal.find('#modalTitle').html('<b>ERROR: You\'ve selected too many files for upload!</b>');
        modal.find('#modalContent').html(''
            + '<div class="text-center">'
                + '<div class="i-circle danger"><i class="fa fa-times"></i></div>'
                + '<br><br>'
                + 'You\'ve selected <b>' + fileCount + '</b> files for upload.<br>'
                + 'Maximum allowed number of files for upload: ' + maxFileCount + '<br>'
            + '</div>'
        + '');
        modal.modal();
    }

    if (valid) {
        
        // Verify each file - extension and file size
        var file;
        var ext;
        var extension;
        var size;
        for (var i = 0; i < fileCount; i++) {
            file = files[i];
            ext = file.name.split('.');
            extension = ext[ext.length - 1];
            if (allowedExtensions.indexOf(extension) === -1) {
                valid = false;
                if (hasOutput) {
                    outputParent.addClass('has-error');
                    output.val('ERROR: Invalid file extension in one or more files!');
                }
                modal.find('.modal-header').removeClass().addClass('modal-header has-error');
                modal.find('#modalTitle').html('<b>ERROR: Invalid file extension in one or more files!</b>');
                modal.find('#modalContent').html(''
                    + '<div class="text-center">'
                        + '<div class="i-circle danger"><i class="fa fa-times"></i></div>'
                        + '<br><br>'
                        + 'Extension <u>' + extension + '</u> for file <b>"' + file.name +'"</b> is not allowed!<br>'
                        + 'Allowed file extensions are: ' + allowedExtensions.join(', ').trim(', ')
                    + '</div>'
                + '');
                modal.modal();
                break;
            }
            size = file.size/1024/1024;
            if (size > maxFileSize) {
                valid = false;
                if (hasOutput) {
                    outputParent.addClass('has-error');
                    output.val('ERROR: Exceeded file limit for one or more files!');
                }
                modal.find('.modal-header').removeClass().addClass('modal-header has-error');
                modal.find('#modalTitle').html('<b>ERROR: Exceeded file limit for one or more files!</b>');
                modal.find('#modalContent').html(''
                    + '<div class="text-center">'
                        + '<div class="i-circle danger"><i class="fa fa-times"></i></div>'
                        + '<br><br>'
                        + 'Size of file <b>"' + file.name + '"</b> (<u>' + Math.round(size) + 'MB</u>) exceeds maximum size for one file!<br>'
                        + 'Maximum file size per file: ' + maxFileSize + 'MB'
                    + '</div>'
                + '');
                modal.modal();
                break;
            }
        }
    }
    
    if (valid) {
        if (hasOutput) {
            output.val(fileCount > 1 ? fileCount + ' files selected' : input.val().replace(/\\/g,'/').replace(/.*\//,''));
        }
        __validFiles[input.attr('name')] = true;
    } else {
        input.val('');
        __validFiles[input.attr('name')] = false;
    }

}

/* ---------------------------------------------------------------------- */
/* --- DOM ready event --- */

$(document).ready(function () {
    // Empty
});

/* ---------------------------------------------------------------------- */
/* --- Window ready events --- */

$(window).load(function () {
    // Empty
});

/* ---------------------------------------------------------------------- */
/* --- DOM events --- */

/**
 * Document.on jquery events
 */
$(document)
    // Click on form submit button
    .on('click','.' + __BUTTON['SUBMIT'],function (e) {
	var t = $(this);
	if (t.data(__ELEMENT['DATA_SUBMIT_TYPE']) === __BUTTON['SYNC']) {
	    __syncPostRequest(t,e);
	} else {
	    __ajaxPostRequest(t,e);
	}
    })
    // Click on AJAX link
    .on('click','.' + __AJAX_LINK,function (e) {
        e.preventDefault();
        __ajaxGetRequest($(this));
    })
    // After selected file input (bootstrap fileinput only)
    .on('change', 'input[type="file"]',__validateFile)
; 
