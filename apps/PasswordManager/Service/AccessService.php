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

namespace KSA\PasswordManager\Service;

use KSA\PasswordManager\Entity\Node\Node;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Core\Access\IAccessService;

class AccessService {

    public function __construct(private readonly IAccessService $accessService) {
    }

    public function hasAccess(Node $node, IUser $user): bool {
        return $this->accessService->hasAccess($node, $user)
            || $node->isSharedTo($user);
    }

}
