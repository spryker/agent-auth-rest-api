<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\AgentAuthRestApi\Processor\Reader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OauthAccessTokenDataTransfer;
use Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToOauthServiceInterface;
use Spryker\Glue\AgentAuthRestApi\Dependency\Service\AgentAuthRestApiToUtilEncodingServiceBridge;
use Spryker\Glue\AgentAuthRestApi\Processor\Reader\AgentAuthorizationHeaderReader;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use SprykerTest\Glue\AgentAuthRestApi\AgentAuthRestApiTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group AgentAuthRestApi
 * @group Processor
 * @group Reader
 * @group AgentAuthorizationHeaderReaderTest
 * Add your own group annotations below this line
 */
class AgentAuthorizationHeaderReaderTest extends Unit
{
    /**
     * @var \SprykerTest\Glue\AgentAuthRestApi\AgentAuthRestApiTester
     */
    protected AgentAuthRestApiTester $tester;

    public function testGetIdAgentFromOauthAccessTokenReturnsNullWhenJwtSubClaimIsNotJsonObject(): void
    {
        // Arrange: underlying service returns int (what json_decode("1234567890") produces)
        $utilEncodingServiceMock = $this->createMock(UtilEncodingServiceInterface::class);
        $utilEncodingServiceMock->method('decodeJson')->willReturn(1234567890);

        $oauthServiceMock = $this->createMock(AgentAuthRestApiToOauthServiceInterface::class);
        $oauthServiceMock->method('extractAccessTokenData')
            ->willReturn((new OauthAccessTokenDataTransfer())->setOauthUserId('1234567890'));

        $reader = new AgentAuthorizationHeaderReader(
            $oauthServiceMock,
            new AgentAuthRestApiToUtilEncodingServiceBridge($utilEncodingServiceMock),
        );

        // Act
        $result = $reader->getIdAgentFromOauthAccessToken('Bearer some.jwt.token');

        // Assert
        $this->assertNull($result);
    }
}
