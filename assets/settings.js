/** global jQuery, smtp_settings */
jQuery(function ($) {
    var table = $('#smtp-test-table');
    var button = $('#smtp_test');
    button.click(function (e) {
        var data = {
            to: $('#smtp_to').val(),
            subject: $('#smtp_subject').val(),
            message: $('#smtp_message').val(),
            action: 'smtp_test_email',
            _ajax_nonce: smtp_settings.nonce
        };

        button.prop('disabled', true);
        $.ajax({
            method: 'POST',
            url: smtp_settings.ajax_url,
            data: data
        }).done(function (response) {
            table.siblings('.notice').remove();
            table.before('<div class="notice notice-success inline" role="alert"><p>' + response.data + '</p></div>');
        }).fail(function (response, a, b) {
            var message;
            if (response.responseJSON) {
                message = response.responseJSON.data || 'Unknown error';
            } else {
                message = response.statusText;
            }

            table.siblings('.notice').remove();
            table.before('<div class="notice notice-error inline" role="alert"><p>' + message + '</p></div>');
        }).always(function() {
            button.prop('disabled', false);
        });
    });
});
