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

namespace Keestash\Core\DTO\Encryption\Credential;

use DateTime;
use KSP\Core\DTO\Encryption\Credential\IJsonCredential;
use KSP\Core\DTO\User\IJsonUser;

/**
 * Class Credential
 *
 * @package Keestash\Core\DTO\Encryption\Credential
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Credential implements IJsonCredential {

    /** @var int */
    private $id;
    /** @var string */
    private $secret;
    /** @var DateTime */
    private $createTs;
    /** @var IJsonUser */
    private $owner;

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return [
            "id"          => $this->getId()
            , "secret"    => $this->getSecret()
            , "create_ts" => $this->getCreateTs()
            , "owner"     => $this->getOwner()
        ];
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getSecret(): string {
        return $this->secret;
    }

    public function setSecret(string $secret): void {
        $this->secret = $secret;
    }

    public function getCreateTs(): DateTime {
        return $this->createTs;
    }

    public function setCreateTs(DateTime $createTs): void {
        $this->createTs = $createTs;
    }

    public function getOwner(): IJsonUser {
        return $this->owner;
    }

    public function setOwner(IJsonUser $owner): void {
        $this->owner = $owner;
    }

}
