<?php

$stdout = new mageekguy\atoum\writers\std\out();

$cli = new \mageekguy\atoum\reports\realtime\cli();
$runner->addReport($cli->addWriter($stdout));

$telemetry = new \mageekguy\atoum\reports\telemetry();
$telemetry
	->setTelemetryUrl(getenv('CI') !== false ? null : 'http://localhost:8087')
	->readProjectNameFromComposerJson(__DIR__ . '/composer.json')
	//->sendAnonymousReport()
;
$runner->addReport($telemetry->addWriter($stdout));

$script
	->php('php -n -ddate.timezone=Europe/Paris')
	->addTestsFromDirectory(__DIR__ . '/tests/units')
;
