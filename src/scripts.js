jQuery(function() {
    jQuery("input.atoggle").click(function() {
        var e = jQuery(this).data("atarget");
        var t = jQuery(e);
        var n = jQuery(this).data("astate");
        if (this.checked) {
            if (n == 1) {
                t.removeAttr("disabled");
            } else {
                t.attr("disabled", true);
            }
        } else {
            if (n === 0) {
                t.removeAttr("disabled");
            } else {
                t.attr("disabled", true);
            }
        }
    });
    if (!jQuery("#show_advanced").attr("checked")) {
        jQuery(".advanced_opt").fadeOut(166);
    }
    jQuery("#show_advanced").click(function() {
        jQuery(".advanced_opt").fadeToggle(166);
    });
    jQuery("input.only_once").click(function() {
        if (!jQuery("#add_graph").attr("checked")) {
            jQuery(".disable_graph").attr("disabled", true);
        }
    });
    jQuery('.select-all').click(function() {
        jQuery(this).closest('ul').find(':checkbox').attr('checked', this.checked);
    });
    if(jQuery().jqplot) {
      render_stats();
    }
});