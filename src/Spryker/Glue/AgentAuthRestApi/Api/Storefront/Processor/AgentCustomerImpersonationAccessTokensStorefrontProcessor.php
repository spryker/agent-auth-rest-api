<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AgentAuthRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\AgentCustomerImpersonationAccessTokensStorefrontResource;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Oauth\OauthClientInterface;
use Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig;
use Spryker\Glue\AgentAuthRestApi\Api\Storefront\Exception\AgentAuthExceptionFactory;

class AgentCustomerImpersonationAccessTokensStorefrontProcessor extends AbstractStorefrontProcessor
{
    public function __construct(
        protected OauthClientInterface $oauthClient,
        protected AgentAuthExceptionFactory $exceptionFactory = new AgentAuthExceptionFactory(),
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        return $this->processCustomerImpersonationGrant($data);
    }

    protected function processCustomerImpersonationGrant(
        AgentCustomerImpersonationAccessTokensStorefrontResource $resource,
    ): AgentCustomerImpersonationAccessTokensStorefrontResource {
        if ($resource->customerReference === null || $resource->customerReference === '') {
            throw $this->exceptionFactory->createFailedToImpersonateCustomerException();
        }

        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType(AgentAuthRestApiConfig::GRANT_TYPE_CUSTOMER_IMPERSONATION)
            ->setCustomerReference($resource->customerReference);

        $oauthResponseTransfer = $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);

        if ($oauthResponseTransfer->getIsValid() === false) {
            throw $this->exceptionFactory->createFailedToImpersonateCustomerException();
        }

        // $resource->id is intentionally left null so the JSON:API response emits data.id = null
        // (legacy Glue REST behavior). The id property is declared as identifier in the schema only
        // to satisfy API Platform's resource model; with a null value the IdNormalizer skips the
        // synthetic-identifier-suffix stripping that would otherwise mangle the self-link.
        $resource->accessToken = $oauthResponseTransfer->getAccessToken();
        $resource->tokenType = $oauthResponseTransfer->getTokenType();
        $resource->expiresIn = $oauthResponseTransfer->getExpiresIn();
        $resource->refreshToken = $oauthResponseTransfer->getRefreshToken();

        return $resource;
    }
}
