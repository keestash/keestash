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

namespace Keestash\Core\Repository\ApiLog;

use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\DTO\Instance\Request\IAPIRequest;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;

class ApiLogRepository extends AbstractRepository implements IApiLogRepository {

    public function log(IAPIRequest $request): ?int {
        $sql       = "insert into `apilog` (
                    `token_name`
                    , `token`
                    , `user_id`
                    , `start_ts`
                    , `end_ts`
                    , `route`
                    )
                    values (
                            :token_name
                            , :token
                            , :user_id
                            , :start_ts
                            , :end_ts
                            , :route
)";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return null;

        $tokenName = $request->getToken()->getName();
        $token     = $request->getToken()->getValue();
        $userId    = $request->getToken()->getUser()->getId();
        $startTs   = $request->getStart();
        $endTs     = $request->getEnd();
        $route     = $request->getRoute();

        $statement->bindParam("token_name", $tokenName);
        $statement->bindParam("token", $token);
        $statement->bindParam("user_id", $userId);
        $statement->bindParam("start_ts", $startTs);
        $statement->bindParam("end_ts", $endTs);
        $statement->bindParam("route", $route);

        if (false === $statement->execute()) return null;

        $lastInsertId = (int) parent::getLastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;
    }

    public function removeForUser(IUser $user): bool {
        $sql       = "DELETE FROM `apilog` WHERE `user_id` = :user_id;";
        $statement = $this->prepareStatement($sql);

        if (null === $statement) return false;
        $userId = $user->getId();
        $statement->bindParam("user_id", $userId);
        $statement->execute();
        return false === $this->hasErrors($statement->errorCode());
    }

}
