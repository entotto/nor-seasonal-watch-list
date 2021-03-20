(function(window, $) {

    $('#select_season').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace(Routing.generate('all_watch_index') + '?season=' + val)
    })

})(window, jQuery);
