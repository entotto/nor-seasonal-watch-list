(function(window, $) {

    $('#select_show').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace('#' + val)
    })

    const containerValues = {}
    let currentAnchorId = ''
    const changeSelector = function () {
        // noinspection JSUnresolvedVariable
        const anchorId = document.querySelector('#' + getKeyForMax(containerValues)).dataset.anchorid
        if (currentAnchorId !== anchorId) {
            currentAnchorId = anchorId
            document.querySelector('#select_show [value="show_target_' + anchorId + '"]').selected = true
        }
    }
    const showContainers = document.querySelectorAll('.show_container')
    const observer = new IntersectionObserver(function (entries) {
        for (const entry of entries) {
            // if (entry['isIntersecting'] === true) {
            containerValues[entry['target'].id] = entry['intersectionRatio']
            setTimeout(() => {changeSelector()}, 1000)
            // }
        }
    }, { threshold: [0.2, 0.4, 0.6, 0.8]})
    showContainers.forEach(showContainer => {
        observer.observe(showContainer)
    })

    const getKeyForMax = function (dict) {
        let keyForMax = '';
        let max = 0;
        for (const [key, value] of Object.entries(dict)) {
            if (value > max) {
                max = value
                keyForMax = key
            }
        }
        return keyForMax
    }

})(window, jQuery);
