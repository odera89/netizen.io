jQuery(document).ready(function($){
    
    //SEARCH MENU
    $("#oqp-search-menu input[type=text],#oqp-search-menu select").change(function(){
        //$(this).css("background-color","#FFFFCC");
        if($(this).val()){
            $(this).addClass("selected");
        }else{
             $(this).removeClass("selected");
        }
    }); 
    

        

        //switch preview/form
        $('.oqp-form-field.editable.completed .preview').show();
        $('.oqp-form-field.editable.completed .form').hide();
        //display edit links
        $('.completed .edit_action').show();
        $('.completed .edit_action').click(function(){
            var parent = $(this).parents('.oqp-form-field:first');
            var preview = parent.find('.preview');
            var form = parent.find('.form');
            
            console.log("clck");
             console.log(parent);
            
            if(form.is(':visible') == false){
                preview.hide();
                form.slideDown("fast");
            }else{
                preview.show();
                form.slideUp("fast");
                
            }
            
        });
        
        
        //ADD TAXONOMY TERM
        var taxonomy_fields=$('.field-taxonomy.editable');
        
        taxonomy_fields.each(function(index, value) {
            var taxonomy_radios=$(this).find('input:radio');
            
            var add_term_option=$(this).find('.oqp-add-term');
            
            if(!add_term_option) return;
            
            var add_term_radio=add_term_option.find('input:radio');
            
            var add_term_childof=add_term_option.find(".oqp-add-term-childof");
            
            if(!add_term_childof.is(':checked')) add_term_childof.hide();
            
            
            taxonomy_radios.change(function(){
                var add_term_checked = taxonomy_fields.find('.oqp-add-term input:radio:checked');
                if(add_term_checked.length>0){
                    add_term_childof.show("fast");
                }else{
                    add_term_childof.hide();
                }
            });

            
        });

    
	//EXCERPTS
	var header_excerpt=$('#item-header-content .entry-summary');
	var header_excerpt_backup = header_excerpt.html();
	
	//hide excerpt if previous field is DESCRIPTION + edition link
	$('.oqp-form-field.editable.field-excerpt').each(function(i) {
		var previous_field = $(this).prev();
		if (previous_field.hasClass('field-section')){
			var excerpt_field=$(this);
			excerpt_field.hide();
			
			var excerpt_link=$('<a href="#" class="add-excerpt-link">'+oqp.excerpt_link_text+'</a>')
			excerpt_link.appendTo(previous_field);
			
			excerpt_link.click(function() {
				excerpt_field.slideToggle('slow')
                                excerpt_link.hide();
				return false;
			});
		}
	});



	/*
	$('.oqp_block .editable').hide();
	$('.oqp_block .field-info').show();
	$('.oqp_block .field-info').click(function(){
		var editable_div = $(this).next('.editable');
		var editable_field = editable_div.find(':input');
		editable_div.toggle();
		$(this).toggle();
		editable_field.focus();
	 });
	
	$('.oqp_block .editable').focusout(function() {
		var field_info = $(this).prev('.field-info');
		field_info.toggle();
		$(this).toggle();
	});
	*/
	 
	 
	/*
	//checkbox-tree
	if ($("#yclads_advanced_search ul ul").length>0) {

		$("#yclads_advanced_search ul ul").collapsibleCheckboxTree({
			  // When checking a box, all parents are checked (Default: true)
				   checkParents : false,
			  // When checking a box, all children are checked (Default: false)
				   checkChildren : false,
			  // When unchecking a box, all children are unchecked (Default: true)
				   uncheckChildren : true,
			  // 'expand' (fully expanded), 'collapse' (fully collapsed) or 'default'
				   initialState : 'default'

		 });
	}

	
	*/
});

