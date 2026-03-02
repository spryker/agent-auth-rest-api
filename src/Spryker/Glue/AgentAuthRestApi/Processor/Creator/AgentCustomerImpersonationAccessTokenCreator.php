<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Processor\Creator;

use Generated\Shared\Transfer\OauthRequestTransfer;
use Generated\Shared\Transfer\RestAgentCustomerImpersonationAccessTokensRequestAttributesTransfer;
use Spryker\Glue\AgentAuthRestApi\AgentAuthRestApiConfig;
use Spryker\Glue\AgentAuthRestApi\Dependency\Client\AgentAuthRestApiToOauthClientInterface;
use Spryker\Glue\AgentAuthRestApi\Processor\RestResponseBuilder\AgentAccessTokenRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class AgentCustomerImpersonationAccessTokenCreator implements AgentCustomerImpersonationAccessTokenCreatorInterface
{
    /**
     * @var \Spryker\Glue\AgentAuthRestApi\Dependency\Client\AgentAuthRestApiToOauthClientInterface
     */
    protected $oauthClient;

    /**
     * @var \Spryker\Glue\AgentAuthRestApi\Processor\RestResponseBuilder\AgentAccessTokenRestResponseBuilderInterface
     */
    protected $agentAccessTokenRestResponseBuilder;

    public function __construct(
        AgentAuthRestApiToOauthClientInterface $oauthClient,
        AgentAccessTokenRestResponseBuilderInterface $agentAccessTokenRestResponseBuilder
    ) {
        $this->oauthClient = $oauthClient;
        $this->agentAccessTokenRestResponseBuilder = $agentAccessTokenRestResponseBuilder;
    }

    public function create(
        RestRequestInterface $restRequest,
        RestAgentCustomerImpersonationAccessTokensRequestAttributesTransfer $restAgentCustomerImpersonationAccessTokensRequestAttributesTransfer
    ): RestResponseInterface {
        if (!$restRequest->getRestUser() || !$restRequest->getRestUser()->getIdAgent()) {
            return $this->agentAccessTokenRestResponseBuilder->createActionAvailableForAgentsOnlyErrorResponse();
        }

        $oauthRequestTransfer = (new OauthRequestTransfer())
            ->fromArray($restAgentCustomerImpersonationAccessTokensRequestAttributesTransfer->toArray(), true)
            ->setGrantType(AgentAuthRestApiConfig::GRANT_TYPE_CUSTOMER_IMPERSONATION);

        $oauthResponseTransfer = $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);

        if (!$oauthResponseTransfer->getIsValid()) {
            return $this->agentAccessTokenRestResponseBuilder->createFailedToImpersonateCustomerErrorResponse();
        }

        return $this->agentAccessTokenRestResponseBuilder->createAgentCustomerImpersonationAccessTokensRestResponse($oauthResponseTransfer);
    }
}
