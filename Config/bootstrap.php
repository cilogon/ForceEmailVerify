<?php

App::uses('CakeEventManager', 'Event');
App::uses('ForceEmailVerifyListener', 'ForceEmailVerify.Lib');

CakeEventManager::instance()->attach(new ForceEmailVerifyListener());
