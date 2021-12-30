<?php

use Psr\Container\ContainerInterface;
use App\Service\Objectbase;

$container['find_objectbase_service'] = static function (ContainerInterface $container) {
    return new Objectbase\Find($container->get('objectbase_repository'));
};

$container['create_objectbase_service'] = static function (ContainerInterface $container) {
    return new Objectbase\Create($container->get('objectbase_repository'));
};

$container['update_objectbase_service'] = static function (ContainerInterface $container) {
    return new Objectbase\Update($container->get('objectbase_repository'));
};

$container['delete_objectbase_service'] = static function (ContainerInterface $container) {
    return new Objectbase\Delete($container->get('objectbase_repository'));
};
?>