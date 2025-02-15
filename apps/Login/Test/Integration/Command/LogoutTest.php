<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Login\Test\Integration\Command;

use KSA\Login\Test\Integration\TestCase;
use KSP\Command\IKeestashCommand;
use Ramsey\Uuid\Uuid;

class LogoutTest extends TestCase {

    public function testLogout(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $headers  = $this->login($user, $password);
        $command  = $this->getCommandTester("login:logout");
        $command->setInputs(
            [
                'username' => $user->getName()
            ]
        );
        $command->execute([]);
        $this->assertTrue(IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL === $command->getStatusCode());
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testNonExistingUser(): void {
        $command = $this->getCommandTester("login:logout");
        $command->setInputs(
            [
                'username' => Uuid::uuid4()->toString()
            ]
        );
        $result = $command->execute([]);
        $this->assertTrue($result === IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL);
    }

}
