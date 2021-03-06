(function(window, $) {
    // noinspection DuplicatedCode
    $("form.list_my_vote_form input:checkbox").change(function (e) {
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
    })
})(window, jQuery);
