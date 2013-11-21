/**
 * oasys.jquery.js
 * The file contains common client-side functions for the DreamFactory Oasys(tm) Example Code
 */
/**
 * Our global options
 */
var _options = {
	alertHideDelay:      5000,
	notifyDiv:           'div#request-message',
	ajaxMessageFadeTime: 6000,
	scrollPane:          null,
	defaultUri:          '/rest/system/user',
	//	Set in index.php
	providers:           {},
	baseUrl:             null
};

/**
 * Check if a var is defined and return default value if not optionally
 *
 * @param variable
 * @param [defaultValue]
 * @returns {*}
 * @private
 */
var _isDefined = function(variable, defaultValue) {
	if (typeof variable != 'undefined') {
		return variable;
	}

	if (typeof defaultValue != 'undefined') {
		return defaultValue;
	}

	//	Nope, not defined
	return false;
};

/**
 * A little URL builder
 * @param resource
 * @param [appName]
 * @returns {string}
 * @private
 */
var _getEndpoint = function(resource, appName) {
	return _options.baseUrl + resource + '?app_name=' + _isDefined(appName, 'oasys-examples');
};

/**
 * A System URL builder
 * @param resource
 * @param [appName]
 * @returns {string}
 * @private
 */
var _getSystemEndpoint = function(resource, appName) {
	return _getEndpoint('/rest/system/' + resource, appName);
};

/**
 * A Portal URL builder
 * @param portal
 * @param [appName]
 * @returns {string}
 * @private
 */
var _getPortalEndpoint = function(portal, appName) {
	return _getEndpoint('/rest/portal/' + portal, appName);
};

/**
 * Load the provider stuff
 * @param [provider]
 * @private
 */
var _loadProvider = function(provider) {
	var $_list = $('#provider-list'), _userEndpoint = _getSystemEndpoint('provider_user'), _providerEndpoint = _getSystemEndpoint('provider');
	var _filter = 'user_id = :user_id AND provider_id = ' + $_list.find('option').filter(':selected').data('provider-id');
	var _providerName = _isDefined(provider, $_list.val());

	//	If we get the object, just pull out the name
	if (typeof _providerName == 'object') {
		_providerName = _providerName.api_name;
	}

	//	Fill in the request form
	$('#request-app').val('oasys-examples');
	$('#request-uri').val(_getPortalEndpoint(_providerName));
	$('#request-method').val('GET');
	$('#loading-indicator').hide().removeClass('fa-spin');
	$('#example-code').html('<small>Ready</small>');

	//	Disable controls
	$_list.addClass('disabled');
	$('#provider-auth-status').hide(function() {
		$('#provider-auth-check').show();
	});

	//	Pull the credentials
	$.ajax({
		url:      _userEndpoint,
		data:     {
			filter: _filter
		},
		complete: function() {
			//	Restore controls
			$('#provider-auth-check').hide();
			$_list.removeClass('disabled');
		},
		success:  function(data) {
			if (data && data.record && data.record.length) {
				var _provider = data.record[0];

				if (_provider.auth_text && _provider.auth_text.hasOwnProperty('access_token')) {
					//	Authorized already
					$('#provider-auth-status').html('<i class="fa fa-check btn-success"></i><small>Authorization granted</small>').show();
				}
				else {
					//	Need to authorize...
					$.ajax({
						async:   false,
						url: _getPortalEndpoint(_providerName) + '&control=authorize_url',
						type:    'GET',
						error:   function(error) {
							$('#provider-auth-status').html('<i class="fa fa-times btn-danger"></i><small>Authorization required, but there was an error retrieving the authorization URL.</small>').show();
						},
						success: function(data) {
							if (data && data.authorize_url) {
								_showResults('<h3>Authorization Required</h3><p>Please click <a href="' + data.authorize_url +
											 '" target="_blank">here</a> to authorize this provider.</p>', false);

								$('#provider-auth-status').html('<i class="fa fa-times btn-danger"></i><small>Authorization required. Click <a href="' +
																data.authorize_url + '" target="_blank">here</a> to begin the process.</small>').show();
							}
						}
					});
				}
			}
		}
	});
};

/**
 * Shows the results pretty-printed
 * @param data
 * @param [pretty]
 * @returns {boolean}
 * @private
 */
var _showResults = function(data, pretty) {
	if (false === pretty) {
		$('#example-code').html(data);
	}
	else {
		$('#example-code').html('<pre class="prettyprint">' + JSON.stringify(data, null, '\t') + '</pre>');

		//noinspection JSUnresolvedFunction
		PR.prettyPrint();
	}

	if (-1 == window.location.href.indexOf('#')) {
		window.location.href += '#provider-results';
	}

	return true;
};

/**
 * Call the local DSP
 * @param method
 * @returns {*}
 * @private
 */
var _actions = function(method) {
	if (_options.actions && _options.actions[method]) {
		var _args = [];

		if (arguments.length) {
			Array.prototype.push.apply(_args, arguments);
			_args.shift();
		}
		return _options.actions[method].apply(null, _args);
	}

	throw 'Invalid method "' + method + '"';
};

