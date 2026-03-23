jQuery(function() {
    var $sortable=jQuery("#tblSortable .table-body");
    $sortable.sortable({
        tolerance:'intersect'
        , cursor:'move'
        , items:'tr'
        , placeholder:'ui-state-highlight'
        , nested: 'tbody'
        , update: function(event, ui) {
            var orders=$sortable.sortable('serialize');
            var data={action: 'TCMP_changeOrder', nonce: ajax_vars.nonce, order: orders};
            jQuery.post(ajaxurl, data, function(result) {
                console.log(result);
            });
        }
    });
    $sortable.disableSelection();
});

!function(d,s,id){
    var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
    if(!d.getElementById(id)){
        js=d.createElement(s);
        js.id=id;
        js.src=p+'://platform.twitter.com/widgets.js';
        fjs.parentNode.insertBefore(js,fjs);
    }
}(document, 'script', 'twitter-wjs');

jQuery(function() {
    var href = jQuery("#tcmpRedirect").attr("href") || [];
    if (href.length > 0) {
        window.location.replace(href);
    }
});
