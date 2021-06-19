window.onload = function () {
    setTimeout(function () {
        UIkit.alert('#alert_div').close();
    }, 5000);


    var page_n = 1;
    var per_page = 25;

    let sync_link = document.getElementById('sync');
    let prev = document.getElementById('pag_prev');
    let p_next = document.getElementById('pag_next');
    let items = document.getElementById('product_list').getElementsByTagName('li');
    var pages = Math.abs((items.length / per_page));
    if (sync_link) {
        sync_link.addEventListener('click', function () {
            // console.log(this)
            this.insertAdjacentHTML('afterend', '<div uk-spinner style="padding-left: 1em;"></div>')
        })
    }
    myfunc(items, page_n);

    if (prev || p_next) {
        prev.addEventListener('click', function () {
            if (page_n > 1) {
                page_n = Math.abs(page_n) - 1;
                myfunc(items, page_n);
            }
        });
        p_next.addEventListener('click', function () {
            if (page_n <= pages) {
                page_n = page_n + 1;
                myfunc(items, page_n);
            }
        });
    }

    function myfunc(items, page_num = 1) {
        // console.log(per_page)
        for (let i = 0; i < items.length - 2; i++) {
            let last_el = per_page * page_num;
            let first_el = last_el - per_page;
            if (i < first_el || i > last_el) {
                items[i].style.display = 'none';
            } else {
                items[i].style.display = 'list-item';
            }
        }
    }

    let alert_div = document.getElementById('alert_div');
    let remove_row = document.querySelectorAll('.remove-link');
    remove_row.forEach(row => row.addEventListener('click', function () {
        const url = 'https://i.spey.ru/saas/shopbot_prod/update-settings.php?action=remove_cmenu&my_cmenu=';
        if (confirm('Вы действительно хотите удалить пункт меню?')) {
            let val = row.previousElementSibling.value;
            console.log(typeof(val));
            if (val === undefined) {
                val = row.previousElementSibling.previousElementSibling.value
            }
            console.log(val);
            fetch(url + '' + val, {mode: 'no-cors', method: 'get'})
                .then(response => response.status)
                .then(function (code) {
                    if (code === 200) {
                        row.parentElement.parentElement.remove();
                        alert_div.innerHTML = '' +
                            '<div class="uk-alert-success uk-text-center" uk-alert>\n' +
                            '    <a class="uk-alert-close" uk-close></a>\n' +
                            '    <p>Удалено.</p>\n' +
                            '</div>';
                    }
                })
                .catch(function (error) {
                    alert_div.innerHTML = '' +
                        '<div class="uk-alert-danger uk-animation-fade" uk-alert >\n' +
                        '    <a class="uk-alert-close" uk-close></a>\n' +
                        '    <p>Проблемы с актиацией бота. Напишите на <a href="mailto:info@ciframe.com">info@ciframe.com</a></p>\n' +
                        '</div>';
                    console.log('wow: ' + error.message)
                });
        }
    }));

    let select_menu_row = document.getElementById('add_row_c_menu');
    select_menu_row.addEventListener('change', function () {
        let c_menu_url = document.getElementById('c_menu_url');
        let c_menu_cat = document.getElementById('c_menu_cat');
        let c_menu_text = document.getElementById('c_menu_text');
        if (this.value === 'url') {
            c_menu_url.classList.remove('uk-hidden');
            c_menu_cat.classList.add('uk-hidden');
            c_menu_text.classList.add('uk-hidden');
        }
        if (this.value === 'menu') {
            c_menu_url.classList.add('uk-hidden');
            c_menu_cat.classList.add('uk-hidden');
            c_menu_text.classList.add('uk-hidden');
        }

        if (this.value === 'cat') {
            c_menu_cat.classList.remove('uk-hidden');
            c_menu_url.classList.add('uk-hidden');
            c_menu_text.classList.add('uk-hidden')
        }


        if (this.value === 'text') {
            c_menu_url.classList.add('uk-hidden');
            c_menu_cat.classList.add('uk-hidden');
            c_menu_text.classList.remove('uk-hidden')
        }
    });

}

var h = -1;
var win = null;

function sendExpand() {
    if (typeof win != 'undefined' && win && document.body.scrollHeight !== h) {
        let section_h = document.getElementsByTagName("section")[0].scrollHeight;
        if (!section_h)
            section_h = document.getElementsByTagName("body")[0].scrollHeight;
        h = 120 + section_h;
        const sendObject = {
            height: h,
            width: 800
        };
        win.postMessage(sendObject, '*');
    }
}

function activateHook(url, alert_div) {
    fetch(url, {
        mode: 'no-cors',
        method: 'get'
    })
        .then(response => response.status)
        // .then(contents => console.log(contents))
        .then(function (code) {
            console.log(code);
            if (code === 200) {
                console.log(typeof (code));
                document.getElementById('spinner').remove();
                alert_div.innerHTML = '' +
                    '<div class="uk-alert-success" uk-alert>\n' +
                    '    <a class="uk-alert-close" uk-close></a>\n' +
                    '    <p>Бот активирован успешно.</p>\n' +
                    '</div>';
            }
        })
        .catch(function (error) {
            document.getElementById('spinner').remove();
            alert_div.innerHTML = '' +
                '<div class="uk-alert-danger uk-animation-fade" uk-alert >\n' +
                '    <a class="uk-alert-close" uk-close></a>\n' +
                '    <p>Проблемы с актиацией бота. Напишите на <a href="mailto:info@ciframe.com">info@ciframe.com</a></p>\n' +
                '</div>';
            console.log('wow: ' + error.message)
        });
}

function loadIndex(url, alert_div) {
    fetch(url, {
        mode: 'no-cors',
        method: 'get'
    })
        .then(response => response.text())
        .then(function (response) {
            document.body.innerHTML = response;
        })
        .catch(function (error) {
            document.getElementById('spinner').remove();
            alert_div.innerHTML = '' +
                '<div class="uk-alert-danger uk-animation-fade" uk-alert >\n' +
                '    <a class="uk-alert-close" uk-close></a>\n' +
                '    <p>Проблемы с актиацией бота. Напишите на <a href="mailto:info@ciframe.com">info@ciframe.com</a></p>\n' +
                '</div>';
            console.log('wow: ' + error.message)
        });
}


window.addEventListener('load', function () {
    win = parent;
    sendExpand();
});
setInterval(sendExpand, 250);
