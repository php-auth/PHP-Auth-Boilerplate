/*
| ------------------------------------------------------------------------------
| JNavbar
| ------------------------------------------------------------------------------
*/
class JNavbar {

    /**
     * Class constructor
     *
     * @access public
     */
    constructor() {
        if ($(".navbar")[0]) {
            this.navbar = Math.round($(".navbar").outerHeight(true));
            if ($(".navbar.fixed-top")[0]) {
                $("body").css("padding-top", this.navbar + "px");
            }
            // Close Navbar when clicked outside
            $_["document"].on("click", function (event) {
                if ($(".navbar .navbar-toggler").attr("aria-expanded") == "true"
                    && $(event.target).closest(".navbar").length === 0) {
                    $('button[aria-expanded="true"]').click();
                }
            });
            if ($("nav.navbar-effect")[0]) {
                $(".custom-toggler").on("click", e => this.effect(e));
                window.addEventListener("scroll", e => this.effect(e));
            }
        }
    }

    /**
     * Change the background - dark to light & light to dark
     *
     * @param {Object} event
     * @access public
     */
    effect(event) {
        var scroll   = window.scrollY > this.navbar ? true : false;
        var navbar   = $("#navbarSupportedContent");
        var navdark  = $("nav.navbar-dark");
        var navlight = $("nav.navbar-light");
        if ((navdark[0] && scroll) ||
            (navbar.is(":visible") && navbar.width() < 992 && navdark[0])) {
            $(".light-brand").addClass("d-none");
            navdark.addClass("navbar-light");
            navdark.addClass("bg-white");
            navdark.removeClass("bg-dark-5");
            navdark.removeClass("navbar-dark");
            $(".dark-brand").removeClass("d-none");
        } else if ((!scroll && !navbar.is(":visible")) ||
                   (!scroll && event.type === "click") ||
                   (!scroll && navbar.width() >= 992)) {
            $(".dark-brand").addClass("d-none");
            navlight.addClass("navbar-dark");
            navlight.addClass("bg-dark-5");
            navlight.removeClass("bg-white");
            navlight.removeClass("navbar-light");
            $(".light-brand").removeClass("d-none");
        }
    }
}
