<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Processor\Creator;

use Generated\Shared\Transfer\RestAgentAccessTokensRequestAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface AgentAccessTokenCreatorInterface
{
    public function createAccessToken(
        RestRequestInterface $restRequest,
        RestAgentAccessTokensRequestAttributesTransfer $restAgentAccessTokensRequestAttributesTransfer
    ): RestResponseInterface;
}
