<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Processor\Reader;

interface AgentAuthorizationHeaderReaderInterface
{
    public function getIdAgentFromOauthAccessToken(string $agentAccessTokenHeader): ?int;

    public function extractToken(string $authorizationToken): ?string;

    public function extractTokenType(string $authorizationToken): ?string;
}
