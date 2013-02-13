
jQuery(document).ready(function($){  
	//checkbox-tree
	if ($("ul.expandable").length>0) {
            
                //CHECKBOX TREE
            
		$("ul.expandable").collapsibleCheckboxTree({
			  // When checking a box, all parents are checked (Default: true)
				   checkParents : false,
			  // When checking a box, all children are checked (Default: false)
				   checkChildren : false,
			  // When unchecking a box, all children are unchecked (Default: true)
				   uncheckChildren : true,
			  // 'expand' (fully expanded), 'collapse' (fully collapsed) or 'default'
				   initialState : 'default'

		 });
                 
                 //SPLIT INTO COLUMNS
                 
                $('ul.expandable').each(function(index) {
                    var children = $(this).children('li');
                    var minimum_items = oqp.taxonomy_columns_max;

                        $(this).easyListSplitter({ colNumber: 6 });
                        
                        var container = $(this).parent();

                        container.addClass('columns-list');

                   
                });
                 
                 
	}

})
