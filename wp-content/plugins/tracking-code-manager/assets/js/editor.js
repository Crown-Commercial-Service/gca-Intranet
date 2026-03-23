
jQuery(function(){
    //enable/disable some part of except creating coherence
    function tcmCheckVisible() {
        var $mode=jQuery('[name=trackMode]:checked');
        var showTrackCode=false;
        var showTrackConversion=false;
        if($mode.length>0) {
            if(parseInt($mode.val())!=0) { // TCMP_TRACK_MODE_CODE
                showTrackConversion=true;
                jQuery('#position-box').hide();

                tcmShowHide('.box-track-conversion', false);
                tcmShowHide('#box-track-conversion-'+$mode.val(), true);
            } else {
                showTrackCode=true;
                jQuery('#position-box').show();
            }
        }
        tcmShowHide('#box-track-conversion', showTrackConversion);
        tcmShowHide('#box-track-code', showTrackCode);

        var $all=jQuery('[name=trackPage]:checked');
        if($all.length>0 && parseInt($all.val())==1) { // TCMP_TRACK_PAGE_SPECIFIC
            showExcept=false;
            jQuery('[type=checkbox]').each(function() {
                var $check=jQuery(this);
                var id=TCMP.attr($check, 'id', '');
                if(TCMP.startsWith(id, 'include')) {
                    var $select=id.replace('_Active', '');
                    $select=TCMP.jQuery($select);

                    isCheck=$check.is(':checked');
                    selection=$select.select2('val');
                    found=false;
                    for(i=0; i<selection.length; i++) {
                        if(parseInt(selection[i])==-1){
                            found=true;
                        }
                    }

                    var $except=id.replace('_Active', '');
                    $except=$except.replace('Active', '')+'Box';
                    $except=$except.substr('include'.length);
                    $except='except'+$except;
                    $except=jQuery('[id='+$except+']');

                    if(found) {
                        showExcept=true;
                        if($except.length>0) {
                            $except.show();
                        }
                    } else {
                        if($except.length>0) {
                            $except.hide();
                        }
                    }
                }
            });
        }

        showInclude=false;
        if($all.length==0) {
            showExcept=false;
        } else {
            if(parseInt($all.val())==0) { // TCMP_TRACK_PAGE_ALL
                showExcept=true;
            } else {
                showInclude=true;
            }
        }
        tcmShowHide('#tcmp-except-div', showExcept);
        tcmShowHide('#tcmp-include-div', showInclude);
    }
    function tcmShowHide(selector, show) {
        $selector=jQuery(selector);
        if(show) {
            $selector.show();
        } else {
            $selector.hide();
        }
    }

    jQuery('.tcmLineTags,.tcmp-dropdown').select2({
        placeholder: "Type here..."
        , theme: "classic"
        , width: '550px'
    });

    jQuery('.tcmp-hideShow').click(function() {
        tcmCheckVisible();
    });
    jQuery('.tcmp-hideShow, input[type=checkbox], input[type=radio]').change(function() {
        tcmCheckVisible();
    });
    jQuery('.tcmLineTags').on('change', function() {
        tcmCheckVisible();
    });
    tcmCheckVisible();
});

jQuery.noConflict()(function($){
    var text;
    try {
        text=$('#codeAce').html();
        text=TCMP.replace(text, '&lt;', '<');
        text=TCMP.replace(text, '&gt;', '>');
        text=TCMP.replace(text, '&amp;', '&');
        
        var ACE_code = ace.edit("codeAce");
        ACE_code.renderer.setShowGutter(false);
        ACE_code.setTheme("ace/theme/monokai");
        ACE_code.getSession().setMode("ace/mode/html");
        ACE_code.getSession().setUseSoftTabs(true);
        ACE_code.getSession().setUseWrapMode(true);
        ACE_code.session.setUseWorker(false)
        ACE_code.setValue(text);
        
        $('#codeAce').focusout(function() {
            var $hidden=$('#code');
            var code=ACE_code.getValue();
            $hidden.val(code);
        });
        $('#codeAce').trigger('focusout');
    } catch(e) {
        if (e) {
            return;
        }
    }
    });
