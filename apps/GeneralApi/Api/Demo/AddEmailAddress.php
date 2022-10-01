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

namespace KSA\GeneralApi\Api\Demo;

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\User\UserService;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\Settings\Repository\DemoUsersRepository;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddEmailAddress implements RequestHandlerInterface {

    private DemoUsersRepository $demoUsersRepository;
    private UserService         $userService;

    public function __construct(
        DemoUsersRepository $demoUsersRepository
        , UserService       $userService
    ) {
        $this->demoUsersRepository = $demoUsersRepository;
        $this->userService         = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $email      = $parameters['email'] ?? '';

        if (false === $this->userService->validEmail($email)) {
            throw new GeneralApiException('invalid email');
        }

        $this->demoUsersRepository->add($email);

        return new JsonResponse(
            [
                "message" => "ok"
            ]
            , IResponse::OK
        );
    }

}