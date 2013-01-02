<?php include 'header.php'; ?>
<article>
	<div class="hero-unit">
		<h1>Welcome to creator links!</h1>
		<p>Creator links is a repository that store links of creator pages of commons and author pages of Wikisource. This links are enhanced with Wikidata and VIAF data.</p>
	</div>
	<p>The entries can be found by links. For that add to the URL ?site=ID_OF_THE_SITE&title=TITLE_OF_THE_PAGE</p>
	<p>IDs of wikipedia wikis are like enwiki or zh_min_nanwiki, IDS of wikisource wikis are like frsource, IDs of other websites are the ids shown in the list of links.<p>
	<p>Data are also available in JSON and serielized PHP formats. To get these formats add format=json or format=php in the URL.</p>
</article>
<?php include 'footer.php';
exit(); ?>
