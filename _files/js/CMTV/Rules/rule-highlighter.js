var CMTV_Rules = window.CMTV_Rules || {};

(function ($, window)
{
    "use strict";

    CMTV_Rules.Rule_Copy = new Clipboard('.rule .rule-link', {
        text: function (a)
        {
            return window.location.href.split("#")[0] + $(a).attr('href');
        }
    });

    CMTV_Rules.Rule_Copy.on('success', function (a)
    {
        a.clearSelection();
        XF.flashMessage(XF.phrase('CMTV_Rules_rule_link_copied_to_clipboard'), 1500);
    });

    $(function() {
        if (location.hash)
        {
            toggleRule();
        }
    });

    $(window).on('hashchange', function() {
        toggleRule();
    });

    function toggleRule()
    {
        var hash = location.hash;

        $('.rule').removeClass('rule--selected');

        if (!$(hash).hasClass('rule-anchor'))
        {
            return;
        }

        $(hash).parent().addClass('rule--selected');
    }
})
(jQuery, window);