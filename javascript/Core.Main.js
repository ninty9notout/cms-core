/**
 * File: Core.Main.js
 */
(function($) {
	$.entwine('ss', function($) {
		/**
		 * Class: .cms-edit-form #CanCommentType
		 * 
		 * Toggle display of group dropdown in "access" tab,
		 * based on selection of radiobuttons.
		 */
		$('.cms-edit-form #CanCommentType').entwine({
			onmatch: function() {
				var dropdown = $('#CommenterGroups');
		
				this.find('.optionset :input').bind('change', function(e) {
					var wrapper = $(this).closest('.middleColumn').parent('div');
					if(e.target.value == 'OnlyTheseUsers') {
						wrapper.addClass('remove-splitter');
						dropdown['show']();
					} else {
						wrapper.removeClass('remove-splitter');
						dropdown['hide']();	
					}
				});
		
				var currentVal = this.find('input[name=' + this.attr('id') + ']:checked').val();
				dropdown[currentVal == 'OnlyTheseUsers' ? 'show' : 'hide']();

				this._super();
			},
			onunmatch: function() {
				this._super();
			}
		});
	});
}(jQuery));