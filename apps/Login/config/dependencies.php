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

use Keestash\ConfigProvider;
use KSA\Login\Api\Configuration;
use KSA\Login\Api\Login\Alpha;
use KSA\Login\Api\Login\Login;
use KSA\Login\Api\Logout;
use KSA\Login\Event\ApplicationEndedEventListener;
use KSA\Login\Factory\Api\ConfigurationFactory;
use KSA\Login\Factory\Api\Login\AlphaFactory;
use KSA\Login\Factory\Api\Login\LoginFactory;
use KSA\Login\Factory\Api\LogoutFactory;
use KSA\Login\Factory\Event\ApplicationEndedEventListenerFactory;
use KSA\Login\Service\TokenService;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    ConfigProvider::FACTORIES => [
        // api
        // -- login
        Login::class                           => LoginFactory::class
        , Alpha::class                         => AlphaFactory::class

        // -- key
        , Configuration::class                 => ConfigurationFactory::class
        , Logout::class                        => LogoutFactory::class

        // service
        , TokenService::class                  => InvokableFactory::class

        // command
        , \KSA\Login\Command\Login::class      => \KSA\Login\Factory\Command\LoginFactory::class
        , \KSA\Login\Command\Logout::class     => \KSA\Login\Factory\Command\LogoutFactory::class

        // event
        , ApplicationEndedEventListener::class => ApplicationEndedEventListenerFactory::class
    ]
];
