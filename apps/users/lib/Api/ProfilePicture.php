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

namespace KSA\Users\Api;

use Keestash\Api\AbstractApi;
use Keestash\Api\Response\Base64Response;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Api\Response\PlainResponse;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\AssetService;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class ProfilePicture extends AbstractApi {

    private $assetService   = null;
    private $parameters     = null;
    private $userRepository = null;

    public function __construct(
        IL10N $l10n
        , AssetService $assetService
        , IUserRepository $userRepository
    ) {
        parent::__construct($l10n, true);

        $this->assetService   = $assetService;
        $this->userRepository = $userRepository;
    }

    public function onCreate(...$params): void {
        $this->parameters = $params[0];

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $userId = $this->parameters['user_id'] ?? null;

        $user = $this->userRepository->getUserById((string) $userId);

        if (null === $user) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No user found"
                ]
            );
            parent::setResponse($response);
            return;
        }

        $picture = $this->assetService->getUserProfilePicture($user);

        if (null === $picture) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "No picture found"
                ]
            );
            parent::setResponse($response);
            return;
        }

        $defaultResponse = new PlainResponse();
        $defaultResponse->setMessage($picture);

        parent::setResponse($defaultResponse);
    }

    public function afterCreate(): void {

    }

}