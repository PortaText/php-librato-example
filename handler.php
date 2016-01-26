<?php

/* Configurable section STARTS */
$from = '14445556666';
$targets = array('12223334444', '13334445555');
$apiKey = 'apikey';
/* Configurable section ENDS */

/* Include Composer autoloader */
require implode(DIRECTORY_SEPARATOR, array(
  __DIR__, "vendor", "autoload.php"
));
use PortaText\Client\Curl as Client;

/* Read Librato payload */
$body = json_decode($_POST['payload'], true);

/* Get alert information */
$alertName = $body['alert']['name'];
$alertDescription = $body['alert']['description'];
$alertSources = array();
$alertConditions = array();

foreach ($body['conditions'] as $data) {
  $threshold = $data['threshold'];
  $cond = $data['type'];
  $alertConditions[] = "$cond $threshold";
}
$alertConditions = implode(" and ", $alertConditions);

foreach ($body['violations'] as $source => $data) {
  foreach ($data as $values) {
    $metric = $values['metric'];
    $value = $values['value'];
    $alertSources[] = "$source $metric value: $value";
  }
}
$alertSources = implode(" and ", $alertSources);

foreach ($body['conditions'] as $data) {
  $threshold = $data['threshold'];
  $cond = $data['type'];
  $alertConditions[] = "$cond $threshold";
}
$alertConditions = implode(" and ", $alertConditions);

foreach ($body['violations'] as $source => $data) {
  foreach ($data as $values) {
    $metric = $values['metric'];
    $value = $values['value'];
    $alertSources[] = "$source $metric value: $value";
  }
}
$alertSources = implode(" and ", $alertSources);

/* Create the SMS content */
$text = "Librato alert: $alertName ($alertDescription) $alertConditions for $alertSources";

/* Send the message! :) */
$portatext = new Client();
try {
  foreach($targets as $to) {
    $portatext
      ->setApiKey($apiKey)
      ->sms()
      //->toContactLists(array(3))
      ->to($to)
      ->text($text)
      ->from($from)
      ->post();
  }
  header("HTTP/1.1 200 OK");
  echo('{"result": "ok"}');
} catch(\Exception $e) {
  file_put_contents(
    '/tmp/errors',
    print_r($e->getMessage(), true) . "\n" . print_r($e->getResult(), true)
  );
  header("HTTP/1.1 500 OK");
  echo('{"result": "error"}');
}

