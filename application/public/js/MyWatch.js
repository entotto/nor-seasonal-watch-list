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

    // Work around scroll-to-anchor bug in chrome
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    if (window.location.hash && isChrome) {
        setTimeout(function () {
            const hash = window.location.hash;
            window.location.hash = "";
            window.location.hash = hash;
        }, 300);
    }

})(window, jQuery);
