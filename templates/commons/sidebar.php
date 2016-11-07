<?php
$activePage = isset($activePage) ? $activePage : "";

function gly($name){
	return "<span class='glyphicon glyphicon-".$name."' aria-hidden='true'></span>";
}

function buildLinks($activePage = ""){
	$links = [
		["home", gly("home") . " Home", "/"],
		["search", gly("search") . " Search", "/"],
		["stage4", gly("blackboard") . " Stage 4 Demo", "/stage4"],
		["project_site", gly("book") . " Project Wiki", "https://wiki.illinois.edu/wiki/pages/viewpage.action?pageId=610271845"],
		["admin", gly("wrench") . " Admin Panel", "http://fa16-cs411-08.cs.illinois.edu/phpmyadmin"],
	];
	foreach($links as $link){
		if($activePage == $link[0]){
			echo '<li role="presentation" class="active"><a href="' . $link[2] . '">' . $link[1] . '</a></li>';
		}else{
			echo '<li role="presentation"><a href="' . $link[2] . '">' . $link[1] . '</a></li>';
		}
		echo "\n";
	}
}

?>
<div id="sidebar" class="col-md-4">
	<div class="title">Navigation</div>
	<ul class="nav nav-pills nav-stacked">
		<?php buildLinks($activePage) ?>
	</ul>
	<div class="title">What is InfoDB?</div>
	<p>
		<span class="accent">InfoDB</span> can show and edit detailed information about a <span class="accent">celebrity</span> and provide <span class="accent">customized searching</span> similar to IMDB. <span class="accent">InfoDB</span> is planned to show a graph (nodes/edges) to represent the connection between actors/producers through movies they were <span class="accent">involved</span>.
	</p>
	<p>
		Copyright 2016 All Authors. Optimized for mobile.
	</p>
</div>
