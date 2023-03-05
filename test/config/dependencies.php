<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace KST\config;

use Doctrine\DBAL\Connection;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\File\Upload\FileService;
use KST\Service\Core\Cache\RedisService;
use KST\Service\Core\Manager\EventManager\EventService;
use KST\Service\Core\Service\Config\IniConfigService;
use KST\Service\Core\Service\Core\Locale\LocaleService;
use KST\Service\Core\Service\Email\EmailService;
use KST\Service\Core\Service\Encryption\Credential\CredentialService;
use KST\Service\Core\Service\HTTP\HTTPService;
use KST\Service\Core\Service\Phinx\Migrator;
use KST\Service\Core\System\Installation\App\LockHandler as CoreAppLockHandler;
use KST\Service\Core\System\Installation\Instance\LockHandler as CoreInstanceLockHandler;
use KST\Service\Factory\Core\Cache\RedisServiceFactory;
use KST\Service\Factory\Core\Manager\EventManager\EventManagerFactory;
use KST\Service\Factory\Core\Repository\InstanceDBFactory;
use KST\Service\Factory\Core\Service\Config\ConfigServiceFactory;
use KST\Service\Factory\Core\Service\Config\IniConfigServiceFactory;
use KST\Service\Factory\Core\Service\Email\EmailServiceFactory;
use KST\Service\Factory\Core\Service\HTTP\HTTPServiceFactory;
use KST\Service\Factory\Core\Service\Phinx\MigratorFactory;
use KST\Service\Factory\Core\System\Installation\App\LockHandlerFactory as TestAppLockHandler;
use KST\Service\Factory\Core\System\Installation\Instance\LockHandlerFactory as TestInstanceLockHandler;
use KST\Service\Factory\Service\File\Upload\FileServiceFactory;
use KST\Service\Factory\Service\UserServiceFactory;
use KST\Service\Factory\ThirdParty\Doctrine\ConnectionFactory;
use KST\Service\Service\UserService;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    Connection::class                => ConnectionFactory::class
    , Migrator::class                => MigratorFactory::class
    , HTTPService::class             => HTTPServiceFactory::class
    , UserService::class             => UserServiceFactory::class
    , FileService::class             => FileServiceFactory::class
    , EventService::class            => EventManagerFactory::class
    , LocaleService::class           => InvokableFactory::class
    , RedisService::class            => RedisServiceFactory::class
    , EmailService::class            => EmailServiceFactory::class
    , CoreAppLockHandler::class      => TestAppLockHandler::class
    , CoreInstanceLockHandler::class => TestInstanceLockHandler::class
    , ConfigService::class           => ConfigServiceFactory::class
    , IniConfigService::class        => IniConfigServiceFactory::class
    , InstanceDB::class              => InstanceDBFactory::class
    , CredentialService::class       => InvokableFactory::class
];