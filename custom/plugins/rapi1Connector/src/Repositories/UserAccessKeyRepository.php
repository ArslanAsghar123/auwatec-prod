<?php

namespace Rapidmail\Shopware\Repositories;

use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\User\Aggregate\UserAccessKey\UserAccessKeyEntity;

class UserAccessKeyRepository
{
    private EntityRepository $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function createUserAccessKey(Context $context, string $userId): UserAccessKeyEntity
    {
        $userAccessKey = new UserAccessKeyEntity();
        $userAccessKey->setUserId($userId);
        $userAccessKey->setAccessKey(AccessKeyHelper::generateAccessKey('user'));
        $userAccessKey->setSecretAccessKey(AccessKeyHelper::generateSecretAccessKey());
        $userAccessKey->setCreatedAt(new \DateTime());

        $this->entityRepository->upsert([
            [
                'userId' => $userAccessKey->getUserId(),
                'accessKey' => $userAccessKey->getAccessKey(),
                'secretAccessKey' => $userAccessKey->getSecretAccessKey(),
            ],
        ], $context);

        return $userAccessKey;
    }
}
