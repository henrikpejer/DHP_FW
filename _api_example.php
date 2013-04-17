<?php

$app = new App();
$app->get('something',function(){echo "something";});
$app->post('something', function(){echo 'something else';});
$app->configure->post('environment','SettingsValue');	# only set post when environment is 'environment'
$app->configure->post = 'settingsValue'; # valid for ALL environments;
$app->get('hejja',array($app,'methodToCall')); # callbacks possible, ok?

$app->apply('blogModule','/blog'); # hmm... if everything is a module/component, then...
