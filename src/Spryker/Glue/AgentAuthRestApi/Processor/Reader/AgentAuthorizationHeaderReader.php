<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Processor\Reader;

use Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToOauthServiceInterface;
use Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToUtilEncodingServiceInterface;

class AgentAuthorizationHeaderReader implements AgentAuthorizationHeaderReaderInterface
{
    /**
     * @var \Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToOauthServiceInterface
     */
    protected $oauthService;

    /**
     * @var \Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    public function __construct(
        AgentAuthRestApiToOauthServiceInterface $oauthService,
        AgentAuthRestApiToUtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->oauthService = $oauthService;
        $this->utilEncodingService = $utilEncodingService;
    }

    public function getIdAgentFromOauthAccessToken(string $agentAccessTokenHeader): ?int
    {
        $agentAccessToken = $this->extractToken($agentAccessTokenHeader);
        $agentAccessTokenType = $this->extractTokenType($agentAccessTokenHeader);

        if (!$agentAccessToken || !$agentAccessTokenType) {
            return null;
        }

        $oauthAccessTokenDataTransfer = $this->oauthService->extractAccessTokenData($agentAccessToken);
        $decodedOauthUserId = $this->utilEncodingService->decodeJson($oauthAccessTokenDataTransfer->getOauthUserId(), true);

        return $decodedOauthUserId['id_agent'] ?? null;
    }

    public function extractToken(string $authorizationToken): ?string
    {
        $pieces = preg_split('/\s+/', $authorizationToken);

        return $pieces[1] ?? null;
    }

    public function extractTokenType(string $authorizationToken): ?string
    {
        $pieces = preg_split('/\s+/', $authorizationToken);

        return $pieces[0] ?? null;
    }
}
