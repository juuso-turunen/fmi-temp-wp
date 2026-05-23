<?php
/**
 * Script to get the temperature in Lahti at 10:30 AM today
 * Saves the temperature as JSON in the file specified by the first argument
 */


$local_file = $argv[1] ?? false;

if (! $local_file) throw new InvalidArgumentException('Local file is required as an argument!');

$place = "Lahti";

// Determine the target 10:30 AM local time, adjusted to UTC
$tz = new DateTimeZone('Europe/Helsinki');
$targetTime = new DateTime('10:30:00', $tz);
if (new DateTime('now', $tz) < $targetTime) {
    $targetTime->modify('-1 day');
}
$targetTime->setTimezone(new DateTimeZone('UTC'));
$targetTimeUTC = $targetTime->format('Y-m-d\TH:i:s\Z');

$url = "https://opendata.fmi.fi/wfs?" . http_build_query([
    'service'        => 'WFS',
    'version'        => '2.0.0',
    'request'        => 'getFeature',
    'storedquery_id' => 'fmi::observations::weather::timevaluepair',
    'place'          => $place,
    'parameters'     => 't2m',
    'starttime'      => $targetTimeUTC,
    'endtime'        => $targetTimeUTC
]);

$xmlData = @file_get_contents($url);
if (!$xmlData) exit;

$dom = new DOMDocument();
if (@$dom->loadXML($xmlData)) {
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('wml2', 'http://www.opengis.net/waterml/2.0');
    $elements = $xpath->query('//wml2:MeasurementTVP');

    if ($elements->length > 0) {
        $valueNode = $xpath->query('wml2:value', $elements->item($elements->length - 1))->item(0);
        $timeNode = $xpath->query('wml2:time', $elements->item($elements->length - 1))->item(0);

        if (!$valueNode || strtolower($valueNode->nodeValue) === 'nan') return;
        if (!$timeNode || strtolower($timeNode->nodeValue) === 'nan') return;

        $temp = round((float)$valueNode->nodeValue, 1);
        $time = $timeNode->nodeValue;

        $obj = array('time' => $time, 'temp' => $temp);

        // Save the temperature and the time as JSON to a local file
        file_put_contents($local_file, json_encode($obj));
    }
}
