(function ($) {
	var config = {};

	$.fn.makeRuleEditor = function (options) {
		var self = this;

		$.fn.makeRuleEditor.opts = $.extend(
			{},
			$.fn.makeRuleEditor.defaults,
			options,
		);

		var ul = $(this).find('ul');

		ul.find('li').each(function (index, val) {
			addRow(self, val);
		});

		var newA = $(
			'<a href="#" class="ruleadd button-secondary">Add Rule</a>',
		);
		newA.on('click', function (e) {
			addRow(self);
			e.preventDefault();
		});

		this.append(ul).append($('<span></span>').append(newA));

		ul.sortable();

		return this;
	};

	function addRow(obj, existing_li) {
		var group = '';
		var role = '';

		li = $(existing_li);
		if (existing_li) {
			group = li.data('group');
			role = li.data('role');

			li.data('group', '');
			li.data('role', '');
		} else {
			li = $('<li></li>', {
				class: 'ui-state-default ui-sortable-handle',
			});
		}

		var options_name = $.fn.makeRuleEditor.opts['options_name'];

		var groupselect = $('<input></input>', {
			class: 'groupslist',
			type: 'text',
			value: group,
			name: options_name + '[groups][]',
		});
		var roleselect = $('<select></select>', {
			class: 'roleslist',
			name: options_name + '[roles][]',
		});

		// On first pass, fetch roles list from the default role dropdown
		if (!$.fn.makeRuleEditor.roleslist) {
			copyRolesListFromSelect(
				$.fn.makeRuleEditor.opts['roleslist_selector'],
			);
		}
		var roleslist = $.fn.makeRuleEditor.roleslist;
		fillSelect(roleselect, roleslist, role);

		var mainspan = $('<span></span>', { class: 'ruleedit-mainspan' })
			.append('If user is in Google Group / OrgUnit')
			.append(groupselect)
			.append(' then change role to ')
			.append(roleselect);

		var newrow = $(li)
			.append(
				$('<span></span>', {
					class: 'ui-icon ui-icon-arrowthick-2-n-s',
				}),
			)
			.append(mainspan);

		var deletebtn = $('<a href="#" class="ruledelete">Delete</a>').on(
			'click',
			function (e) {
				newrow.remove();
				e.preventDefault();
			},
		);

		newrow.append(deletebtn);

		if (!existing_li) {
			$(obj).find('ul').append(newrow);
		}
	}

	function fillSelect(selectobj, list, curval) {
		var curvalexists = false;
		$.each(list, function (index, value) {
			var attrs = { value: value[0] };
			if (curval == value[0]) {
				attrs.selected = 'selected';
				curvalexists = true;
			}
			selectobj.append(
				$('<option></option>', attrs).append(
					document.createTextNode(value[1]),
				),
			);
		});
		if (!curvalexists && curval != '' && curval != '_gal_superadmin') {
			// selected value doesn't exist - perhaps has been deleted
			selectobj.append(
				$('<option></option>', {
					value: curval,
					selected: 'selected',
				}).append(
					document.createTextNode('-- DELETED ROLE -- ' + curval),
				),
			);
		}
	}

	$.fn.makeRuleEditor.defaults = {
		ruleslist_selector: '',
		options_name: '',
	};

	var copyRolesListFromSelect = function (selector) {
		var sel = $(selector + ' option');
		var roleslist = $.map(sel, function (o, i) {
			return [[o.value, o.innerText]];
		});
		$.fn.makeRuleEditor.roleslist = roleslist;
	};
})(jQuery);
