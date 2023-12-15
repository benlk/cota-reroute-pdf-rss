<?php

error_reporting(E_ALL);
ini_set( 'display_errors', false );
ini_set( 'log_errors', true );
ini_set( 'error_log', './php-error.log' );

define( 'CSV_SEPARATOR', ',' );
define( 'CSV_ENCLOSURE', '"' );
define( 'CSV_ESCAPE', '"' );

define( 'JSON_FILE', './build/acf-options.json' );
define( 'CSV_FILE', './alerts-log.csv' );

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
 * return a line for the CSV
 * 
 * @param array $args potential items for the CSV line
 * @return array
 */
function return_csv_line( $args = [] ) {
    $args = array_merge( 
        $args,
        [
            'header'      => 'header',
            'description' => 'description',
            'link_title'  => 'link_title',
            'link_url'    => 'link_url',
            'link_target' => 'link_target',
            'time'        => 'time',
        ]
    );

    return [
        $args['header'],
        $args['description'],
        $args['link_title'],
        $args['link_url'],
        $args['link_target'],
        $args['time'],
    ];
}

/**
 * Loop through the JSON file, and:
 * - check to see if the items in the JSON file are already in the JSON file
 * - if the line isn't in the JSON file, echo it in the CSV format we expect
 */
function iterate( $json_handle, $csv_handle ) {
    $json = file_get_contents( JSON_FILE );
    $alerts = json_decode( $json, true );

    $csv_contents = file_get_contents( CSV_FILE );

    error_log( var_export( $csv_contents, true ));

    if ( empty( $alerts ) ) {
        return;
    }

    foreach ( $alerts as $alert ) {
        error_log( var_export( $alert, true ) );

        $new_entry = [];
        $new_entry['header']      = $alert['header'];
        $new_entry['description'] = $alert['description'];
        $new_entry['link_title']  = $alert['link']['title'];
        $new_entry['link_url']    = $alert['link']['url'];
        $new_entry['link_target'] = $alert['link']['target'];
        $new_entry['time']        = time();

        // check if the link_url exists in the file already
        if ( ! str_contains( $csv_contents, $new_entry['link_url'] ) ) {
            // if not, then write it to the file
            fputcsv(
                $csv_handle,
                $new_entry,
                CSV_SEPARATOR,
                CSV_ENCLOSURE,
                CSV_ESCAPE
            );
        }
    }
};

/**
 * Main
 */
function main() {
    /**
     * Read from the scraped files
     */
    $json_handle = fopen( JSON_FILE, 'r' );
    $csv_handle  = fopen( CSV_FILE, 'c+' );

    if ( false === $json_handle ) {
        error_log( 'error accessing scraped JSON' );
        exit(1);
    }

    if ( false === $csv_handle ) {
        error_log( 'error creating or accessing csv file' );
        exit(2);
    }

    if ( 0 === filesize( './alerts-log.csv' ) ) {
        fputcsv(
            $csv_handle,
            return_csv_line(),
            CSV_SEPARATOR,
            CSV_ENCLOSURE,
            CSV_ESCAPE
        );
    }

    iterate( $json_handle, $csv_handle );
}

main();