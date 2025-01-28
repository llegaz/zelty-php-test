<?php

declare(strict_types=1);

namespace LLegaz\ZeltyPhpTest\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use LLegaz\ZeltyPhpTest\Helpers\BooleanValidator as SUT_4;
use LLegaz\ZeltyPhpTest\Helpers\InputValidator as SUT_2;
use LLegaz\ZeltyPhpTest\Helpers\JsonHelper as SUT_3;
use LLegaz\ZeltyPhpTest\Helpers\StringValidator as SUT;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

use function json_encode;

/**
 * Helpers units.
 *
 * @author Laurent LEGAZ <laurent@legaz.eu>
 *
 * @internal
 */
class HelpersTest extends \PHPUnit\Framework\TestCase
{
    private Request $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->request = new Request('POST', '/login');
    }

    public function testCredentialsValidatorHelper()
    {
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateLoginString($this->request, 'login')
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validatePasswordString(
                $this->request,
                'passw0rd'
            )
        );
    }

    public function testCredentialsValidatorHelperWithErrors()
    {
        $this->expectException(HttpBadRequestException::class);
        SUT::validateLoginString($this->request, 'login#');
    }

    public function testCredentialsValidatorHelperWithError2()
    {
        $this->expectException(HttpBadRequestException::class);
        SUT::validatePasswordString($this->request, ' passw0rd ');
    }

    public function testHexaDecimalValidatorHelper()
    {
        $this->assertStringMatchesFormat(
            '%x',
            SUT::validateAlphaNumString(
                $this->request,
                '6cd4aa6eac9f38bbb3417f274ff197a5dd302c480ba806bd'
            )
        );
    }

    public function testHexaDecimalValidatorHelperWithErrors()
    {
        $this->expectException(HttpBadRequestException::class);
        SUT::validateAlphaNumString($this->request, 'Not_an_Hexad3cimal_string');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testInputValidatorHelper()
    {
        $inputs = [
            'string'     => 'a simple string',
            'hex string' => '6cd4aa6eac9f38bbb3417f274ff197a5dd302c480ba806bd',
            'login'      => 'random_login',
            'password'   => 'soleil_doré1234',
        ];
        SUT_2::validateInputString($this->request, $inputs, 'string');
        SUT_2::validateInputHexString($this->request, $inputs, 'hex string');
        SUT_2::validateLogin($this->request, $inputs, 'login');
        SUT_2::validatePassword($this->request, $inputs);
    }

    public function testInputValidatorHelperWithError()
    {
        $this->expectException(HttpBadRequestException::class);
        SUT_2::validateInputString($this->request, [], 'no arg and no inputs');
    }

    public function testInputValidatorHelperWithError1()
    {
        $inputs = [
            'not a string' => false,
        ];
        $this->expectException(HttpBadRequestException::class);
        SUT_2::validateInputString($this->request, $inputs, 'not a string');
    }

    public function testInputValidatorHelperWithError2()
    {
        $inputs = [
            'hex string' => 'not a hex string',
        ];
        $this->expectException(HttpBadRequestException::class);
        SUT_2::validateInputHexString($this->request, $inputs, 'hex string');
    }

    public function testInputValidatorHelperWithError3()
    {
        $inputs = [
            'login' => 'wrong/login',
        ];
        $this->expectException(HttpBadRequestException::class);
        SUT_2::validateLogin($this->request, $inputs);
    }

    public function testInputValidatorHelperWithError4()
    {
        $inputs = [
            'password' => 'wrong password',
        ];
        $this->expectException(HttpBadRequestException::class);
        SUT_2::validatePassword($this->request, $inputs);
    }

    public function testJSonHelper()
    {
        $response = SUT_3::sendJsonResponse(
            $this->request,
            new Response(200),
            ['data' => 'some data']
        );
        $this->assertTrue($response instanceof Response);
        $this->assertObjectHasAttribute('headerNames', $response);
        $this->assertObjectHasAttribute('headers', $response);
        $this->assertArrayHasKey(0, $response->getHeader('Content-Type'));
        $this->assertEquals('application/json', $response->getHeader('Content-Type')[0]);
        $this->assertEquals(json_encode(['data' => 'some data']), $response->getBody());
    }

    // empty data response
    public function testJSonHelperWithError()
    {
        $this->expectException(HttpNotFoundException::class);
        SUT_3::sendJsonResponse($this->request, new Response(), []);
    }

    public function testJSonHelperHandleRequestWithoutHeaderError()
    {
        $this->expectException(HttpBadRequestException::class);
        SUT_3::handleJsonRequest($this->request);
    }

    public function testJSonHelperHandleRequestWithoutBodyError()
    {
        $request = (new Request('POST', '/login'))
            ->withAddedHeader('Content-Type', 'application/json')
        ;
        $this->expectException(HttpBadRequestException::class);
        SUT_3::handleJsonRequest($request);
    }

    public function testStringValidatorHelper()
    {
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateString(
                $this->request,
                'ceci est un test'
            )
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateConstrainedString(
                $this->request,
                'ceci est un test'
            )
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateConstrainedString(
                $this->request,
                'ceci est un test',
                'testArg',
                32,
                8
            )
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateConstrainedString(
                $this->request,
                'ceci est un test'
            )
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateLoginString(
                $this->request,
                'ceci_est_un_login'
            )
        );
    }

    public function testDateStringValidatorHelper()
    {
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateDateString(
                $this->request,
                '86-11-15',
                'test_date'
            )
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateDateString(
                $this->request,
                'Sat, 15 Nov 86',
                'test_date2'
            )
        );
        $this->assertStringMatchesFormat(
            '%s',
            SUT::validateDateString(
                $this->request,
                '1986/11/15',
                'test_date3'
            )
        );
    }

    public function testStringValidatorHelperWithError()
    {
        $this->expectException(HttpBadRequestException::class);

        SUT::validateConstrainedString($this->request, 'ceci est un test', 'testArg', 5);
    }

    public function testStringValidatorHelperWithError2()
    {
        $this->expectException(HttpBadRequestException::class);

        SUT::validateConstrainedString($this->request, 'ceci est un test', 'testArg', 50, 30);
    }

    public function testDateStringValidatorHelperWithError()
    {
        $this->expectException(HttpBadRequestException::class);

        SUT::validateDateString($this->request, '15/11/86');
    }

    public function testBoolValidatorHelper()
    {
        $this->assertTrue(SUT_4::validateBool($this->request, 'yes'));
        $this->assertFalse(SUT_4::validateBool($this->request, 'off'));
        $this->assertTrue(SUT_4::validateBool($this->request, 'true'));
        $this->assertFalse(SUT_4::validateBool($this->request, false));
    }

    public function testBoolValidatorHelperWithError()
    {
        $this->expectException(HttpBadRequestException::class);
        SUT_4::validateBool($this->request, 'no_a_boolean');
    }
}
