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

namespace Keestash\Factory\Core\Service\Controller;

use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Service\Controller\AppRenderer;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\System\Application;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\File\RawFile\IRawFileService;
use KSP\Core\Service\HTTP\IHTTPService;
use KSP\Core\Service\L10N\IL10N;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class AppRendererFactory {

    public function __invoke(ContainerInterface $container): IAppRenderer {
        return new AppRenderer(
            $container->get(IRouterService::class)
            , $container->get(Config::class)
            , $container->get(TemplateRendererInterface::class)
            , $container->get(Application::class)
            , $container->get(IHTTPService::class)
            , $container->get(LockHandler::class)
            , $container->get(FileService::class)
            , $container->get(IRawFileService::class)
            , $container->get(ILocaleService::class)
            , $container->get(ILoaderService::class)
            , $container->get(RouterInterface::class)
            , $container->get(IL10N::class)
            , $container->get(FileRepository::class)
        );
    }

}