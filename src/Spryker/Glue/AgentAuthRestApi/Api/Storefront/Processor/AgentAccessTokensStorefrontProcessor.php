<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AgentAuthRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\AgentAccessTokensStorefrontResource;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Oauth\OauthClientInterface;
use Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig;
use Spryker\Glue\AgentAuthRestApi\Api\Storefront\Exception\AgentAuthExceptionFactory;

class AgentAccessTokensStorefrontProcessor extends AbstractStorefrontProcessor
{
    public function __construct(
        protected OauthClientInterface $oauthClient,
        protected AgentAuthExceptionFactory $exceptionFactory = new AgentAuthExceptionFactory(),
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        return $this->processAgentCredentialsGrant($data);
    }

    protected function processAgentCredentialsGrant(AgentAccessTokensStorefrontResource $resource): AgentAccessTokensStorefrontResource
    {
        if ($resource->username === null || $resource->username === '' || $resource->password === null || $resource->password === '') {
            throw $this->exceptionFactory->createInvalidLoginException();
        }

        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->setGrantType(AgentAuthRestApiConfig::GRANT_TYPE_AGENT_CREDENTIALS)
            ->setUsername($resource->username)
            ->setPassword($resource->password);

        $oauthResponseTransfer = $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);

        if ($oauthResponseTransfer->getIsValid() === false) {
            throw $this->exceptionFactory->createInvalidLoginException();
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
