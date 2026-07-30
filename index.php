<?php include 'assets/view/header.php'; ?>
<script type="application/ld+json">
	<?php echo json_encode([
		"@context" => "https://schema.org",
		"@type" => "CivicStructure",
		"name" => "Skelby Forsamlingshus",
		"description" => "Forsamlingshus i Skelby - mødested for kultur, arrangementer, udlejning og fællesskab i Sydfalster.",
		"address" => [
			"@type" => "PostalAddress",
			"streetAddress" => "Gl. Landevej 66",
			"postalCode" => "4874",
			"addressLocality" => "Gedser",
			"addressCountry" => "DK"
		],
		"telephone" => "+4520835647",
		"url" => "https://skelby-forsamlingshus.dk/"
	], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
<main>
	<div id="centerColumn" style="position: relative;">


		<div id="twoColumn">
			<div id="column2">
				<iframe title="Kort over Skelby Forsamlingshus" width="100%" height="350" src="https://www.openstreetmap.org/export/embed.html?bbox=11.892968416213991%2C54.63258619607408%2C11.910885572433473%2C54.63779608414236&amp;layer=mapnik&amp;marker=54.63519122354269%2C11.90192699432373" style="border: 1px solid black"></iframe><br /><small><a href="https://www.openstreetmap.org/?mlat=54.63519&amp;mlon=11.90193#map=17/54.63519/11.90193&amp;layers=N">Vis større kort</a></small>
			</div>

			<div class="arrow-up"></div>

			<div style="width:90%; height:350px;overflow: hidden;">
				<img style="width:100%; height:100%;margin-left:25px; object-fit: cover;object-position: right;"
					src="/assets/img/skelby/setting3.jpg" alt="Skelby Forsamlingshus"
					srcset="">
			</div>
			<div style="width:73%;text-align:center; margin:auto;margin-left: 66px;">
				<p style="font-size: 16px; line-height: 1.5;"><b>Forsamlingshus i Skelby</b><br>
				<address style="font-style: normal;">Gl. Landevej 66, 4874 Gedser</address>
				<a class="subtle-link" href="&#x74;&#x65;&#x6c;&#x3a;&#x2b;&#x34;&#x35;&#x32;&#x30;&#x38;&#x33;&#x35;&#x36;&#x34;&#x37;">&#x2b;&#x34;&#x35;&#x20;&#x32;&#x30;&#x20;&#x38;&#x33;&#x20;&#x35;&#x36;&#x20;&#x34;&#x37;</a>
				</p>
			</div>


		</div>


</main>
<?php include 'assets/view/footer.php'; ?>