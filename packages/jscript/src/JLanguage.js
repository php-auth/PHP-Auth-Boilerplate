/*
| ------------------------------------------------------------------------------
| JLanguage
| ------------------------------------------------------------------------------
*/
class JLanguage {

    /**
     * Class constructor
     *
     * @access public
     */
    constructor() {
        $(document).on("click", ".dropdown-language", e => {
            e.preventDefault();
            this.change(e.target.text.trim());
        });
    }

    /**
     * Return translation data via sessionStorage
     *
     * cache("language"); // cache("pt-BR");
     *
     * @param {String} language
     * @access private
     */
    cache(language) {
        return JSON.parse(sessionStorage.getItem(language)) || {};
    }

    /**
     * Return the translation
     *
     * translate("Text...");
     *
     * @param {String} text
     * @access public
     */
    translate(text) {
        var language = this.cache($_["document"].lang);
        return language[text] ? language[text] : text;
    }

    /**
     * Returns the translation of the route.
     *
     * translate_route({language: "pt-BR", path: "/path"});
     *
     * @param {Object} route
     * @access private
     */
    translate_route(route) {
        Object.prototype.getKey = function (value) {
            for (let key in this) {
                if (this[key] == value) {
                    return key;
                }
            }
            return "";
        };
        var translation = path => this.cache(route.language)[path] ||
                                  this.cache($_["document"].lang).getKey(path);
        var arr = route.path.split("/");
        var len = arr.length;
        if (arr[1]) {
            route.path = "";
            for (let i=0; i<=len; i++) {
                if (arr[i]) {
                    route.path += "/" + (translation(arr[i]) || arr[i]);
                }
            }
            return route.path;
        }
        return translation(route.path) || route.path;
    }

    /**
     * Redirects to the specified URL
     *
     * @param {String} url
     * @access private
     */
    redirect(url) {
        window.location.href = url;
        /*window.location.href = this.translate_route({
          language: language,
          path: window.location.pathname.substring(1)
        });*/
    }

    /**
     * Select the language
     *
     * @param {String} lang_code
     * @param {Function} callback
     * @access public
     */
    selectLanguage(lang_code, callback) {
        // $_GET['language'] - /framework/classes/Settings.php
        $_["http"].request("/?language=" + lang_code, (error, response) => {
            if (error) {
                console.log(error);
            }
            return callback();
        });
    }

    /**
     * Change language
     *
     * @param {String} lang_code
     * @access public
     */
    change(lang_code) {
        var data = JSON.parse(sessionStorage.getItem(lang_code)) || null;
        if (lang_code !== "en" && data === null) {
            this.selectLanguage(lang_code, () => {
                // $_GET['lang'] - /framework/classes/Language.php
                $_["http"].request("/?lang=" + lang_code, (error, response) => {
                    if (error) {
                        console.log(error);
                    }
                    sessionStorage.setItem(lang_code,
                                           JSON.stringify(response.data));
                    this.redirect("/");
                });
            });
        } else {
            this.selectLanguage(lang_code, () => this.redirect("/"));
        }
    }
}
