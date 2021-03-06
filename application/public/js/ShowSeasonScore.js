(function(window, $) {
    alert('Library loaded');
    $(".show_season_score_form").submit(function(e) {
        e.preventDefault();
        const form = $(this)
        const url = form.attr('action')
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function () {
                // noinspection JSUnresolvedFunction
                window.location.replace(Routing.generate('admin_show_season_score_index'))
            },
            error: function () {
                alert('Got error');
            }
        })
    });
})(window, jQuery);
