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

namespace Keestash\Factory\ThirdParty\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use KSP\Core\Service\Config\IConfigService;
use Psr\Container\ContainerInterface;

class ConnectionFactory {

    public function __invoke(ContainerInterface $container): Connection {
        $config = $container->get(IConfigService::class);
        return DriverManager::getConnection(
            [
                'driver'     => 'pdo_mysql'
                , 'host'     => $config->getValue('db_host')
                , 'dbname'   => $config->getValue('db_name')
                , 'port'     => $config->getValue('db_port')
                , 'user'     => $config->getValue('db_user')
                , 'password' => $config->getValue('db_password')
            ]
        );
    }

}