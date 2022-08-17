<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\Domain\Storage;

use Akeneo\UserManagement\Domain\Model\User;

interface FindUsers
{
    /** @return User[] */
    public function __invoke(
        ?string $search = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;
}
