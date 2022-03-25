<?php

namespace Drupal\Tests\bibcite_entity\Kernel;

use Drupal\bibcite_entity\Entity\Contributor;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test contributor entity.
 *
 * @group bibcite
 */
class ContributorTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'field',
    'bibcite',
    'bibcite_entity',
  ];

  // @todo Add leading title to name.
  /**
   * Name for tests.
   *
   * @var string
   */
  protected $name = 'Mr. Jüan Martinez (Martin) de Lorenzo y Gutierez Jr.';

  /**
   * Parts of name for tests.
   *
   * @var array
   */
  protected $nameParts = [
    'prefix' => 'Mr.',
    'first_name' => 'Jüan',
    'middle_name' => 'Martinez',
    'last_name' => 'de Lorenzo y Gutierez',
    'nick' => 'Martin',
    'suffix' => 'Jr.',
  ];

  /**
   * Test setting up of Contributor name.
   */
  public function testContributorName() {

    $config = \Drupal::configFactory()->getEditable('bibcite_entity.contributor.settings');

    $config->set('full_name_pattern', '@prefix @first_name @middle_name @nick @last_name @suffix')->save();
    $entity = Contributor::create($this->nameParts);
    $this->assertEquals('Mr. Jüan Martinez Martin de Lorenzo y Gutierez Jr.', $entity->name->value);
    $config->set('full_name_pattern', '@prefix @first_name @last_name @suffix')->save();
    $this->assertEquals('Mr. Jüan de Lorenzo y Gutierez Jr.', $entity->name->value);

    $entity = Contributor::create();
    $entity->name = $this->name;
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals($value, $entity->{$part}->value);
    }

    $entity = Contributor::create();
    $entity->name = [$this->name];
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals($value, $entity->{$part}->value);
    }

    $entity = Contributor::create();
    $entity->name = ['value' => $this->name];
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals($value, $entity->{$part}->value);
    }

    $entity = Contributor::create();
    $entity->name = [['value' => $this->name]];
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals($value, $entity->{$part}->value);
    }
  }

  /**
   * Test unset name parts by full name.
   */
  public function testUnsetContributorName() {
    $entity = Contributor::create();
    $entity->name = $this->name;
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals($value, $entity->{$part}->value);
    }
    $entity->name = NULL;
    foreach ($this->nameParts as $part => $value) {
      $this->assertNull($entity->{$part}->value);
    }
  }

  /**
   * Test clearing name parts by full name.
   */
  public function testClearContributorName() {
    $entity = Contributor::create();
    $entity->name = $this->name;
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals($value, $entity->{$part}->value);
    }
    $entity->name = '';
    foreach ($this->nameParts as $part => $value) {
      $this->assertEquals('', $entity->{$part}->value);
    }
  }

}
