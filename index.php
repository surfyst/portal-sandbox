<?php
use DreamFactory\Platform\Utility\ResourceStore;
use DreamFactory\Platform\Yii\Models\App;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Curl;
use Kisma\Core\Utility\HtmlMarkup;
use Kisma\Core\Utility\Inflector;

//	Bootstrap ourselves
require_once __DIR__ . '/autoload.php';

$_dspUrl = Curl::currentUrl( false, false );

//	Must be logged in...
if ( Pii::guest() )
{
	header( 'Location: ' . $_dspUrl . '/' );
	die();
}

$_apps = null;
$_models = ResourceStore::model( 'app' )->findAll( array( 'select' => 'id, api_name', 'order' => 'api_name' ) );

if ( !empty( $_models ) )
{
	/** @var App[] $_models */
	foreach ( $_models as $_model )
	{
		$_apps .= HtmlMarkup::tag( 'option', array( 'value' => $_model->id, 'name' => Inflector::neutralize( $_model->api_name ) ), $_model->api_name );
	}
}

//	Default url
$_defaultUrl = $_dspUrl . '/rest/system/user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Oasys Example Code</title>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
	<script src="//google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="js/html5shiv.js"></script>
	<script src="js/respond.min.js"></script>
	<![endif]-->

	<link rel="icon" href="img/apple-touch-icon.png" type="image/png">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" type="text/css" media="screen">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" type="text/css">
	<link rel="stylesheet" href="css/jquery.jscrollpane.min.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/jquery.jscrollpane.lozenge.css" media="all" />
	<link rel="stylesheet" href="css/jquery.multientry.css" media="all" />
	<link rel="stylesheet" href="css/main.css" type="text/css" media="all" />
</head>
<body>

<div id="wrap">
<nav class="navbar navbar-default navbar-inverse navbar-fixed-top df-header">
	<div class="navbar-header">
		<button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<div class="brand-wrap">
			<img src="img/logo-32x32.png" alt="" />

			<div class="pull-left">
				<a href="#" class="navbar-brand df-title">DreamFactory Oasys</a>
				<br />
				<small>Example Code</small>
			</div>
		</div>
	</div>
	<div class="collapse navbar-collapse">
		<ul class="nav navbar-nav">
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" id="themes">Examples<b class="caret"></b></a>

				<ul class="dropdown-menu">
					<li class="dropdown-header">HTML</li>
					<li>
						<a href="#" class="example-code" data-provider="github">GitHub</a>
					</li>
					<li>
						<a href="#" class="example-code" data-provider="facebook">Facebook</a>
					</li>
					<li>
						<a href="#" class="example-code" data-provider="salesforce">Salesforce</a>
					</li>
				</ul>
			</li>

			<li>
				<a href="https://www.dreamfactory.com/developers/documentation" target="_blank">Docs</a>
			</li>
			<li>
				<a href="https://www.dreamfactory.com/developers/live_API" target="_blank">API</a>
			</li>
			<li>
				<a href="https://www.dreamfactory.com/developers/faq" target="_blank">FAQs</a>
			</li>
			<li>
				<a href="https://www.dreamfactory.com/developers/support" target="_blank">Support</a>
			</li>
			<li>
				<a href="#" id="app-close" target="_blank">Close</a>
			</li>
		</ul>
	</div>
</nav>

