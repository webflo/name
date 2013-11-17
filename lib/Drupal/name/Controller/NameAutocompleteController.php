<?php

/**
 * @file
 * Contains \Drupal\name\Controller\NameAutocompleteController.
 */
namespace Drupal\name\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\name\NameAutocomplete;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller routines for name autocompletion routes.
 */
class NameAutocompleteController implements ContainerInjectionInterface {

  /**
   * The name autocomplete helper class to find matching name values.
   *
   * @var \Drupal\name\NameAutocomplete
   */
  protected $nameAutocomplete;

  /**
   * Constructs an NameAutocompleteController object.
   *
   * @param \Drupal\name\NameAutocomplete $name_autocomplete
   *   The name autocomplete helper class to find matching name values.
   */
  public function __construct(NameAutocomplete $name_autocomplete) {
    $this->nameAutocomplete = $name_autocomplete;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('name.autocomplete')
    );
  }

  /**
   * Returns response for the name autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   *
   * @see \Drupal\name\NameAutocomplete::getMatches()
   */
  public function autocomplete(Request $request) {
    $field_instance = $request->attributes->get('field_name');
    if ($field_instance = entity_load('field_instance', $field_instance)) {
      if ($field_instance->getFieldType() == 'name') {
        $matches = $this->nameAutocomplete->getMatches($field_instance, $request->attributes->get('component'), $request->query->get('q'));
        return new JsonResponse($matches);
      }
    }

    throw new NotFoundHttpException();
  }

}
