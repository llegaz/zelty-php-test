<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Tests;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use LLegaz\ZeltyPhpTest\Authentication\TokenStore\TokenStore as SUT;
use LLegaz\ZeltyPhpTest\Entities\Token;
use LLegaz\ZeltyPhpTest\Entities\User;
use function base64_decode;

/**
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 *
 * @internal
 */
class TokenStoreTest extends \PHPUnit\Framework\TestCase
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
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['persist', 'flush', 'getRepository', 'remove'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $repoMock = $this->getMockBuilder(EntityRepository::class)
            ->setMethods(['findOneBy'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $repoMock->expects($this->any())
            ->method('findOneBy')
            ->willReturn(new Token())
        ;
        $emMock->expects($this->any())
            ->method('getRepository')
            ->with(Token::class)
            ->willReturn($repoMock)
        ;
        $containerMock->expects($this->once())
            ->method('get')
            ->with(EntityManager::class)
            ->willReturn($emMock)
        ;

        $this->sut = new SUT($containerMock);
    }

    public function testCreateToken()
    {
        $user  = (new User())->generateUserId();
        $token = $this->sut->create($user);
        $this->assertStringMatchesFormat('%x', base64_decode($token, true));
    }

    public function testReadToken()
    {
        $this->assertTrue(
            $this->sut->read('YzEzOTVhMDQ2ZTM1NTA3YTIxODZiODZiYWQ5MmI0ODI4ODI1ZjQxYTk0MmE=') instanceof Token
        );
    }

    public function testRevokeToken()
    {
        $token = new Token();
        $token->setTokenId('1234')
            ->setUserId('1234')
            ->setExpiry('15-11-1986')
        ;
        $this->assertTrue($token->isExpired());

        if ($token->isExpired()) {
            $this->sut->revoke($token);
        }
    }
}
