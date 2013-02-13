jQuery(document).ready(function($){
    //make meta boxes reactive
    postboxes.add_postbox_toggles('oqp');
    
    //close meta boxes
    $('#oqp-form-fields .postbox').addClass("closed");
    
    //tabs
    $( "#oqp-admin-tabs" ).tabs();
    
    //hide extensions options if no checked
    var extensions_checkboxes = $('#oqp-form-extensions .oqp-extension input:checkbox.extension_enabled');
    
    extensions_checkboxes.each(function(index) {
        extension_toggle_options($(this));
        
        $(this).bind('change',function(){
            extension_toggle_options($(this));
        });
        
    });



    
    

	
});

function extension_toggle_options(ext){
    var options = ext.parents('.oqp-extension').find('.extension-options');

    if(ext.is(':checked')){
        options.show();
    }else{
        options.hide();
    }
    
}