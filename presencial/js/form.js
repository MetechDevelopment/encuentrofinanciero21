$(document).on('click', 'input#legal', function (e) {
    $('form#registration_form button').prop('disabled', !this.checked);
});

$(document).on('submit', 'form#registration_form', function (e) {
    e.preventDefault();
    btnSub = $(this).find('button');
    btnSub.html('<sapn>Enviando…</span><span class="fa fa-spinner fa-spin fa-fw" aria-hidden=""></span>');
    btnSub.prop('disabled', true);

    var serializedData = $(this).serialize();

    $.ajax({
        type: $(this).attr('method'),
        data: serializedData,
        url: $(this).attr('action'),
        dataType: 'json',
        cache: false,
        context: this,
        success: function (data) {
            console.log(data);

            $('#error_message').addClass('hidden');
            btnSub.html('<sapn>ENVIANDO</span><span class="fa fa-check" aria-hidden=""></span>');
            $(this).trigger('reset');

            // Simulate an HTTP redirect:
            window.location.replace('gracias.html');
        },
        error: function (xhr, status, error) {
            var data = JSON.parse(xhr.responseText);

            console.log(data);

            $('#error_message').removeClass('hidden');

            $('#error_message .text').html('<strong>¡Cuidado!</strong>. ' + data.response.message);
            btnSub.html('<span> SOLICITAR INVITACIÓN </span> <span class="fa fa-paper-plane" aria-hidden></span>');

            $(this).find('button').prop('disabled', false);
        },
    });

    return false;
});
