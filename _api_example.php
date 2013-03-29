<?php

$app = new App();
$app->get('something',function(){echo "something";});
$app->post('something', function(){echo 'something else';});
$app->settings->post('environment','SettingsValue');	# only set post when environment is 'environment'
$app->settings->post = 'settingsValue'; # valid for ALL environments;
$app->get('hejja',array($app,'methodToCall')); # callbacks possible, ok?
