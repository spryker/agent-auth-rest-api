<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AgentAuthRestApi\Dependency\Client;

use Generated\Shared\Transfer\OauthAccessTokenValidationRequestTransfer;
use Generated\Shared\Transfer\OauthAccessTokenValidationResponseTransfer;
use Generated\Shared\Transfer\OauthRequestTransfer;
use Generated\Shared\Transfer\OauthResponseTransfer;

class AgentAuthRestApiToOauthClientBridge implements AgentAuthRestApiToOauthClientInterface
{
    /**
     * @var \Spryker\Client\Oauth\OauthClientInterface
     */
    protected $oauthClient;

    /**
     * @param \Spryker\Client\Oauth\OauthClientInterface $oauthClient
     */
    public function __construct($oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    public function processAccessTokenRequest(OauthRequestTransfer $oauthRequestTransfer): OauthResponseTransfer
    {
        return $this->oauthClient->processAccessTokenRequest($oauthRequestTransfer);
    }

    public function validateAccessToken(
        OauthAccessTokenValidationRequestTransfer $authAccessTokenValidationRequestTransfer
    ): OauthAccessTokenValidationResponseTransfer {
        return $this->oauthClient->validateAccessToken($authAccessTokenValidationRequestTransfer);
    }
}
