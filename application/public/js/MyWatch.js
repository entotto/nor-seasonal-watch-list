(function(window, $) {
    // noinspection DuplicatedCode
    const submitSelection = function (e) {
        e.preventDefault();
        const control = $(e.target)
        const form = control.closest('form')
        const url = form.attr('action')
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function () {
            },
            error: function (x) {
                alert('Got error posting the form: ' + x.data);
            }
        })
    }

    $("form.list_my_watch_form select").change(submitSelection)

    $("form.list_my_watch_form input[type=radio]").change(submitSelection)

    $('#select_season').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction
        window.location.replace(Routing.generate('my_watch_index') + '?season=' + val)
    })

    $('input[type="radio"].score-choice-danger').next().addClass('text-danger');
    $('input[type="radio"].score-choice-secondary').next().addClass('text-secondary');
    $('input[type="radio"].score-choice-info').next().addClass('text-info');
    $('input[type="radio"].score-choice-primary').next().addClass('text-primary');
    $('input[type="radio"].score-choice-success').next().addClass('text-success');
    $('input[type="radio"].score-choice-royal').next().addClass('text-royal');

})(window, jQuery);
