/*
| ------------------------------------------------------------------------------
| JHttp
| ------------------------------------------------------------------------------
*/
class JHttp {

    /**
     * Class constructor
     *
     * Assign ajax via CSS class:
     *
     * <a class="ajax" href="/app-route"></a>
     * <form class="ajax" action="/app-route" return="my_function"></form>
     *
     * function my_function(error, response) {
     *     if (!error) {
     *         console.log(response.data);
     *     } else {
     *         console.log(response);
     *     }
     * }
     *
     * @access public
     */
    constructor() {
        var self = this;
        $(document).on("click", "a.ajax", function (e) {
            if ($(this).attr("data-ajax-href")) {
                $_['http'].ajax($(this).attr("href"),
                                $(this).attr("data-ajax-href"));
                return false;
            }
            if (e.target.href) {
                $_["http"].request(e.target.href, (error, response) => {
                    self.html(response.data);
                    $_["document"].resizeScreen();
                });
            }
            e.preventDefault();
        });
        $(document).on("submit", "form.ajax", function (e) {
            $_["http"].request(e.target.action, $(this).serialize(),
            (error, response) => {
                if ($(this).attr("data-ajax-return")) {
                    response.reset = () => $(this).trigger("reset");
                    window[$(this).attr("data-ajax-return")](error, response);
                } else {
                    self.html(response.data);
                    $_["document"].resizeScreen();
                }
            });
            e.preventDefault();
        });
    }

    /**
     * Insert HTML into the current document.
     *
     * html("<tags>");
     *
     * @param {String} data
     * @access private
     */
    html(data) {
        $_["document"].html(data);
        // Reason: Run scripts embedded in page after full load.
        $("body").html($("body").html());
        $JScript.main();
    }

    /**
     * HTTP request via GET with alias URL.
     *
     * <a class="ajax" href="/alias/route" data-ajax-href="/real/route">Link</a>
     *
     * ajax("href", "data-ajax-href");
     *
     * @param {String} alias
     * @param {String} url
     * @access private
     */
    ajax(alias, url) {
        $_["http"].request(url, (error, response) => {
            window.history.pushState({}, null, alias || url);
            this.html(response.data);
            $_["document"].resizeScreen();
            return false;
        });
    }

    /**
     * HTTP request via GET
     *
     * const url = "/my/route/here" or "http://domain.ext/query/..."
     * get(url, (error, response) => {
     *     if (!error) {
     *         return console.log(response);
     *     }
     *     console.log(error);
     * });
     *
     * get(url)
     * .then(response => console.log(response))
     * .catch(error => console.log(error));
     *
     * @param {String} url
     * @param {Function} callback
     * @access public
     */
    get(url, callback = null) {
        var options = {};
        try {
            const $url = new URL(url);
            options.baseURL = $url.protocol + "//" + $url.host
            url = url.split(options.baseURL)[1];
        } catch(e) {}
        return axios.create(options)
                    .get(url)
                    .then(response => callback ?
                                      callback(false, response.data) :
                                      response.data)
                    .catch(error => callback ?
                                    callback(true, error.response) :
                                    error.response);
    }

    /**
     * HTTP request - GET | POST
     *
     * => GET
     *
     * request("/app-route", (error, response) => {
     *     if (!error) {
     *         console.log(response.data);
     *     } else {
     *         console.log(response);
     *     }
     * });
     *
     * => POST
     *
     * request("/app-route", "Form data", (error, response) => {
     *     if (!error) {
     *         console.log(response.data);
     *     } else {
     *         console.log(response);
     *     }
     * });
     *
     * @param {String} url
     * @param {String} data
     * @param {Function} callback
     * @access public
     */
    request(url, data = null, callback = null) {
        let type     = "get",
            error    = false,
            response = {};
        if (type_of(data) === "function") {
            callback = data;
            data     = null;
        } else if (type_of(data) === "string") {
            type = "post";
        }
        axios.defaults.headers.common["Ajax"] = true;
        axios[type](url, data).then(res => {
            response = res;
        }).catch(error => {
            response = error.response;
            error    = true;
        }).finally(() => {
            callback(error, response);
        });
    }
}
