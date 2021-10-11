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
})