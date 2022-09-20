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

namespace Keestash\Core\Repository\Queue;

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Repository\Queue\IQueueRepository;

class QueueRepository implements IQueueRepository {

    private IDateTimeService $dateTimeService;
    private IBackend         $backend;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
    }

    public function getQueue(): array {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'q.id'
                , 'q.create_ts'
                , 'q.priority'
                , 'q.attempts'
                , 'q.reserved_ts'
                , 'q.payload'
                , 'q.type'
                , 'q.stamps'
            ]
        )
            ->from('queue', 'q');

        $result = $queryBuilder->executeQuery();
        return $result->fetchAllAssociative();
    }

    public function getSchedulableMessages(): array {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $fiveMinutesAgo = new DateTime();
        $fiveMinutesAgo = $fiveMinutesAgo->modify('-5 minute');
        $queryBuilder->select(
            [
                'q.id'
                , 'q.create_ts'
                , 'q.priority'
                , 'q.attempts'
                , 'q.reserved_ts'
                , 'q.payload'
                , 'q.type'
                , 'q.stamps'
            ]
        )
            ->from('queue', 'q')
            ->where('q.attempts < ?')
            ->andWhere('q.reserved_ts < ?')
            ->setParameter(0, 3)
            ->setParameter(1, $this->dateTimeService->toYMDHIS($fiveMinutesAgo));

        $result = $queryBuilder->executeQuery();
        return $result->fetchAllAssociative();
    }

    public function delete(IMessage $message): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->delete(
            'queue'
        )
            ->where('id = ?')
            ->setParameter(0, $message->getId())
            ->executeStatement() !== 0;
    }

    public function insert(IMessage $message): IMessage {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`queue`")
            ->values(
                [
                    "`id`"            => '?'
                    , "`priority`"    => '?'
                    , "`attempts`"    => '?'
                    , "`payload`"     => '?'
                    , "`type`"        => '?'
                    , "`reserved_ts`" => '?'
                    , "`create_ts`"   => '?'
                    , "`stamps`"      => '?'
                ]
            )
            ->setParameter(0, $message->getId())
            ->setParameter(1, $message->getPriority())
            ->setParameter(2, $message->getAttempts())
            ->setParameter(3, json_encode($message->getPayload()))
            ->setParameter(4, $message->getType())
            ->setParameter(5, $this->dateTimeService->toYMDHIS($message->getReservedTs()))
            ->setParameter(6, $this->dateTimeService->toYMDHIS($message->getCreateTs()))
            ->setParameter(7, json_encode($message->getStamps()->toArray()))
            ->executeStatement();

        return $message;
    }

    public function update(IMessage $message): IMessage {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->update('`queue`')
            ->set('`priority`', '?')
            ->set('`attempts`', '?')
            ->set('`payload`', '?')
            ->set('`type`', '?')
            ->set('`reserved_ts`', '?')
            ->set('`create_ts`', '?')
            ->set('`stamps`', '?')
            ->where('`id` = ?')
            ->setParameter(0, $message->getPriority())
            ->setParameter(1, $message->getAttempts())
            ->setParameter(2, json_encode($message->getPayload()))
            ->setParameter(3, $message->getType())
            ->setParameter(4, $this->dateTimeService->toYMDHIS($message->getReservedTs()))
            ->setParameter(5, $this->dateTimeService->toYMDHIS($message->getCreateTs()))
            ->setParameter(6, json_encode($message->getStamps()->toArray()))
            ->setParameter(7, $message->getId());

        $rowCount = $queryBuilder->executeStatement();

        if (0 === $rowCount) {
            throw new KeestashException();
        }

        return $message;
    }

}