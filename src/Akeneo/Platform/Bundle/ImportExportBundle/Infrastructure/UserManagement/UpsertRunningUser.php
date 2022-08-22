<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;

class UpsertRunningUser
{
    private const AUTOMATED_USER_PREFIX = 'job_automated_';

    public function __construct(
        private UpsertUserHandlerInterface $upsertUserHandler,
        private RoleRepositoryInterface $roleRepository,
    ) {
    }

    public function execute(string $jobCode, array $userGroupCodes): void
    {
        $username = sprintf('%s%s', self::AUTOMATED_USER_PREFIX, $jobCode);
        $allRoleCodes = array_map(static fn (RoleInterface $role) => $role->getRole(), $this->roleRepository->findAll());

        $upsertUserCommand = UpsertUserCommand::job(
            $username,
            'fakepassword',
            sprintf('%s@example.com', $username),
            $jobCode,
            'Automated Job',
            $allRoleCodes,
            $userGroupCodes,
        );

        $this->upsertUserHandler->handle($upsertUserCommand);
    }
}
