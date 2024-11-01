(function ($) {
    pixlr.settings.exit = 'http://cc.axcoto.com/';
    pixlr.settings.credentials = true;
    pixlr.settings.method = 'get';
    
    $(document).ready(function () {
        $('.axcoto-pixlr-edit').click(function (e) {
            e.preventDefault();
            var s = $(this);
            pixlr.settings.target = s.attr('target');
            pixlr.overlay.show({
                'title' : s.attr('title'),
                'image': s.attr('href')
            });

        })
    })

})(jQuery)