// LowEndPing
// ++++++++++++++++++++++++++++++++++++++++++

var totalQueries = -1;

var conn = false;

var onData = function(topic, data) {
	var $respdiv = $('#server_' + data.server_id + '_response');
	$respdiv.find('div.progress').remove();
	$respdiv.html('<div class="response"><pre style="text-align: left;">' + data.data + '</pre></div>');
	totalQueries--;
	
	if (totalQueries < 1) {
		$('#resultcontainer').data('queryid', false);
		$('input[type="submit"]').button('complete');
		totalQueries = -1;
		
		conn.unsubscribe(data.query_id.toString());
	}
};

var loading = '\
<div class="progress progress-striped active">\
	  <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>\
</div>';

var validateRegex = new RegExp('^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^(?:(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-fA-F]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,1}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,2}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:(?:[0-9a-fA-F]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,3}(?:(?:[0-9a-fA-F]{1,4})))?::(?:(?:[0-9a-fA-F]{1,4})):)(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,4}(?:(?:[0-9a-fA-F]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,5}(?:(?:[0-9a-fA-F]{1,4})))?::)(?:(?:[0-9a-fA-F]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-fA-F]{1,4})):){0,6}(?:(?:[0-9a-fA-F]{1,4})))?::))))$');

$(document).ready(function() {
	$("#serverlink").click(function(e) {
		e.preventDefault();
		$("#servers").slideToggle();
	});

	$('#mastercheck').change(function(e) {
		$('input:checkbox').prop('checked', $(this).is(":checked"));
	});

	$('input:checkbox').change(function(e) {
		var checkbox = $("#mastercheck");
		if (checkbox.is(":checked") && !$(this).is(":checked")) {
			checkbox.attr('checked', false);
		}
	});

	$('#queryform').submit(function(e) {
		e.preventDefault();

		var query = $('input[name=query]').val();

		if (!validateRegex.test(query)) {
			alert("Please enter a valid hostname/ip address");
			return;
		}
		
		if ($('input:checkbox:checked').length < 1) {
			alert('Please select one or more servers.');
			return;
		}

		$("#servers").slideUp();

		$('input[type="submit"]').button('loading');
		
		$.post('/submit', $(this).serialize(), function(res) {
			if (res.success) {
				$('#resultcontainer').resetResponses();
				
				$('#resultcontainer').data('queryid', res.queryid);
				
				if (res.resultLink) {
					$('#resultLink').attr('href', res.resultLink).parent().parent().removeClass('hidden');
				}
				
				totalQueries = res.serverCount;
				
				if (res.websocket && window.WebSocket) {
					if (!conn) {
						conn = new ab.Session(
							res.websocket,
							function() {
								console.log('WebSocket connected.');
								conn.subscribe(res.queryid.toString(), onData);
							}, function() {
								conn = false;
								console.log('WebSocket closed.');
								if (totalQueries > 0) {
									console.log('Falling back to HTTP requests.');
									$('#resultcontainer').status();
								}
							}, {
								'skipSubprotocolCheck': true
							}
						);
					} else {
						conn.subscribe(res.queryid.toString(), onData);
					}
				} else {
					$('#resultcontainer').status();
				}
			} else {
				$('input[type="submit"]').button('complete');
				alert('Error: ' + res.error);
			}
		}, 'json');
	});

	$.fn.extend({
		// Resets the response containers before a new query, adding the loading
		// bar.
		resetResponses : function(options) {
			var obj = $(this);

			obj.children().each(function(index) {
				$(this).find('div.response').remove();
				$(this).addClass('hidden');
			});

			$("input:checked").each(function(index) {
				var id = $(this).attr("name");
				
				if (id == undefined)
					return;

				id = id.match(/servers\[(\d+)\]/)[1];

				$('#server_' + id).removeClass('hidden');
				$('#server_' + id + '_response').html(loading);
			});

			obj.slideDown('slow');
		},

		status : function(options) {
			var currentQuery = $(this).data('queryid');
			if (currentQuery == undefined || totalQueries == -1) {
				return;
			}
			var obj = $(this);
			$.get('/update/' + currentQuery, function(data) {
				for (idx in data) {
					var response = data[idx];
					var $respdiv = $('#server_' + response.id + '_response');
					$respdiv.find('div.progress').remove();
					$respdiv.html('<div class="response"><pre style="text-align: left;">' + response.data + '</pre></div>');
					totalQueries--;
				}
				if (totalQueries > 0) {
					setTimeout(function() {
						obj.status();
					}, 2000);
				} else {
					obj.data('queryid', false);
					$('input[type="submit"]').button('complete');
					totalQueries = -1;
				}
			}, 'json');
		}
	});

});