<?php

namespace Tests\phpUnit\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Api\Services\MediaLibrary\MediaLibraryApiProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\phpUnit\CatrowebPhpUnit\CatrowebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Api\Services\MediaLibrary\MediaLibraryApiProcessor
 */
final class MediaLibraryApiProcessorTest extends CatrowebTestCase
{
  /**
   * @var MediaLibraryApiProcessor|MockObject
   */
  protected $object;

  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(MediaLibraryApiProcessor::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(MediaLibraryApiProcessor::class));
    $this->assertInstanceOf(MediaLibraryApiProcessor::class, $this->object);
  }

  /**
   * @group integration
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(AbstractApiProcessor::class, $this->object);
  }
}
