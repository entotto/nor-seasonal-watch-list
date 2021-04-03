(function(window, $) {

    // Wide
    $('#select_show_wide').change(function (e) {
        e.preventDefault()
        const control = $(e.target)
        const val = control.val()
        // noinspection JSUnresolvedFunction,JSUnresolvedVariable
        window.location.replace('#' + val)
    })

    const containerValuesWide = {}
    let currentAnchorIdWide = ''
    const changeSelectorWide = function () {
        // noinspection JSUnresolvedVariable
        const anchorId = document.querySelector('#' + getKeyForMax(containerValuesWide)).dataset.anchorid
        if (currentAnchorIdWide !== anchorId) {
            currentAnchorIdWide = anchorId
            document.querySelector('#select_show_wide [value="show_target_wide_' + anchorId + '"]').selected = true
        }
    }
    const showContainersWide = document.querySelectorAll('.show_container_wide')
    const observerWide = new IntersectionObserver(function (entries) {
        for (const entry of entries) {
            containerValuesWide[entry['target'].id] = entry['intersectionRatio']
            setTimeout(() => {changeSelectorWide()}, 1000)
        }
    }, { threshold: [0.2, 0.4, 0.6, 0.8, 1.0]})
    showContainersWide.forEach(showContainerWide => {
        observerWide.observe(showContainerWide)
    })

    // Mobile
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
        const maxKey = getKeyForMax(containerValues)
        if (maxKey) {
            const anchorId = document.querySelector('#' + getKeyForMax(containerValues)).dataset.anchorid
            if (currentAnchorId !== anchorId) {
                currentAnchorId = anchorId
                document.querySelector('#select_show [value="show_target_' + anchorId + '"]').selected = true
            }
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
    }, { threshold: [0.2, 0.4, 0.6, 0.8, 1.0]})
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
