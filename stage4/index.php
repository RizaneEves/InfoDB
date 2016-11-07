<!doctype html>
<html>
	<head>
		<title>InfoDB</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

		<link href="https://fonts.googleapis.com/css?family=Playfair+Display|Raleway:300" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="/resources/prism/prism.css">
		<style>
			@media (min-width: 992px){
				.body{
					padding: 8px 32px;
				}
			}
			@media (max-width: 991px){
				#sidebar{
					border-top: 1px solid black;
				}
			}

			.title{
				font-size: 1.5em;
				font-weight: 400;
				font-family: "Playfair Display", "Times New Roman", Serif;
				margin: 20px 0px 10px;
			}

			.headline{
				font-size: 2.5em;
				font-weight: 400;
				font-family: "Playfair Display", "Times New Roman", Serif;
				text-align: center;
				border-bottom: 1px solid black;
				margin: 0px;
				padding: 20px 0px;
			}

			#content, #sidebar{
				font-size: 1.2em;
				font-family: "Raleway", Sans;
			}

			.accent{
				color: #ff5252;
				font-weight: 700;
			}

			div.loading{
				height: 72px;
				background-image: url("/resources/loading.gif");
				background-size: 72px 72px;
				background-position: center center;
				background-repeat: no-repeat;
			}

			pre{
				font-family: "Inconsolata", monospace;
				border-radius: 0;
			}
		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.8/angular.min.js"></script>
		<script src="/resources/prism/prism.js"></script>
		<script src="https://cdn.rawgit.com/sercaneraslan/angular-prism-directive/master/ng-prism.js"></script>
		<script src="main.js"></script>
	</head>
	<body class="body container-fluid">
		<div class="row">
			<h1 class="headline">
				The Amazing InfoDB
			</h1>
		</div>
		<div id="wrapper" class="row" ng-app="infodb" ng-controller="main">
			<div id="content" class="col-md-8">
				<div class="title">Stage 4 Query Demo</div>
				The Amazing InfoDB is a very™ powerful database which contains data that we scraped off of IMDb. To demo its capability, do you know who was involved in making the Rick and Morty TV series?
				<h4 class="text-right">SQL code</h4>
				<div class="loading" ng-if="!stage4.sampleCode"></div>
				<pre><code class="language-sql" prism>{{ stage4.sampleCode }}</code></pre>
				<h4 class="text-right">Result</h4>
				<div ng-switch="stage4.status">
					<div ng-switch-when="pending">
						Executing query...<br>
						<div class="loading"></div>
					</div>
					<div class="table-responsive" ng-switch-when="ok">
						<table class="table table-striped table-bordered table-hover">
							<tbody>
								<tr ng-repeat="result in stage4.result">
									<td ng-repeat="data in result">
										{{ data }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div ng-switch-when="failed">
						SQL failed to execute.
					</div>
				</div> 
			</div>
			<div id="sidebar" class="col-md-4">
				<div class="title">Navigation</div>
				<ul class="nav nav-pills nav-stacked">
					<li role="presentation"><a href="/">Home</a></li>
					<li role="presentation" class="active"><a href="/stage4">Stage 4 Demo</a></li>
				</ul>
				<div class="title">What is InfoDB?</div>
				<p>
					<span class="accent">InfoDB</span> can show and edit detailed information about a <span class="accent">celebrity</span> and provide <span class="accent">customized searching</span> similar to IMDB. <span class="accent">InfoDB</span> is planned to show a graph (nodes/edges) to represent the connection between actors/producers through movies they were <span class="accent">involved</span>.
				</p>
				<p>
					Click <a href="https://wiki.illinois.edu/wiki/pages/viewpage.action?pageId=610271845" target="_blank">here</a> to go to our porject page on Illinois Wiki.
				</p>
				<p>
					Are you an admin? Click <a href="/phpmyadmin">here</a> to do your admin stuff.
				</p>
			</div>
		</div>
	</body>
</html>
