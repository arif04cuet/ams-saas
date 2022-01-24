$(document).on('ajaxError', function (event, context) {
    if (context.handler === 'onPhotoUpload') {
        $("#avatarPreview").empty();
    }

    if (context.handler === 'onSignatureUpload') {
        $("#signaturePreview").empty();
    }


})

$(document).on('ajaxSuccess', function (event, context) {
    if (context.handler === 'onPhotoUpload') {
        $("div[data-validate-for='avatar']").empty();
    }
    if (context.handler === 'onSignatureUpload') {
        $("div[data-validate-for='signature']").empty();
    }
    if (context.handler === 'onSubmitRoll') {
        $('#referenceSelection').select2();
    }
})

//submit new application when button click
$('#newAppPreviewSubmitBtn').click(function () {
    $("#newApplicationForm").submit();
});

// select2 for new application and associate application
$(document).ready(function () {
    $('#rollSelection').select2();

});