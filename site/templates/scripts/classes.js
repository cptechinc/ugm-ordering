class AjaxRequest {
	constructor(url = '') {
		this.url = url;
		this.method = 'GET';
		this.data = {};
	}

	setData(data) {
		this.data = data;
	}

	setMethod(method) {
		this.method = method;
	}

	request(callback) {
		$.ajax({
			url: this.url,
			method: this.method,
			beforeSend: function(xhr) {},
			data: this.data,
			success: function(json) {
				callback(json);
			},
			error: function(xhr){
			},
		});
	}
}
