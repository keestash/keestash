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

namespace KST;

use JsonException;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

abstract class TestCase extends FrameworkTestCase {

    private ServiceManager  $serviceManager;
    private ResponseService $responseService;
    private RequestService  $requestService;

    protected function getServiceManager(): ServiceManager {
        return $this->serviceManager;
    }

    protected function getService(string $name) {
        return $this->getServiceManager()->get($name);
    }

    protected function logResponse(ResponseInterface $response): void {
        /** @var LoggerInterface $logger */
        $logger = $this->getService(LoggerInterface::class);
        $logger->debug('response', [
            'status'    => $response->getStatusCode()
            , 'body'    => (string) $response->getBody()
            , 'headers' => $response->getHeaders()
        ]);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->serviceManager  = require __DIR__ . '/config/service_manager.php';
        $this->responseService = $this->serviceManager->get(ResponseService::class);
        $this->requestService  = $this->serviceManager->get(RequestService::class);
    }

    protected function getResponseService(): ResponseService {
        return $this->responseService;
    }

    protected function getRequestService(): RequestService {
        return $this->requestService;
    }

    protected function getUser(): IUser {
        return $this->serviceManager->get(IUserRepository::class)
            ->getUserById((string) Service\Service\UserService::TEST_USER_ID_2);
    }

    protected function getDefaultRequest(array $body = []): ServerRequestInterface {
        return $this->getRequestService()->getRequestWithToken(
            $this->getUser()
            , []
            , []
            , $body
        );
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws JsonException
     */
    protected function getResponseBody(ResponseInterface $response): array {
        return (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
    }

    public function assertInvalidResponse(ResponseInterface $response): void {
        $this->assertTrue(false === $this->responseService->isValidResponse($response));
    }

    public function assertValidResponse(ResponseInterface $response): void {
        $this->assertTrue(true === $this->responseService->isValidResponse($response));
    }

    public function assertStatusCode(int $statusCode, ResponseInterface $response): void {
        $this->assertTrue($statusCode === $response->getStatusCode());
    }

}
