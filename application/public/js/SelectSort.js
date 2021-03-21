(function(window, $) {

    $('#select_sort').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        window.location.replace('?sort=' + val)
    })

})(window, jQuery);
