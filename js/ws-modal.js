(function(){
    var window    = this,
        document  = window.document,
        $         = window.jQuery,
        container = $("#wpcontent");
    /**
     * 
     */
    function hide() {
        $('.ws-module-settings-container').hide();
    }
    /**
     * 
     */
    function show() {
        $('.ws-module-settings-container').removeClass("hidden");
        $('.ws-module-settings-container').show();
    }
    function load_content() {
        var button = $(this) || $(container).find('.nav-tab-wrapper').data("selected");
        var infos  = button.data();
        $.ajax({
            url: "wp-admin/admin.php",
            data: {
                page:     "weekly-schedule",
                modal:    true,
                settings: {},
                schedule: 1
            }
        });
    }
    /**
     * 
     */
    function update_nav_tab(event) {
        if (event) event.preventDefault();
        // init
        var button   = $(this),
            btn_data = button.data("nav");
        $(container).find('.ws-tab-content').hide().removeClass("nav-tab-active");
        $(container).find('.nav-tab').removeClass("nav-tab-active");
        // infos dans le contenu de la modal
        var selected = $(".nav-tab-wrapper").data("selected");
        if (selected === undefined || selected == "" ) selected = "general";
        if (btn_data !== undefined && btn_data != "" ) selected = btn_data;
        // Affichage des contenus de la modal :
        button.addClass("nav-tab-active");
        $(container).find('.ws-'+selected).show();
        $(container).find('.ws-nav-tab-'+selected).addClass("nav-tab-active");
        return false;
    }
    /**
     * INIT
     */
    if ( $("#posts-filter").data("selected") > 0 ) {
        update_nav_tab();
        show();
    }
    $(container).find('.nav-tab').off().on("click", update_nav_tab);
    $(container).find('.ws-module-settings-container .ws-close-modal').off().on('click', hide);
    $(container).find('.ws-module-settings-container .ws-load-modal' ).off().on('click', load_content);
    $(container).find('.ws-open-modal'                               ).off().on('click', show);
})();