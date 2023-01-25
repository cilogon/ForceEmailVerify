<?php

App::uses("MVPAController", "Controller");

class ForceEmailAddressesController extends MVPAController {
  // Class name, used by Cake
  public $name = "ForceEmailAddresses";
  
  // Establish pagination parameters for HTML views
  public $paginate = array(
    'limit' => 25,
    'order' => array(
      'mail' => 'asc'
    )
  );
  
  public $edit_contains = array(
    'CoDepartment',
    'CoPerson' => array('PrimaryName'),
    'Organization',
    'OrgIdentity' => array('PrimaryName')
  );

  public $view_contains = array(
    'CoDepartment',
    'CoPerson' => array('PrimaryName'),
    'Organization',
    'OrgIdentity' => array('OrgIdentitySourceRecord' => array('OrgIdentitySource'),
                           'PrimaryName'),
    'SourceEmailAddress'
  );
  
  /**
   * Callback to set relevant tab to open when redirecting to another page
   * - precondition:
   * - postcondition: Auth component is configured
   * - postcondition:
   *
   * @since  COmanage Registry v0.8
   */

  function beforeFilter() {
    $this->redirectTab = 'email';
    parent::beforeFilter();
  }

  function edit($id) {
    if($this->request->is('post')) {
      return;
    } else {
      parent::edit($id);
    }
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v0.1
   * @return Array Permissions
   */
  
  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Only platform or CO admins can use this plugin.
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin']);

    $this->set('permissions', $p);
    return $p[$this->action];
  }
}
