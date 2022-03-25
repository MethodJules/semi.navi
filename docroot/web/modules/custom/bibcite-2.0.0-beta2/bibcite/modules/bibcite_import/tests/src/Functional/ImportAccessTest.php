<?php

namespace Drupal\Tests\bibcite_import\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test access to import form.
 *
 * @group bibcite
 */
class ImportAccessTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'text',
    'bibcite',
    'bibcite_entity',
    'bibcite_import',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * Test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->user = $this->drupalCreateUser([
      'create bibcite_reference',
      'edit own bibcite_reference',
      'edit any bibcite_reference',
      'view bibcite_reference',
      'administer bibcite',
    ]);
  }

  /**
   * Test Import form.
   */
  public function testImportForm() {
    $this->drupalLogin($this->user);

    $this->drupalGet('admin/content/bibcite/reference/import');
    $this->assertSession()->statusCodeEquals(200);

    $simple_user = $this->drupalCreateUser([
      'create bibcite_reference',
      'edit own bibcite_reference',
      'edit any bibcite_reference',
      'view bibcite_reference',
    ]);
    $this->drupalLogin($simple_user);
    $this->drupalGet('admin/content/bibcite/reference/import');
    $this->assertSession()->statusCodeEquals(403);

    $user_import = $this->drupalCreateUser([
      'create bibcite_reference',
      'edit own bibcite_reference',
      'edit any bibcite_reference',
      'view bibcite_reference',
      'bibcite import',
    ]);
    $this->drupalLogin($user_import);
    $this->drupalGet('admin/content/bibcite/reference/import');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Test Populate form.
   */
  public function testPopulateForm() {
    $this->drupalLogin($this->user);

    $this->drupalGet('admin/content/bibcite/reference/populate');
    $this->assertSession()->statusCodeEquals(200);

    $simple_user = $this->drupalCreateUser([
      'create bibcite_reference',
      'edit own bibcite_reference',
      'edit any bibcite_reference',
      'view bibcite_reference',
    ]);
    $this->drupalLogin($simple_user);
    $this->drupalGet('admin/content/bibcite/reference/populate');
    $this->assertSession()->statusCodeEquals(403);

    $user_populate = $this->drupalCreateUser([
      'create bibcite_reference',
      'edit own bibcite_reference',
      'edit any bibcite_reference',
      'view bibcite_reference',
      'bibcite populate',
    ]);
    $this->drupalLogin($user_populate);
    $this->drupalGet('admin/content/bibcite/reference/populate');
    $this->assertSession()->statusCodeEquals(200);
  }

}
