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

namespace KSA\Login\Controller;

use Keestash\Exception\KeestashException;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\Core\Service\HTTP\IPersistenceService;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Logout extends StaticAppController {

    private ITokenRepository          $tokenRepository;
    private ILoader                   $loader;
    private IPersistenceService       $persistenceService;
    private TemplateRendererInterface $templateRenderer;

    public function __construct(
        ITokenRepository            $tokenRepository
        , ILoader                   $loader
        , IPersistenceService       $persistenceService
        , TemplateRendererInterface $templateRenderer
        , IAppRenderer              $appRenderer
    ) {
        $this->tokenRepository    = $tokenRepository;
        $this->loader             = $loader;
        $this->persistenceService = $persistenceService;
        $this->templateRenderer   = $templateRenderer;

        parent::__construct($appRenderer);
    }

    public function run(ServerRequestInterface $request): string {
        /** @var IUser|null $user */
        $user       = $request->getAttribute(IUser::class);
        $defaultApp = $this->loader->getDefaultApp();
        $this->persistenceService->killAll();

        if (null === $defaultApp) {
            throw new KeestashException();
        }

        if (null !== $user) {
            $this->tokenRepository->removeForUser($user);
        }

        return $this->templateRenderer
            ->render('login::login');
    }

}