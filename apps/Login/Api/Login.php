<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\Login\Api;

use Keestash\ConfigProvider;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\Token\TokenNotCreatedException;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\L10N\IL10N;
use KSP\Core\Service\LDAP\ILDAPService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Login implements RequestHandlerInterface {

    private IUserRepository      $userRepository;
    private IL10N                $translator;
    private UserService          $userService;
    private ITokenRepository     $tokenRepository;
    private TokenService         $tokenService;
    private ILocaleService       $localeService;
    private ILanguageService     $languageService;
    private IJWTService          $jwtService;
    private LoggerInterface      $logger;
    private ILDAPService         $ldapService;
    private IConnectionRepository $connectionRepository;

    public function __construct(
        IUserRepository        $userRepository
        , IL10N                $translator
        , UserService          $userService
        , ITokenRepository     $tokenManager
        , TokenService         $tokenService
        , ILocaleService       $localeService
        , ILanguageService     $languageService
        , IJWTService          $jwtService
        , LoggerInterface      $logger
        , ILDAPService         $ldapService
        , IConnectionRepository $connectionRepository
    ) {
        $this->userRepository       = $userRepository;
        $this->translator           = $translator;
        $this->userService          = $userService;
        $this->tokenRepository      = $tokenManager;
        $this->tokenService         = $tokenService;
        $this->localeService        = $localeService;
        $this->languageService      = $languageService;
        $this->jwtService           = $jwtService;
        $this->logger               = $logger;
        $this->ldapService          = $ldapService;
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws TokenNotCreatedException
     * TODO add demo mode
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $userName   = $parameters["user"] ?? "";
        $password   = $parameters["password"] ?? "";
        $isSaas = $request->getAttribute(ConfigProvider::ENVIRONMENT_SAAS);

        try {
            $user = $this->userRepository->getUser($userName);
        } catch (UserNotFoundException $exception) {
            $this->logger->error('error retrieving user', ['exception' => $exception, 'userName' => $userName]);
            return new JsonResponse(
                'no user found'
                , IResponse::NOT_FOUND
            );
        }

        if (true === $this->userService->isDisabled($user)) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("No User Found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        $verified = false;
        if (true === $user->isLdapUser()) {
            $verified = $this->ldapService->verifyUser(
                $user
                , $this->connectionRepository->getConnectionByUser($user)
                , $password
            );
        } else {
            $this->logger->debug('verifying regular user');
            $verified = $this->userService->verifyPassword($password, $user->getPassword());
        }

        if (false === $verified) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("Invalid Credentials")
                ]
                , IResponse::UNAUTHORIZED
            );
        }
        $token = $this->tokenService->generate("login", $user);

        $this->tokenRepository->add($token);

        $user->setJWT(
            $this->jwtService->getJWT(
                new Audience(
                    IAudience::TYPE_USER
                    , (string) $user->getId()
                )
            )
        );
        return new JsonResponse(
            [
                "settings" => [
                    "locale"     => $this->localeService->getLocaleForUser($user)
                    , "language" => $this->languageService->getLanguageForUser($user)
                    , "isSaas"   => true === $isSaas
                ],
                "user"     => $user
            ],
            IResponse::OK
            , [
                VerificationService::FIELD_NAME_TOKEN       => $token->getValue()
                , VerificationService::FIELD_NAME_USER_HASH => $user->getHash()
            ]
        );

    }

}
