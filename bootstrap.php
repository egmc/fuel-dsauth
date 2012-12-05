<?php


Autoloader::add_classes(array(
		'DsAuth\\Controller'           	=> __DIR__.'/classes/controller.php',
		'DsAuth\\DsAuth'  				=> __DIR__.'/classes/dsauth.php',
		'NinjAuth\\Adapter_Dsauth'  	=> __DIR__.'/classes/adapter/dsauth.php',
));

// use DsAuth as global
Autoloader::alias_to_namespace('DsAuth\\DsAuth');