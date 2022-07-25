$(function() {
	var form = $('#create-account-form');
	var input_new = form.find('input[name=new]');
	var input_confirm = form.find('input[name=confirm]');

	jQuery.validator.addMethod("confirm", function(value, element) {
		return this.optional(element) || compare_passwords();
	}, "Must Match New Password");

	var validator = form.validate({
		errorClass: "is-invalid",
		validClass: "is-valid",
		errorPlacement: function(error, element) {
			var jq = $(element);

			if (jq.closest('.input-group').length) {
				var parent = jq.closest('.input-group');
				error.insertAfter(parent).addClass('invalid-feedback');
			} else {
				error.insertAfter(element).addClass('invalid-feedback');
			}
		},
		rules: {
			password: {required: true},
			confirm: {confirm: true},
			maiden: {required: true},
			city: {required: true},
		},
		messages: {

		},
		submitHandler: function(form) {
			//form.submit();
		}
	});

	$("body").on('change', 'input[name=password]', function(e) {
		validator.element('#confirm');
	});

	function compare_passwords() {
		return input_new.val() == input_confirm.val();
	}
});
