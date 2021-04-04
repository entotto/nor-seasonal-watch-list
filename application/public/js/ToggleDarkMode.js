// from: https://stackoverflow.com/questions/9899372#9899701
function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

docReady(function() {
    let currentColor

    // DOM is loaded and ready for manipulation from here

    // parts from: https://radek.io/posts/secret-darkmode-toggle/

    const toggle_btn = document.getElementById('darkmode-button');
    let isCssInit = false

    function setColorPreference(color_p, persist = false) {
        const new_s = color_p;
        const old_s = color_p === 'light' ? 'dark' : 'light'

        const el = document.body;  // gets root <html> tag
        el.classList.add('color-scheme-' + new_s);
        el.classList.remove('color-scheme-' + old_s);

        if (persist) {
            localStorage.setItem('preferred-color-scheme', color_p);
        }
    }

    function updateUI(color_p, id = 'css') {
        toggle_btn.checked = color_p === 'dark';

        if (isCssInit) {
            const el = document.querySelector('#'+id);
            const data = el.dataset;
            if (toggle_btn.checked) {
                el.setAttribute('href', data.hrefDark)
            } else {
                el.setAttribute('href', data.hrefLight);
            }
            data.colorScheme = color_p;
        }
    }

    function initColorCSS(color_p, id = 'css') {
        isCssInit = true;

        el_o = document.querySelector('#'+id);
        if (el_o !== null) el_o.remove();
        el_l = document.querySelector('#'+id+'-light');
        el_d = document.querySelector('#'+id+'-dark');
        if (color_p === 'dark') {
            el = el_d;
            el_o = el_l;
        } else {
            el = el_l;
            el_o = el_d;
        }
        el.setAttribute('data-href-light', el_l.getAttribute('href'));
        el.setAttribute('data-href-dark', el_d.getAttribute('href'));
        el.setAttribute('data-color-scheme', color_p);
        el.setAttribute('media', 'all');
        el.setAttribute('id', id);
        el_o.remove();
    }

    toggle_btn.addEventListener('click', () => {
        currentColor = currentColor === 'light' ? 'dark' : 'light'
        // const color_p = toggle_btn.checked ? 'dark' : 'light';
        const color_p = currentColor

        if (!isCssInit) initColorCSS(color_p);

        setColorPreference(color_p, true);
        updateUI(color_p);
    });

    // document.addEventListener("keypress", function(event){
    //     var keyName = event.key;
    //     if ((keyName == 'd') || (keyName == 'D')) {
    //         toggle_btn.click();
    //     }
    // });

    /* Set Preference on load */
    const osColorPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    // console.log('OS wants ' + osColorPreference);
    let preferredColorScheme = localStorage.getItem('preferred-color-scheme')
    currentColor = preferredColorScheme
    if (preferredColorScheme !== null) {
        initColorCSS(preferredColorScheme);
    } else {
        preferredColorScheme = osColorPreference;
    }
    setColorPreference(preferredColorScheme, false);
    updateUI(preferredColorScheme);
});