/**
 * Runs the API call
 * @private
 */
var _execute = function() {
	var _method = $('#request-method').val(), _xMethod = $('#request-x-method').val();
	var _uri = $('#request-uri').val(), _folder = $('#request-x-folder-name').val();
	var _app = $('#request-x-app-name').val() || 'oasys';
	var _raw = $('#request-body').val();
	var $_code = $('#example-code');

	if (!_uri || !_uri.length) {
		alert('Invalid Request URI specified.');
		return false;
	}

	_uri += ( -1 == _uri.indexOf('?') ? '?' : '&') + 'flow_type=1';

	$_code.empty().html('<small>Loading...</small>');

	try {
		var _body = null;

		if (_raw.length) {
			_body = JSON.stringify(JSON.parse(_raw));
		}

		$.ajax({
			url:         _uri,
			async:       true,
			type:        _method,
			dataType:    'json',
			cache:       false,
			processData: false,
			data:        _body,
			beforeSend:  function(xhr) {
				$('#loading-indicator').addClass('fa-spin').show();
				$('#send-request').addClass('disabled');

				if (_xMethod) {
					xhr.setRequestHeader('X-HTTP-Method', _xMethod);
				}
				if (_folder) {
					xhr.setRequestHeader('X-Folder-Name', _folder);
				}
				if (_app) {
					xhr.setRequestHeader('X-DreamFactory-Application-Name', _app);
				}
			},
			success:     function(data) {
				return _showResults(data);
			},
			error:       function(err) {
				if (302 == err.status || 307 == err.status) {
					$_code.empty().html('<h4>Authorization Required</h4><p>Please click the link below to authorize this provider.</p><p>' + err.location +
										'</p>');
				}
				else if (err.responseText) {
					var _json = JSON.parse(err.responseText);
					if (!_json) {
						_json = err.responseText;
					}
					_showResults(_json);
				}
				else {
					_showResults('Error: ' + err.status);
				}
			},
			complete:    function() {
				$('#send-request').removeClass('disabled');
				$('#loading-indicator').fadeOut().removeClass('fa-spin');
			}
		});
	}
	catch (_ex) {
		$_code.html(' >> ' + _ex);
	}

	return false;
};

/**
 * Initialize the app
 * @private
 */
var _initialize = function() {
	if (!_options.actions) {
		_options.actions = window.parent.Actions;
		_options.config = window.parent.Config;
	}

	//	Load providers
	_loadProvider();
};

/**
 * Initialize any buttons and set fieldset menu classes
 */
jQuery(function($) {
	//	Initialize...
	_initialize();

	$('body').on('open.dsp reopen.dsp',function() {
		_actions('toggleFullScreen', true);
	}).on('close.dsp', function() {
			_actions('toggleFullScreen', false);
		});

	$('.multientry').multientry({
		label:       'Header(s)',
		formId:      'call-settings-form',
		placeholder: 'Header (i.e. &quot;Content-Type: application/json&quot;)'
	});

	$('a.example-code').on('click', function(e) {
		e.preventDefault();
		var _which = $(this).data('provider');

		if (_which) {
			$('div#example-code').load('salesforce.html');
		}
	});

	//	Close the app
	$('#app-close').on('click', function(e) {
		e.preventDefault();
		if (window.parent && window.parent.Actions) {
			window.parent.Actions.showAdmin();
			window.parent.Actions.toggleFullScreen(false);
		}
	});

	$('#add-provider').on('click', function(e) {
		e.preventDefault();
		if (!$(this).hasClass('disabled')) {
			$('#select-provider').slideUp();
			$('#new-provider').slideDown();
			$('#add-provider').addClass('disabled');
		}
	});

	$('#add-provider-cancel').on('click', function(e) {
		e.preventDefault();
		$('#select-provider').slideDown();
		$('#new-provider').slideUp();
		$('#add-provider').removeClass('disabled');
	});

	$('#send-request').on('click', function(e) {
		e.preventDefault();
		_execute();
	});

	$('#reset-request').on('click', function(e) {
		e.preventDefault();
		$('#request-uri').val(_options.baseUrl + _options.defaultUri);
		$('#request-method').val('GET');
		$('#request-app').val('admin');
		$('#example-code').html('<small>Ready</small>');
		$('#loading-indicator').hide().removeClass('fa-spin');
	});

	$('#provider-list').on('change', function() {
		var _id = $(this).val();

		if (_options.providers && _options.providers.hasOwnProperty(_id)) {
			_loadProvider(_options.providers[_id]);
		}
	});

	/**
	 * Clear any alerts after configured time
	 */
	if (_options.alertHideDelay) {
		window.setTimeout(function() {
			$('div.alert').not('.alert-fixed').fadeTo(500, 0).slideUp(500, function() {
				$(this).remove();
			});
		}, _options.alertHideDelay);
	}
});
