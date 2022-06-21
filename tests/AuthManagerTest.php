<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Tests;

use DI\Container;
use LLegaz\ZeltyPhpTest\Authentication\AuthenticationManager as SUT;
use function strlen;

/**
 * @author Laurent LEGAZ <laurent@legaz.eu>
 *
 * @internal
 */
class AuthManagerTest extends \PHPUnit\Framework\TestCase
{
    private $sut;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $containerMock = $this->getMockBuilder(Container::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $authSettings = [
            'qop'   => 'auth',
            'realm' => 'zelty.fr',
        ];
        $containerMock->expects($this->any())
            ->method('get')
            ->willReturn($authSettings)
        ;

        $this->sut = new SUT($containerMock);
    }

    public function testGenCNonce()
    {
        $cnonce = $this->sut->generateClientNonce();
        $this->assertEquals(48, strlen($cnonce));
        $this->assertStringMatchesFormat('%x', $cnonce);
    }

    public function testValidateKeyedDigest()
    {
        $response   = '3882e6efb51c17d0c6ee97ad178e4922ebc4cf671bfa35f942c1ff9762727366';
        $fixedNonce = '6cd4aa6eac9f38bbb3417f274ff197a5dd302c480ba806bd';
        $this->sut->setSalt('5e4201c27882cd27b1b1ae9250338d99');
        $digest = $this->sut->getKeyedDigest(
            $this->sut->getA1Hash('login', 'passw0rd'),
            $this->sut->getA2Hash(),
            $fixedNonce,
            '00000001',
            $fixedNonce
        );
        $this->assertEqualsIgnoringCase($response, $digest);
        $this->assertTrue($this->sut->validateChallenge($response));
        $this->assertTrue($this->sut->hasValidateChallenge());
    }
}
