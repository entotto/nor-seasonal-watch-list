(function(window, $) {

    $('#page_size_picker').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace('?perPage=' + val)
    })

    $('#season_picker').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace('?season=' + val)
    })

})(window, jQuery);
