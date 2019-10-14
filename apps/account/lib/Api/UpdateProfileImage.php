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

namespace KSA\Account\Api;

use doganoo\SimpleRBAC\Test\DataProvider\Context;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\DTO\HTTP;
use KSA\Account\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\AssetManager\IAssetManager;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UpdateProfileImage extends AbstractApi {

    private $l10n        = null;
    private $userManager = null;
    /** @var IAssetManager $assetManager */
    private $assetManager      = null;
    private $permissionManager = null;
    private $user              = null;
    private $parameters        = null;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userManager
        , IAssetManager $assetManager
        , IPermissionRepository $permissionManager
    ) {
        $this->l10n              = $l10n;
        $this->userManager       = $userManager;
        $this->assetManager      = $assetManager;
        $this->permissionManager = $permissionManager;

        parent::__construct($l10n);
    }

    public function onCreate(...$params): void {
        $this->parameters = $params;
        $userId           = $params[1] ?? null;
        $this->user       = $this->userManager->getUserById((string) $userId);

        parent::setPermission(
            $this->preparePermission($this->user)
        );

    }

    private function preparePermission(IUser $contextUser): IPermission {
        /** @var IPermission $permission */
        $permission = $this->permissionManager->getPermission(Application::PERMISSION_UPDATE_PROFILE_IMAGE);
        $context    = new Context();
        $context->addUser($contextUser);
        $permission->setContext($context);
        return $permission;
    }

    public function create(): void {
        if (null === $this->user) {

            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "No user found"
                )
            );
            return;

        }

        $put = $this->assetManager->writeProfilePicture(
            $this->parameters[0]
            , $this->user
        );

        if (false === $put) {
            parent::setResponse(
                $this->prepareResponse(
                    IResponse::RESPONSE_CODE_NOT_OK
                    , "Could not write user image"
                )
            );
            return;
        }

        parent::setResponse(
            $this->prepareResponse(
                IResponse::RESPONSE_CODE_OK
                , "Profile Image Updated"
            )
        );
    }

    private function prepareResponse(int $code, string $message): IResponse {
        $response = new DefaultResponse();
        $response->setCode(HTTP::OK);
        $response->addMessage(
            $code
            ,
            [
                "message" => $this->l10n->translate($message)
            ]
        );
        return $response;
    }

    public function afterCreate(): void {

    }

}