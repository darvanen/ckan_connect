<?php

namespace Drupal\ckan_connect\Ckan\Crud;

use Drupal\ckan_connect\Ckan\CkanApiBase;
use Drupal\ckan_connect\Client\CkanClient;

/**
 * Class CkanCrudBase
 *
 * Any CKAN object that responds to all CRUD requests.
 */
abstract class CkanCrudBase extends CkanApiBase implements CkanCrudInterface {

  /**
   * {@inheritdoc}
   */
  protected $validActions = [
    CkanClient::CKAN_ACTION_LIST,
    CkanClient::CKAN_ACTION_SHOW,
    CkanClient::CKAN_ACTION_CREATE,
    CkanClient::CKAN_ACTION_UPDATE,
    CkanClient::CKAN_ACTION_DELETE,
  ];

  /**
   * @var string $id
   *   The uuid formatted ID generated by CKAN for this object.
   */
  protected $id;

  /**
   * @var string $action
   */
  protected $action;

  /**
   * CkanApiBase constructor.
   *
   * @param string $id
   *   The uuid generated by CKAN, may be an empty string for new objects
   *   intended for create actions.
   */
  public function __construct($id = '') {
    $this->id = $id;
  }

  /**
   * Set the action that the API client should perform with this object.
   *
   * @param string $action
   *
   * @return bool
   */
  public function setAction($action) {
    if (in_array($action, $this->validActions)) {
      $this->action = $action;
      return TRUE;
    }
    // @todo: throw error instead.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    if (empty($this->action)){
      // @todo: throw error instead.
      return FALSE;
    }
    return $this->machineName . '_' . $this->action;
  }

  /**
   * {@inheritdoc}
   */
  public function getParameters() {
    $actionsRequiringParameters = [
      CkanClient::CKAN_ACTION_CREATE,
      CkanClient::CKAN_ACTION_UPDATE,
      CkanClient::CKAN_ACTION_PATCH,
    ];

    if (in_array($this->action, $actionsRequiringParameters)) {
      if ($this->prepareParameters()) {
        return $this->parameters;
      }
      else {
        // @todo: throw error instead.
        return FALSE;
      }
    }

    // Delete and get (list/show) actions only require the ID.
    return ['id' => $this->id];
  }

  /**
   * {@inheritdoc}
   */
  public function getValidActions() {
    return $this->validActions;
  }

  /**
   * @param $action
   *
   * @return bool
   */
  protected function prepareParameters() {
    $keys = array_keys($this->parameters);

    // Check for missing required keys (create only).
    if ($this->action === CkanClient::CKAN_ACTION_CREATE) {
      if (!empty(array_diff_key($this->requiredParameters, $keys))) {
        return FALSE;
      }
    }

    // Update and patch queries must include an ID.
    if ($this->action === CkanClient::CKAN_ACTION_UPDATE || $this->action === CkanClient::CKAN_ACTION_PATCH) {

      // This check is the entire reason id is a separate property.
      if (empty($this->id)) {
        return FALSE;
      }

      $this->parameters['id'] = $this->id;
    }
    return TRUE;
  }

}
