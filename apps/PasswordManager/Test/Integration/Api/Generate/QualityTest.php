<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Integration\Api\Generate;

use KSA\PasswordManager\Api\Generate\Quality;
use KSA\PasswordManager\Test\TestCase;
use KSP\Api\IResponse;

class QualityTest extends TestCase {

    public function testWithMissingData(): void {
        /** @var Quality $quality */
        $quality  = $this->getService(Quality::class);
        $response = $quality->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testBadQuality(): void {
        /** @var Quality $quality */
        $quality      = $this->getService(Quality::class);
        $response     = $quality->handle(
            $this->getDefaultRequest()
                ->withAttribute('value', 'abcd')
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($responseBody['quality'] === -1);
    }

}