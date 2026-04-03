<?php

require 'vendor/autoload.php';

$container = require 'config/bootstrap.php';
$passwordHasher = $container->get('security.password_hasher_factory')->getPasswordHasher('App\Entity\User');

$plainPassword = 'Scallare192003+';
$hashedPassword = $passwordHasher->hash($plainPassword);

echo $hashedPassword . PHP_EOL;
