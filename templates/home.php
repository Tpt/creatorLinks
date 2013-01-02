<?php include 'header.php'; ?>
<article>
	<div class="hero-unit">
		<h1>Welcome to creator links!</h1>
		<p>Creator links is a repository that store links of creator pages of Wikimedia Commons and author pages of Wikisource. Links are enhanced with Wikidata and VIAF data. The source code is available on <a href="https://github.com/Tpt/creatorLinks">GitHub</a>.</p>
	</div>
	<p>The entries can be found by links. For that add to the URL ?site=ID_OF_THE_SITE&title=TITLE_OF_THE_PAGE</p>
	<p>IDs of Wikipedias are like enwiki or zh_min_nanwiki, IDs of Wikisources are like frsource, IDs of other websites are the ids shown in the list of links.<p>
	<p>Data are also available in JSON and serialized PHP formats. To get these formats add format=json or format=php to the URL.</p>
</article>
<?php include 'footer.php';
exit(); ?>
