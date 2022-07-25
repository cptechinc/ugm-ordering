$(function() {
	var form_additem = $('#add-item-form');
	var input_itemid = form_additem.find('input[name=itemID]');
	var input_qty = form_additem.find('input[name=qty]');

	form_additem.validate({
		submitHandler: function(form) {
			if (form_additem[0].hasAttribute("data-validated")) {
				if (form_additem.attr('data-validated') == input_itemid.val()) {
					if (input_qty.val() == '') {
						input_qty.addClass('is-invalid').focus();
					} else {
						form.submit();
					}
				} else {
					lookup_item();
				}
			} else {
				lookup_item();
			}
		}
	});

	$('.item-lookup-result').on('click', function (e) {
		e.preventDefault();

		var button = $(this);
		var itemID = button.data('itemid');
		form_additem.attr('data-validated', itemID);
		input_itemid.val(itemID);
		input_qty.focus();
	});

	function lookup_item() {
		var exists_url = URI("{{ page.url_itemjson }}");
		exists_url.addQuery('action', 'validate-itemid');
		exists_url.addQuery('itemID', input_itemid.val());

		/**
		 * Example Response
		 * {
		 * 	'exists' => true,
		 *  'itemID' => $item->itemid
		 * }
		 */
		$.getJSON(exists_url.toString(), function( response ) {

			if (response.exists) {
				form_additem.attr('data-validated', response.itemID);
				input_itemid.addClass('is-valid');

				if (response.itemID != input_itemid.val()) {
					input_itemid.val(response.itemID);
				}

				var description_url = URI("{{ page.url_itemjson }}");
				description_url.addQuery('itemID', input_itemid.val());

				$.getJSON(description_url.toString(), function( item ) {
					console.log(item);
					console.log(description_url.toString());
					if (item.exists) {
						$('small.desc1').text(item.item.description);
						$('small.desc2').text(item.item.description2);
					}
				});

				if (input_qty.val() == '') {
					input_qty.focus();
				} else {
					//form_additem.submit();
				}
			} else {
				swal2.fire({
					title: 'Item not found.',
					text: input_itemid.val() + ' cannot be found.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Make advanced search?'
				}).then(function (result) {
					if (result.value) {
						var url = URI("{{ page.url }}");
						url.addQuery('q', input_itemid.val());
						window.location.replace(url.toString());
					}
				});
			}
		});
	}
});
