<?php

/**
 * @file
 * Contains \Drupal\name\Tests\NameAutocompleteTest.
 */

namespace Drupal\name\Tests;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\name\Controller\NameAutocompleteController;
use Drupal\name\NameAutocomplete;
use Drupal\simpletest\KernelTestBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Tests name autocomplete.

 * @group name
 */
class NameAutocompleteTest extends KernelTestBase {

  use NameTestTrait;

  public static $modules = array(
    'name',
    'field',
    'entity_test',
    'system',
    'user'
  );

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * @var FieldDefinitionInterface
   */
  protected $field;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(self::$modules);

    $this->entityManager = \Drupal::entityManager();
    $this->entityManager->onEntityTypeCreate(\Drupal::entityManager()->getDefinition('entity_test'));

    $this->field = $this->createNameField('field_name_test', 'entity_test', 'entity_test');
  }

  public function testAutocompleteController() {
    $autocomplete = NameAutocompleteController::create($this->container);
    $request = new Request();
    $request->attributes->add(array('q' => 'Bob'));

    try {
      $autocomplete->autocomplete($request, 'field_name_test', 'entity_test', 'invalid_bundle', 'family');
    } catch (\Exception $e) {
      $this->assertTrue($e instanceof AccessDeniedHttpException);
    }

    $result = $autocomplete->autocomplete($request, 'field_name_test', 'entity_test', 'entity_test', 'family');
    $this->assertTrue($result instanceof JsonResponse);
  }

  public function testAutocomplete() {
    $autocomplete = new NameAutocomplete(\Drupal::database());
    $autocomplete->getMatches($this->field, 'name', 'Bob');
  }

}
