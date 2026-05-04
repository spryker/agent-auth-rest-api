<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AgentAuthRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class AgentAuthExceptionFactory
{
    public function createInvalidLoginException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNAUTHORIZED,
            AgentAuthRestApiConfig::RESPONSE_CODE_INVALID_LOGIN,
            AgentAuthRestApiConfig::RESPONSE_DETAIL_INVALID_LOGIN,
        );
    }

    public function createFailedToImpersonateCustomerException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNAUTHORIZED,
            AgentAuthRestApiConfig::RESPONSE_CODE_FAILED_TO_IMPERSONATE_CUSTOMER,
            AgentAuthRestApiConfig::RESPONSE_DETAIL_FAILED_TO_IMPERSONATE_CUSTOMER,
        );
    }
}
