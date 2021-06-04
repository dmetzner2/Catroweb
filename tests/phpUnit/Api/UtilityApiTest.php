<?php

declare(strict_types=1);

namespace Tests\phpUnit\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Utility\UtilityApiFacade;
use App\Api\Services\Utility\UtilityApiLoader;
use App\Api\UtilityApi;
use App\Entity\Survey;
use OpenAPI\Server\Api\UtilityApiInterface;
use OpenAPI\Server\Model\SurveyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\UtilityApi
 */
class UtilityApiTest extends CatrowebTestCase
{
  /**
   * @var UtilityApi|MockObject
   */
  protected $object;

  /**
   * @var UtilityApiFacade|MockObject
   */
  protected $facade;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UtilityApi::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;

    $this->facade = $this->createMock(UtilityApiFacade::class);
    $this->mockProperty(UtilityApi::class, $this->object, 'facade', $this->facade);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(UtilityApi::class));
    $this->assertInstanceOf(UtilityApi::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiController::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassImplements(): void
  {
    $this->assertInstanceOf(UtilityApiInterface::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testCtor(): void
  {
    $this->object = new UtilityApi($this->facade);
    $this->assertInstanceOf(UtilityApi::class, $this->object);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\UtilityApi::healthGet
   */
  public function testHealthCheck(): void
  {
    $response_code = null;
    $response_headers = [];

    $response = $this->object->healthGet($response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NO_CONTENT, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGetNotFound(): void
  {
    $response_code = null;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getActiveSurvey')->willReturn(null);
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_NOT_FOUND, $response_code);
    $this->assertNull($response);
  }

  /**
   * @group unit
   * @small
   * @covers \App\Api\UtilityApi::surveyLangCodeGet
   */
  public function testSurveyLangCodeGet(): void
  {
    $response_code = null;
    $response_headers = [];

    $loader = $this->createMock(UtilityApiLoader::class);
    $loader->method('getActiveSurvey')->willReturn($this->createMock(Survey::class));
    $this->facade->method('getLoader')->willReturn($loader);

    $response = $this->object->surveyLangCodeGet('de', null, $response_code, $response_headers);

    $this->assertEquals(Response::HTTP_OK, $response_code);

    $this->assertInstanceOf(SurveyResponse::class, $response);
  }
}
