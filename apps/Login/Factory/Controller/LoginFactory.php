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

namespace KSA\Login\Factory\Controller;

use Keestash\Core\Repository\Instance\InstanceDB;
use KSA\Login\Controller\Login;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class LoginFactory {

    public function __invoke(ContainerInterface $container): Login {
        return new Login(
            $container->get(InstanceDB::class)
            , $container->get(IAppRenderer::class)
            , $container->get(IUserRepository::class)
            , $container->get(TemplateRendererInterface::class)
        );
    }

}