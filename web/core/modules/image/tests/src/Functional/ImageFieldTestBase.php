<?php

declare(strict_types=1);

namespace Drupal\Tests\image\Functional;

use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * @todo Test the following functions.
 *
 * In file:
 * - image.effects.inc:
 *   image_style_generate()
 *   \Drupal\image\ImageStyleInterface::createDerivative()
 *
 * - image.module:
 *   image_style_options()
 *   \Drupal\image\ImageStyleInterface::flush()
 */

/**
 * This class provides methods specifically for testing Image's field handling.
 */
abstract class ImageFieldTestBase extends BrowserTestBase {

  use ImageFieldCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'image',
    'field_ui',
    'image_module_test',
  ];

  /**
   * A user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
      $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    }

    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'access administration pages',
      'administer site configuration',
      'administer content types',
      'administer node fields',
      'administer nodes',
      'create article content',
      'edit any article content',
      'delete any article content',
      'administer image styles',
      'administer node display',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Preview an image in a node.
   *
   * @param \Drupal\Core\Image\ImageInterface $image
   *   A file object representing the image to upload.
   * @param string $field_name
   *   Name of the image field the image should be attached to.
   * @param string $type
   *   The type of node to create.
   */
  public function previewNodeImage($image, $field_name, $type) {
    $edit = [
      'title[0][value]' => $this->randomMachineName(),
    ];
    $edit['files[' . $field_name . '_0]'] = \Drupal::service('file_system')->realpath($image->uri);
    $this->drupalGet('node/add/' . $type);
    $this->submitForm($edit, 'Preview');
  }

  /**
   * Upload an image to a node.
   *
   * @param \stdClass $image
   *   A file object representing the image to upload.
   * @param string $field_name
   *   Name of the image field the image should be attached to.
   * @param string $type
   *   The type of node to create.
   * @param string $alt
   *   The alt text for the image. Use if the field settings require alt text.
   */
  public function uploadNodeImage($image, $field_name, $type, $alt = '') {
    $edit = [
      'title[0][value]' => $this->randomMachineName(),
    ];
    $edit['files[' . $field_name . '_0]'] = \Drupal::service('file_system')->realpath($image->uri);
    $this->drupalGet('node/add/' . $type);
    $this->submitForm($edit, 'Save');
    if ($alt) {
      // Add alt text.
      $this->submitForm([$field_name . '[0][alt]' => $alt], 'Save');
    }

    // Retrieve ID of the newly created node from the current URL.
    $matches = [];
    preg_match('/node\/([0-9]+)/', $this->getUrl(), $matches);
    return $matches[1] ?? FALSE;
  }

  /**
   * Retrieves the fid of the last inserted file.
   */
  protected function getLastFileId() {
    return (int) \Drupal::entityQueryAggregate('file')
      ->accessCheck(FALSE)
      ->aggregate('fid', 'max')
      ->execute()[0]['fid_max'];
  }

}
