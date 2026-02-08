"use strict";
!function() {
    initMultiSelect();
}();
function initMultiSelect(queryClass = '.bs-multiselect') {
    document.querySelectorAll(queryClass).forEach((el, index) => {
		const placeholder = $(el).data('placeholder');
		const disable = $(el).data('disabled');
		
		$(el).multiselect({
		  nonSelectedText: placeholder,
		  includeSelectAllOption: true,
		  enableFiltering: false,
		  buttonWidth: '99%',
		  maxHeight: '30px',
		  numberDisplayed: 1
		});

		if (disable) {
		  $(el).multiselect('disable');
		}
  });
}
