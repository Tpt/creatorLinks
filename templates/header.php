<?php header('Content-type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>CreatorLinks</title>
	<link type="text/css" href="bootstrap.min.css" rel="stylesheet" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css">
body {
	padding-top: 60px;
}
</style>
	<link type="text/css" href="bootstrap-responsive.min.css" rel="stylesheet" />
</head>
<body>
<div role="document">
	<header class="navbar navbar-fixed-top navbar-inverse" role="banner">
		<div class="navbar-inner">
			<div class="container-fluid">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="<?php echo $basePath ?>">CreatorLinks</a>
				<div class="nav-collapse">
					<ul class="nav">
						<li <?php if( $action == 'home' ) { echo 'class="active"'; } ?>><a href="<?php echo $basePath; ?>" rel="home">Home</a></li>
						<!-- <li <?php if( $action == 'about' ) { 'echo class="active"'; } ?>><a href="<?php echo $basePath; ?>">About</a></li> -->
					</ul>
					<form action="<?php echo $basePath ?>/search.php" method="GET" id="quick-search" role="search" class="navbar-search pull-right"><input type="search" name="search" id="search-box" placeholder="search" class="search-query" value="<?php if( isset( $search ) ) { echo $search; } ?>" /></form>
				</div>
			</div>
		</div>
	</header>
	<div class="container-fluid">
		<div class="row-fluid">
			<?php /*  <div class="span3">
				<nav class="well sidebar-nav" role="navigation">
					<ul class="nav nav-list">
					   <li class="nav-header">{@wsexport.books@}</li>
						<li><a href="{jurl 'book:index', array('lang' => $lang, 'format' => 'html', 'order' => 'name', 'asc' => 'true')}" rel="directory">{@wsexport.all_books@}</a></li>
						<li><a href="{jurl 'book:index', array('lang' => $lang, 'format' => 'html', 'order' => 'downloads', 'asc' => 'false')}" rel="directory">{@wsexport.popular_publications@}</a></li>
						<li><a href="{jurl 'book:index', array('lang' => $lang, 'format' => 'html', 'order' => 'created', 'asc' => 'false')}" rel="directory">{@wsexport.new_publications@}</a></li>
						<li><a href="{jurl 'book:index', array('lang' => $lang, 'format' => 'html', 'order' => 'downloads', 'asc' => 'true')}" rel="directory">{@wsexport.unpopular_publications@}</a></li>
						<li><a href="{jurl 'book:random', array('lang' => $lang, 'format' => 'html')}">{@wsexport.random_book@}</a></li>
					</ul>
				</nav>
			</div><!-- class="span9"-->*/ ?>
			<div role="main">
			<?php if(isset($success)) {
				echo '<div class="alert alert-success">' . $success . '</div>' . "\n";
			} ?>
			<?php if(isset($error)) {
				echo '<div class="alert alert-error">' . $error . '</div>' . "\n";
			} ?>
