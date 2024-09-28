class JScript {

    /**
     * Class constructor
     *
     * @access public
     */
    constructor() {
        window.$_      = [];
        window.global  = window;
        global.type_of = obj => Object.prototype
                                      .toString
                                      .call(obj)
                                      .slice(8, -1)
                                      .toLowerCase();
        if (!window["module"]) {
            global.module  = {};
            module.exports = null;
        }
    }

    /**
     * Initialization
     *
     * @access public
     */
    init() {
        $_["document"]  = new JDocument();
        $_["http"]      = new JHttp();
        $_["file"]      = new JFile();
        $_["language"]  = new JLanguage();
        $_["element"]   = new JElement();
        $_["event"]     = new JEvent();
        $_["time"]      = new JTime();
        $_["component"] = new JComponent();
        $_["document"].ready(this.main);
    }

    /**
     * Main
     *
     * @access public
     */
    main() {
        global.translate = text => $_["language"].translate(text);
        $_["component"].navbar();
        $("[onload]").trigger("onload");
    }
}
