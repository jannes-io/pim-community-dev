<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByOwnerUsernameQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogsByOwnerUsernameQuery implements GetCatalogsByOwnerUsernameQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @return array<Catalog>
     */
    public function execute(string $ownerUsername, int $offset = 0, int $limit = 100): array
    {
        $query = <<<SQL
            SELECT BIN_TO_UUID(akeneo_catalog.id) AS id, name, owner_id, is_enabled
            FROM akeneo_catalog
            JOIN oro_user ON oro_user.id = akeneo_catalog.owner_id
            WHERE oro_user.username = :owner_username
            ORDER BY akeneo_catalog.id
            LIMIT :offset, :limit
        SQL;

        $catalogs = $this->connection->executeQuery(
            $query,
            [
                'owner_username' => $ownerUsername,
                'limit' => $limit,
                'offset' => $offset,
            ],
            [
                'limit' => Types::INTEGER,
                'offset' => Types::INTEGER,
            ]
        )->fetchAllAssociative();

        return \array_map(static fn ($row) => new Catalog(
            (string) $row['id'],
            (string) $row['name'],
            (int) $row['owner_id'],
            (bool) $row['is_enabled'],
        ), $catalogs);
    }
}