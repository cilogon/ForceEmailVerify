<?php

  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return(false);

  // We can emit all attributes because the ones we don't want should be null
  print $this->Form->hidden('co_department_id', array('default' => $vv_pid['codeptid'])) . "\n";
  print $this->Form->hidden('organization_id', array('default' => $vv_pid['organizationid'])) . "\n";
  print $this->Form->hidden('org_identity_id', array('default' => $vv_pid['orgidentityid'])) . "\n";
  print $this->Form->hidden('co_person_id', array('default' => $vv_pid['copersonid'])) . "\n";
  
  if(isset($cur_co))
    print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
  
  // Add breadcrumbs. This logic is copied from the coCrumb element,
  // which we cannot use because it does not always set the plugin
  // to be null.
  if(!empty($vv_pid['copersonid'])) {
    $args = array(
      'plugin' => null,
      'controller' => 'co_people',
      'action' => 'index',
      'co' => $cur_co['Co']['id']
    );
    $this->Html->addCrumb(_txt('me.population'), $args);

    $args = array(
      'plugin' => null,
      'controller' => 'co_people',
      'action' => 'canvas',
      $vv_pid['copersonid']
    );
    $this->Html->addCrumb($vv_bc_name, $args);
  } else {
    $args = array(
      'plugin' => null,
      'controller' => 'org_identities',
      'action' => 'index'
    );
    if(!$pool_org_identities) {
        $args['co'] = $cur_co['Co']['id'];
    }
    $this->Html->addCrumb(_txt('ct.org_identities.pl'), $args);

    $args = array(
      'plugin' => null,
      'controller' => 'orgIdentities',
      'action' => 'edit',
    $vv_pid['orgidentityid']
    );
    $this->Html->addCrumb($vv_bc_name, $args);
  }
?>
<ul id="<?php print $this->action; ?>_email_address" class="fields form-list">
  <li>
    <span class="field-name">
      <?php
        if($e) {
          print $this->Form->label('mail', _txt('fd.email_address.mail'));
        } else {
          print _txt('fd.email_address.mail');
        }
      ?>
    </span>
    <span class="field-info">
      <?php print ($e ? $this->Form->input('mail', array('class' => 'focusFirst')) : filter_var($force_email_addresses[0]['ForceEmailAddress']['mail'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </span>
  </li>
  <li>
    <div class="field-name">
      <?php print $this->Form->label('description',_txt('fd.desc')); ?>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('description') : filter_var($force_email_addresses[0]['ForceEmailAddress']['description'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <span class="field-name">
      <?php
        if($e) {
          print $this->Form->label('type', _txt('fd.type'));
        } else {
          print _txt('fd.type');
        }
      ?>
    </span>
    <span class="field-info">
      <?php
        $attrs['value'] = (isset($force_email_addresses) ? $force_email_addresses[0]['ForceEmailAddress']['type'] : EmailAddressEnum::Official);
        $attrs['empty'] = false;

        if($e) {
          print $this->Form->select('type',
                                    $vv_available_types,
                                    $attrs);

          if($this->Form->isFieldError('type')) {
            print $this->Form->error('type');
          }
        } else {
          print _txt('en.email_address.type', null, filter_var($force_email_addresses[0]['ForceEmailAddress']['type'],FILTER_SANITIZE_SPECIAL_CHARS));
        }
      ?>
    </span>
  </li>
  <li>
    <span class="field-name">
      <?php print _txt('fd.email_address.verified'); ?>
    </span>
    <span class="field-info">
      <?php
        print $this->Form->input('verified');

      ?>
    </span>
  </li>
  <?php if(!empty($force_email_addresses[0]['SourceEmailAddress']['id'])): ?>
    <li>
      <span class="field-name">
        <?php print _txt('fd.source'); ?><br />
      </span>
      <span class="field-info">
        <?php print $this->Html->link(_txt('ct.email_addresses.1'),
                                      array('controller' => 'email_addresses',
                                            'action'     => 'view',
                                            $force_email_addresses[0]['SourceEmailAddress']['id']));
        ?>,
        <?php print $this->Html->link(_txt('ct.org_identities.1'),
                                      array('controller' => 'org_identities',
                                            'action'     => 'view',
                                            $force_email_addresses[0]['SourceEmailAddress']['org_identity_id']));
        ?>
      </span>
    </li>
  <?php endif; // source id ?>

  <?php if($e): ?>
    <li class="fields-submit">
      <span class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </span>
      <span class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </span>
    </li>
  <?php endif; ?>
</ul>
<?php
  print $this->element("changelog");
