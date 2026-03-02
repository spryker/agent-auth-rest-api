<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestUserTransfer;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface RestUserMapperInterface
{
    public function mapAgentDataToRestUserTransfer(RestUserTransfer $restUserTransfer, RestRequestInterface $restRequest): RestUserTransfer;
}