<div class="container">

	<section id="provider-settings">
		<div class="panel-group" id="provider-settings-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#provider-settings-group" href="#provider-form-body">Providers</a>
						<span class="pull-right"><button type="button" class="btn btn-primary btn-xs"><i class="fa fa-plus-square"></i>Add...</button></span>
					</h4>
				</div>
				<div id="provider-form-body" class="panel-collapse collapse in">
					<div class="panel-body">

						<form class="form-horizontal" id="provider-settings-form">
							<div class="form-group">
								<label for="provider-list" class="col-sm-2 control-label">Providers</label>

								<div class="col-sm-10">
									<select id="provider-list"></select>

									<input type="text"
										   class="form-control"
										   id="request-uri"
										   value="<?php echo $_defaultUrl; ?>"
										   placeholder="The request URI (i.e. /system/user)">

									<p class="help-block">Either an absolute or relative URL.</p>
								</div>
							</div>

							<div class="form-group row">
								<label for="request-method" class="col-sm-2 control-label">Method</label>

								<div class="col-sm-4">
									<select class="form-control" id="request-method">
										<option value="GET">GET</option>
										<option value="POST">POST</option>
										<option value="PUT">PUT</option>
										<option value="PATCH">PATCH</option>
										<option value="MERGE">MERGE</option>
										<option value="DELETE">DELETE</option>
										<option value="OPTIONS">OPTIONS</option>
										<option value="COPY">COPY</option>
									</select>
								</div>

								<label for="request-app" class="col-sm-2 control-label">App/API Key</label>

								<div class="col-sm-4">
									<select class="form-control" id="request-app">
										<optgroup label="Built-In">
											<option value="admin">admin</option>
											<option value="launchpad">launchpad</option>
										</optgroup>
										<optgroup label="Available">
											<?php echo $_apps; ?>
										</optgroup>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label for="request-body" class="col-sm-2 control-label">Body</label>

								<div class="col-sm-10">
									<textarea id="request-body" rows="2" class="form-control"></textarea>

									<p class="help-block">Must be valid JSON</p>
								</div>
							</div>

							<div class="multientry" data-attribute="request-headers" data-name="request-headers"></div>
							<hr />
							<div class="form-group">
								<div class="form-buttons">
									<button id="reset-request" type="button" class="btn btn-danger">Reset</button>
									<button id="send-request" type="button" class="btn btn-warning">Send Request</button>
								</div>
							</div>

						</form>

					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="call-settings">
		<div class="panel-group" id="call-settings-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#call-settings-group" href="#session-form-body">Call Settings</a>
					</h4>
				</div>
				<div id="session-form-body" class="panel-collapse collapse in">
					<div class="panel-body">

						<form class="form-horizontal" id="call-settings-form">
							<div class="form-group">
								<label for="request-uri" class="col-sm-2 control-label">Resource</label>

								<div class="col-sm-10">
									<input type="text"
										   class="form-control"
										   id="request-uri"
										   value="<?php echo $_defaultUrl; ?>"
										   placeholder="The request URI (i.e. /system/user)">

									<p class="help-block">Either an absolute or relative URL.</p>
								</div>
							</div>

							<div class="form-group row">
								<label for="request-method" class="col-sm-2 control-label">Method</label>

								<div class="col-sm-4">
									<select class="form-control" id="request-method">
										<option value="GET">GET</option>
										<option value="POST">POST</option>
										<option value="PUT">PUT</option>
										<option value="PATCH">PATCH</option>
										<option value="MERGE">MERGE</option>
										<option value="DELETE">DELETE</option>
										<option value="OPTIONS">OPTIONS</option>
										<option value="COPY">COPY</option>
									</select>
								</div>

								<label for="request-app" class="col-sm-2 control-label">App/API Key</label>

								<div class="col-sm-4">
									<select class="form-control" id="request-app">
										<optgroup label="Built-In">
											<option value="admin">admin</option>
											<option value="launchpad">launchpad</option>
										</optgroup>
										<optgroup label="Available">
											<?php echo $_apps; ?>
										</optgroup>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label for="request-body" class="col-sm-2 control-label">Body</label>

								<div class="col-sm-10">
									<textarea id="request-body" rows="2" class="form-control"></textarea>

									<p class="help-block">Must be valid JSON</p>
								</div>
							</div>

							<div class="multientry" data-attribute="request-headers" data-name="request-headers"></div>
							<hr />
							<div class="form-group">
								<div class="form-buttons">
									<button id="reset-request" type="button" class="btn btn-danger">Reset</button>
									<button id="send-request" type="button" class="btn btn-warning">Send Request</button>
								</div>
							</div>

						</form>

					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="provider-results">
		<div class="panel-group" id="call-results-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a name="provider-results" data-toggle="collapse" data-parent="#call-results-group" href="#call-results-body">Call Results</a>
						<span id="loading-indicator" class="pull-right"><i class="fa fa-spinner"></i></span>
					</h4>
				</div>
				<div id="call-results-body" class="panel-collapse collapse in">
					<div class="panel-body">
						<div id="example-code">Waiting...</div>
					</div>
				</div>
			</div>
		</div>
	</section>

</div>
</div>

<?php require_once( 'views/_footer.php' ); ?>
<script>
var _baseUrl = '<?php echo $_dspUrl; ?>';
</script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/jquery.mousewheel.min.js"></script>
<script type="text/javascript" src="js/mwheelintent.min.js"></script>
<script type="text/javascript" src="js/jquery.jscrollpane.min.js"></script>
<script src="js/jquery.multientry.js"></script>
<script src="js/app.jquery.js"></script>
</body>
</html>
