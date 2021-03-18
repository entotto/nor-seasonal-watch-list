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
    const formatDate = function (originalDate) {
        const day = originalDate.getDate()
        const formattedDay = day < 10 ? "0" + day : day
        const dayNum = originalDate.getDay()
        const month = originalDate.getMonth() + 1
        const formattedMonth = month < 10 ? "0" + month : month
        const year = originalDate.getFullYear()
        const formattedLocalTime = originalDate.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})
        const offset = originalDate.getTimezoneOffset()
        let offsetHours = Math.floor(offset / 60)
        if (offsetHours < 10) {
            offsetHours = "0" + offsetHours
        }
        let offsetMinutes = offset % 60
        if (offsetMinutes < 10) {
            offsetMinutes = "0" + offsetMinutes
        }
        let offsetSign
        if (offset > 0) {
            offsetSign = '-'
        } else {
            offsetSign = '+'
        }
        const offsetText = offsetSign + offsetHours + ':' + offsetMinutes
        let dayName
        switch (dayNum) {
            case 0:
                dayName = 'Sunday'
                break;
            case 1:
                dayName = 'Monday'
                break;
            case 2:
                dayName = 'Tuesday'
                break;
            case 3:
                dayName = 'Wednesday'
                break;
            case 4:
                dayName = 'Thursday'
                break;
            case 5:
                dayName = 'Friday'
                break;
            case 6:
                dayName = 'Sunday'
                break;
            default:
                dayName = ''
        }
        let monthName
        switch (month) {
            case 1:
                monthName = 'Jan'
                break;
            case 2:
                monthName = 'Feb'
                break;
            case 3:
                monthName = 'Mar'
                break;
            case 4:
                monthName = 'Apr'
                break;
            case 5:
                monthName = 'May'
                break;
            case 6:
                monthName = 'Jun'
                break;
            case 7:
                monthName = 'Jul'
                break;
            case 8:
                monthName = 'Aug'
                break;
            case 9:
                monthName = 'Sep'
                break;
            case 10:
                monthName = 'Oct'
                break;
            case 11:
                monthName = 'Nov'
                break;
            case 12:
                monthName = 'Dec'
                break;
            default:
                monthName = ''
        }
        return dayName + ' ' + year + '-' + formattedMonth + '-' + formattedDay + ' ' +
            formattedLocalTime + ' ' + offsetText
    }
    const setLocalTimes = function () {
        const startTime = new Date(electionTimes.start)
        const endTime = new Date(electionTimes.end)
        $("#localStartTime").text(formatDate(startTime))
        $("#localEndTime").text(formatDate(endTime))
    }
    const calcRemaining = function(difference, defaultResult) {
        let result = defaultResult
        if (difference > 0) {
            const parts = {
                days: Math.floor(difference / (1000 * 60 * 60 * 24)),
                hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
                minutes: Math.floor((difference / 1000 / 60) % 60),
                seconds: Math.floor((difference / 1000) % 60)
            }
            if (parts.seconds < 10) {
                parts.seconds = '0' + parts.seconds
            }
            result = Object.keys(parts).map(part => {
                if (!parts[part]) return
                return `${parts[part]} ${part}`
            }).join(" ")
        } else {
            // Reload the page to get its other mode.
            // noinspection JSUnresolvedFunction
            window.location.reload()
        }
        return result
    }
    const countdownTimer = function () {
        const startDifference = +new Date(electionTimes.start) - +new Date()
        const endDifference = +new Date(electionTimes.end) - +new Date()
        const startOutput = document.getElementById('countdownStartOutput')
        if (startOutput) {
            startOutput.innerHTML = calcRemaining(startDifference, "Voting has started!")
        }
        const endOutput = document.getElementById('countdownOutput')
        if (endOutput) {
            endOutput.innerHTML = calcRemaining(endDifference, "Time's up!")
        }
    }
    setLocalTimes()
    countdownTimer()
    setInterval(countdownTimer, 1000)
})(window, jQuery);
