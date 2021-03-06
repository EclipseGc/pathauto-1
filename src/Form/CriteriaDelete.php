<?php
/**
 * @file
 * Contains \Drupal\pathauto\Form\CriteriaDelete.
 */

namespace Drupal\pathauto\Form;


use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\ctools\Form\ConditionDelete;

class CriteriaDelete extends ConditionDelete {
  protected function getRouteInfo() {
    return ['entity.pathauto_pattern.edit_form', ['machine_name' => $this->machine_name, 'step' => 'selection_criteria']];
  }

  protected function getConditions($cached_values) {
    /** @var \Drupal\pathauto\PathautoPatternInterface $pattern */
    $pattern = $cached_values['pathauto_pattern'];
    $conditions = [];
    foreach ($pattern->getSelectionConditions() as $uuid => $configuration) {
      $conditions[$uuid] = $configuration->getConfiguration();
    }
    return $conditions;
  }

  protected function setConditions($cached_values, $conditions) {
    $old_conditions = $this->getConditions($cached_values);
    $diff = array_diff(array_keys($old_conditions), array_keys($conditions));
    /** @var \Drupal\pathauto\PathautoPatternInterface $pattern */
    $pattern = $cached_values['pathauto_pattern'];
    // There should only be one item in $diff, but we'll loop anyway.
    foreach ($diff as $key) {
      $pattern->removeSelectionCondition($key);
    }
    return $cached_values;
  }

  protected function getContexts($cached_values) {
    /** @var \Drupal\pathauto\PathautoPatternInterface $pattern */
    $pattern = $cached_values['pathauto_pattern'];
    // @todo This is a total hack. The plugin that getType() represents should
    // be responsible for this.
    $type = $pattern->getType();
    $context_definition = new ContextDefinition('entity:' . $type);
    return [
      $type => new Context($context_definition),
    ];
  }

}
