<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Processor\Validator;

use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AgentValidator implements AgentValidatorInterface
{
    /**
     * @var \Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig
     */
    protected $agentAuthRestApiConfig;

    public function __construct(AgentAuthRestApiConfig $agentAuthRestApiConfig)
    {
        $this->agentAuthRestApiConfig = $agentAuthRestApiConfig;
    }

    public function validate(RestRequestInterface $restRequest): ?RestErrorMessageTransfer
    {
        if (!$this->isAgentResource($restRequest) || $this->isAgent($restRequest)) {
            return null;
        }

        return (new RestErrorMessageTransfer())
            ->setStatus(Response::HTTP_UNAUTHORIZED)
            ->setCode(AgentAuthRestApiConfig::RESPONSE_CODE_AGENT_ONLY)
            ->setDetail(AgentAuthRestApiConfig::RESPONSE_DETAIL_AGENT_ONLY);
    }

    protected function isAgentResource(RestRequestInterface $restRequest): bool
    {
        return in_array(
            $restRequest->getResource()->getType(),
            $this->agentAuthRestApiConfig->getAgentResources(),
            true,
        );
    }

    protected function isAgent(RestRequestInterface $restRequest): bool
    {
        $restUserTransfer = $restRequest->getRestUser();

        if (!$restUserTransfer) {
            return false;
        }

        return (bool)$restRequest->getRestUser()->getIdAgent();
    }
}
