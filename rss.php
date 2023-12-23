<?php

error_reporting( E_ALL );
ini_set( 'display_errors', false );
ini_set( 'log_errors', true );
ini_set( 'error_log', './php-error.log' );

define( 'CSV_FILE', './alerts-log.csv' );

/**
 * Read from the CSV and format it as a fully-specced RSS 2.0 file
 */

$handle = fopen( CSV_FILE, 'r' );

if ( false === $handle ) {
	error_log( 'Could not parse the csv' );
	exit(1);
}

/**
 * Escape strings for display
 * 
 * @link https://stackoverflow.com/questions/3426090/how-do-you-make-strings-xml-safe
 * @param string $string The string to escape
 * @return string
 */
function escape( $string ) {
	return htmlspecialchars( $string, ENT_XML1 );
}

/**
 * Convert our feeds.csv into a number of <outline> elements
 * 
 * @param resource $handle Pointer to ./feeds.csv
 * @return void
 */
function iterate( $handle ) {
	$iterator = 0;
	while ( ( $data = fgetcsv( $handle, 1000 ) ) !== false ) {
		$iterator++;
		// skip over the header row.
		if ( 1 === $iterator ) {
			continue;
		}

		error_log( var_export( $data, true ) );
		/*
		 * 0 header
		 * 1 description
		 * 2 link_title
		 * 3 link_url
		 * 4 link_target
		 * 5 time timestamp that this was added to the scrape CSV
		 */
		$title = escape( sprintf(
			'%1$s: %2$s',
			$data[0] ?? '',
			$data[1] ?? ''
		) );
		$link  = escape( $data[3] ?? '' );

		$description  = '';
		$description .= sprintf(
			'<p><a href="%1$s">%2$s: %1$s</a></p>',
			$data[3],
			$data[2]
		);
		$description .= '<p>The publication date on this feed reflects the date that the item was scraped from cota.com, not the date that the item was published or pulled down.</p>';

		$pubDate = date_format( DateTimeImmutable::createFromFormat( 'U', $data[5] ), DATE_RSS );

		?>
			<item>
				<title><?php echo $title; ?></title>
				<link><?php echo $link; ?></link>
				<description><![CDATA[<?php echo $description; ?>]]></description>
				<pubDate><?php echo $pubDate; ?></pubDate>
				<guid><?php echo $link; ?></guid>
			</item>
		<?php

		echo "\n";
	}
}

?>
<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
	<channel>
		<title>Unofficial Central Ohio Transit Authority Alerts Feed</title>
		<link>https://github.com/benlk/cota-reroute-pdf-rss</link>
		<description>COTA alert messages scraped from a COTA API; parsed and formatted as RSS</description>
		<language>en-us</language>
		<atom:link href="https://raw.githubusercontent.com/benlk/cota-reroute-pdf-rss/main/rss.xml" rel="self" type="application/rss+xml"/>

		<?php iterate( $handle ); ?>
	</channel>
</rss>
