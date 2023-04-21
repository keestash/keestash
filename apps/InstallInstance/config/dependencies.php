<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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
use KSA\InstallInstance\Command\Environment;
use KSA\InstallInstance\Command\Install;
use KSA\InstallInstance\Command\ListEnvironment;
use KSA\InstallInstance\Command\Ping;
use KSA\InstallInstance\Command\Uninstall;
use KSA\InstallInstance\Factory\Command\EnvironmentFactory;
use KSA\InstallInstance\Factory\Command\InstallFactory;
use KSA\InstallInstance\Factory\Command\ListEnvironmentFactory;
use KSA\InstallInstance\Factory\Command\PingFactory;
use KSA\InstallInstance\Factory\Command\UninstallFactory;

return [
    ConfigProvider::FACTORIES => [
        // command
        Uninstall::class         => UninstallFactory::class
        , Install::class         => InstallFactory::class
        , Ping::class            => PingFactory::class
        , Environment::class     => EnvironmentFactory::class
        , ListEnvironment::class => ListEnvironmentFactory::class
    ]
];