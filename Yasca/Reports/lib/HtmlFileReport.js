"use strict";
var Yasca = Yasca || {};
if (!Date.now){
	Date.now = function(){
		return +(new Date());
	};
}
(function(self){
	var severities, sorting, createReport, supports, saveJsonReport, threadYieldTimeoutMillis;

	threadYieldTimeoutMillis = 60;

	supports = {};

	severities = ['','Critical','High','Medium','Low','Info'];
	sorting = {
		initial: function (a,b) {
			if (a.severity > b.severity) { return 1; }
			if (a.severity < b.severity) { return -1; }
			if (a.pluginName > b.pluginName) { return 1; }
			if (a.pluginName < b.pluginName) { return -1; }
			if (a.filename > b.filename) { return 1; }
			if (a.filename < b.filename) { return -1; }
			if (a.lineNumber > b.lineNumber) { return 1; }
			if (a.lineNumber < b.lineNumber) { return -1; }
			return 0;
		}
	};

	saveJsonReport = (function(){
		var saveAs, bb, features, message;

		if (  (	!((window.URL || window.webkitURL || {}).createObjectURL) &&
					!window.saveAs
				) ||

			!window.JSON || !window.JSON.stringify){

			features = [];
			if (!((window.URL || window.webkitURL || {}).createObjectURL)){
				features.push('File URL API');
			}
			if (!window.saveAs){
				features.push('Filesaver API');
			}
			if (!window.JSON || !window.JSON.stringify){
				features.push('JSON API');
			}
			message =
			  'These standard features are required, but missing:\n' +
			  '    ' + features.join(', ') + '\n' +
			  '\n' +
			  'Please open this report in a different browser, such as:\n' +
			  '    Internet Explorer 10 or newer\n' +
			  '    Firefox 9 or newer\n' +
			  '    Google Chrome';

			supports.save = false;

			return function(){ alert(message);};
		} else {
			saveAs =
				window.saveAs	  ||
				function(blob, filename){
					var a = document.createElement("a");
					document.body.appendChild(a);
					a.style = "display: none";
					var url = (window.URL || window.webkitURL).createObjectURL(blob);
					a.href = url;
					a.download = filename;
					a.click();
					(window.URL || window.webkitURL).revokeObjectURL(url);
				};

			supports.save = true;

			return function(){
				bb = new Blob([JSON.stringify(self.results)], {type: 'application/x-json'});
				saveAs(bb, 'results.json');
			};
		}
	}());

	createReport = function(results, resultTableId, done) {
		var scrollPos, index, length, processResult, reportContainer;

		reportContainer = $('body div#table-container');

		reportContainer.append(
			$('<table />')
			.addClass('table table-condensed')
			.attr('border', '0')
			.css('display', 'none')
			.attr("id", resultTableId)
			.append(
				$('<thead/>').append(
				    $('<th/>').text('#').css({'width': 0}),
					$('<th/>').text('Severity').css({'width': 0}),
					$('<th/>').text('Plugin').css({'width': 0}),
					$('<th/>').text('Category').css({'width': 0}),
					$('<th/>').text('Message'),
					$('<th/>').text('Details').css({'width': 0})
				)
			)
		);

		index = 0;
		length = results.length;
		processResult = function(){
			var result, table, detailsId, startTime, markdown;
			markdown = new Showdown.converter();
			if (index < length){
				table = $('#' + resultTableId);
				startTime = Date.now();
				while(index < length){
					result = results[index];
					detailsId = "d" + (index + 1);
					reportContainer.append(
						$('<div />')
						.addClass('detailsPanel')
						.css('display', 'none')
						.attr('severity', result.severity)
						.attr('id', detailsId)
						.append(
							$('<a/>')
								.addClass('btn btn-small backToResults')
								.attr('href', '#')
								.css('margin-bottom', '10px')
								.text('<< Back to Results'),
							(function(){
								var retval, dialog;
								if (!!result.filename){
									retval = result.filename;
									if (!!result.lineNumber){
										retval += ":" + result.lineNumber;
									}
									dialog = $('<div />')
										.addClass('alert')
										.append('<strong>Filename: </strong> ' +
											    retval);
									return dialog;
								} else {
									return '';
								}
							}()),
							$('<h4>Description</h4>'),
							$('<p/>').html(
							    $('<p/>')
							    .html(markdown.makeHtml(result.description))
							),
							$('<h4>References</h4>'),
							(function(){
							    var any, retval;
							    any = false;
							    retval =
                                    $('<ul/>')
									.attr('class', 'references')
									.append(
										$.map(result.references || {}, function(value, key){
										    any = true;
											return $('<a/>')
												.attr('target', '_blank')
												.attr('href', key)
												.text(value)
												.wrap('<li/>')
												.parent()
												.get();
										})
									);
							    if (any) {
							        return retval;
								} else {
									return '';
								}
							}()),
							$('<h4>Source Code</h4>'),
							(function(){
							    var any, retval;
							    any = false;
							    retval =
                                    $('<ul/>')
									.attr('class', 'unsafeData')
									.append(
										$.map(result.unsafeData || {}, function(value, key) {
											any = true;
											return $('<li/>')
												.text(key + ': ' + value)
												.get();
										})
									)
							    if (any) {
							        return retval;
								} else {
									return '';
								}
							}()),
							(function(){
							    var any, retval;
							    any = false;
							    retval =
                                    $('<pre />')
									.html(
										prettyPrintOne(
											$.map(result.unsafeSourceCode || {}, function(value, key) {
										    	any = true;
												return value.replace(/</g, '&lt;') + '<br/>';
											}).join('')
											,
											null,	/* language */
											true	/* line numbers */
										)
									);
							    if (any) {
							        return retval;
								} else {
									return '';
								}
							}())
						)
					);

					table.append(
						$('<tr/>')
						.attr('severity', result.severity)
						.append(
							$('<td/>').text(index + 1),
							$('<td/>').text(severities[result.severity]),
							$('<td/>').text(result.pluginName),
							$('<td/>').text(result.category),
							$('<td/>')
							.attr('class','ellipsis')
							.text(
								(function(){
									var maxFilenameLength, retval, cont;
									maxFilenameLength = 18;
									cont = '...';
									retval = '';
									if (!!result.filename){
										if (result.filename.length > maxFilenameLength){
											retval +=
												cont +
												result.filename.slice(
													cont.length - maxFilenameLength
												);
										} else {
											retval += result.filename;
										}
										if (!!result.lineNumber){
											retval += ':' + result.lineNumber;
										}
										retval += ' - ';
									}
									retval += result.message;
									return retval;
								}())
							),
							$('<td/>')
							.append(
							    $('<a />')
							    .addClass('btn btn-info btn-small')
							    .css('line-height', 'normal')
							    .attr('href','#')
							    .text('Details')
							    .data('detailsId', detailsId)
							)
						)
					);

					index += 1;

					if (Date.now() - startTime >= threadYieldTimeoutMillis){
						$('#loadingNum').text('' + (index + 1));
						break;
					}
				}
				setTimeout(processResult, 0);
			} else {
				$('#loading').empty().text('Loaded');
				setTimeout(done, 0);
			}
		};
		setTimeout(processResult, 0);
	};

	$(document).ready(function(){
	    setTimeout(function() {
	        var reportTableId, results;

	        reportTableId = 'resultsTable';
			results =
				(function(){
					var element, retval;
					element = $("#resultsJson");
					retval = $.parseJSON(element.text());
					element.remove();
					return retval;
				}());

			$('#saveJson').on('click', saveJsonReport);

			results.sort(sorting.initial);

			$('#loadingOf').text('' + results.length);

			createReport(results, reportTableId, function(){
				$('div.detailsPanel').each(function(index, item){
				    var panel;
                    panel = $(item);
                    panel.data('defaultHeight', panel.height());
				});

				(function(){
					var scrollPos = 0;

					$('div.detailsPanel a.backToResults').on('click', function(){
						$(this).parent().fadeOut('fast', function(){
							$(this).css('height', '');
							$('#' + reportTableId).fadeIn('fast', function () {
								$(window).scrollTop(scrollPos);
							});
						});
					});

				    $('#' + reportTableId + ' tr a').on('click', function(){
				    	var detailsId;
				    	detailsId = $(this).data('detailsId');
						scrollPos = $(window).scrollTop();
						$("#" + reportTableId).fadeOut('fast', function(){
							var details;
							details = $("#" + detailsId);
							details.css('height',
								Math.max(
									details.data('defaultHeight'),
								    $(window).height() - $('table.header:first').outerHeight() - 24
								)
							)
							.fadeIn('fast');
						});
					});
				}());

				$(window).resize(function(){
					var details;
					details = $('div.detailsPanel:visible');
					details.css('height',
						Math.max(
							details.data('defaultHeight'),
						    $(window).height() - $('table.header:first').outerHeight() - 24
						)
					);
				});

				if (supports.save === true){
					self.results = results;
				}

				$('#loading').delay(100).fadeOut('fast', function() {
				    var table;
					$(this).remove();
					table = $('#' + reportTableId);
					table.fadeIn('fast', function(){
						table.find('th:not(:contains("Message"))').each(function(){
							var self;
							self = $(this);
							self.css({'width': self.width()});
						});
						table.css({'table-layout': 'fixed'});
					});
				});
			});
		}, 0);
	});
}(Yasca));
