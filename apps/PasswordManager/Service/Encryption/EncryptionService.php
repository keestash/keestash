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

namespace KSA\PasswordManager\Service\Encryption;

use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\Repository\Derivation\DerivationException;
use Keestash\Exception\User\UserException;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IEncryptionService;

/**
 * Wrapper class for the core encryption service.
 * We just extend the core encryption service in order
 * to have a service within the PasswordManager service
 *
 * @package KSA\PasswordManager\Service\Encryption
 */
final readonly class EncryptionService {

    public function __construct(
        private IEncryptionService   $encryptionService
        , private ICredentialService $credentialService
    ) {
    }

    /**
     * @param ICredential $credential
     * @param string      $raw
     * @return string
     * @throws DerivationException
     * @throws EncryptionFailedException
     * @throws UserException
     */
    public function encrypt(ICredential $credential, string $raw): string {
        $key = $this->prepareKey($credential);
        return $this->encryptionService->encrypt($key, $raw);
    }

    /**
     * @param ICredential $credential
     * @return IKey
     * @throws DerivationException
     * @throws EncryptionFailedException
     * @throws UserException
     */
    private function prepareKey(ICredential $credential): IKey {
        $tempKey = new Key();
        $tempKey->setId(
            $credential->getId()
        );
        $tempKey->setCreateTs(
            $credential->getCreateTs()
        );
        $keyHolderCredential = $this->credentialService->createCredentialFromDerivation($credential->getKeyHolder());

        $decrypted = $this->encryptionService->decrypt($keyHolderCredential, $credential->getSecret());
        $tempKey->setSecret($decrypted);
        return $tempKey;
    }

    /**
     * @param ICredential $credential
     * @param string      $encrypted
     * @return string
     * @throws DerivationException
     * @throws EncryptionFailedException
     * @throws UserException
     */
    public function decrypt(ICredential $credential, string $encrypted): string {
        return $this->encryptionService->decrypt(
            $this->prepareKey($credential)
            , $encrypted
        );
    }

}
