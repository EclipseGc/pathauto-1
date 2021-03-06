<?php

/**
 * @file
 * Contains Drupal\pathauto\Entity\PathautoPattern.
 */

namespace Drupal\pathauto\Entity;

use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\pathauto\PathautoPatternInterface;

/**
 * Defines the Pathauto pattern entity.
 *
 * @ConfigEntityType(
 *   id = "pathauto_pattern",
 *   label = @Translation("Pathauto pattern"),
 *   handlers = {
 *     "list_builder" = "Drupal\pathauto\PathautoPatternListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\pathauto\Form\PathautoPatternDeleteForm"
 *     },
 *     "wizard" = {
 *       "add" = "Drupal\pathauto\Wizard\PatternWizardAdd",
 *       "edit" = "Drupal\pathauto\Wizard\PatternWizard"
 *     }
 *   },
 *   config_prefix = "pathauto_pattern",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class PathautoPattern extends ConfigEntityBase implements PathautoPatternInterface {
  /**
   * The Pathauto pattern ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Pathauto pattern label.
   *
   * @var string
   */
  protected $label;

  /**
   * The pattern type.
   *
   * A string denoting the type of pathauto pattern this is. For a node path
   * this would be 'node', for users it would be 'user', and so on. This allows
   * for arbitrary non-entity patterns to be possible if applicable.
   *
   * @var string
   */
  protected $type;

  /**
   * A tokenized string for alias generation.
   *
   * @var string
   */
  protected $pattern;

  /**
   * The plugin configuration for the selection criteria condition plugins.
   *
   * @var array
   */
  protected $selection_criteria = [];

  /**
   * The selection logic for this pattern entity (either 'and' or 'or').
   *
   * @var string
   */
  protected $selection_logic = 'and';

  /**
   * @var int
   */
  protected $weight = 0;

  /**
   * The plugin collection that holds the selection criteria condition plugins.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $selectionConditionCollection;

  /**
   * {@inheritdoc}
   *
   * Not using core's default logic around ConditionPluginCollection since it
   * incorrectly assumes no condition will ever be applied twice.
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $criteria = [];
    foreach ($this->getSelectionConditions() as $id => $condition) {
      $criteria[$id] = $condition->getConfiguration();
    }
    $this->selection_criteria = $criteria;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    foreach ($this->getSelectionConditions() as $instance) {
      $this->calculatePluginDependencies($instance);
    }

    return $this->getDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function getPattern() {
    return $this->pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function setPattern($pattern) {
    $this->pattern = $pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionConditions() {
    if (!$this->selectionConditionCollection) {
      $this->selectionConditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('selection_criteria'));
    }
    return $this->selectionConditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectionCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getSelectionConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionCondition($condition_id) {
    return $this->getSelectionConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSelectionCondition($condition_id) {
    $this->getSelectionConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->selection_logic;
  }

}
