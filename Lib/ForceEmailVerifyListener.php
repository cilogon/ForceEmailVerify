<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeEvent', 'Event');

class ForceEmailVerifyListener implements CakeEventListener {

 public function implementedEvents() {
   return array(
     'Model.afterSave' => 'redirectVerifyEmailAddress'
   );
 }

 public function redirectVerifyEmailAddress(CakeEvent $event) {
   // The subject of the event is a Model object.
   $model = $event->subject();

   // We only intercept the EmailAddress model.
   if(!($model->name === 'EmailAddress')) {
     return true;
   }

   $emailAddress = $model->data;

   // We only intercept EmailAddresses attached to CoPerson records.
   if(empty($emailAddress['EmailAddress']['co_person_id'])) {
     return true;
   }

   $coPersonId = $emailAddress['EmailAddress']['co_person_id'];

   // We only intercept edits by checking revision and deleted fields.
   if(!empty($emailAddress['EmailAddress']['deleted'])) {
     return true;
   }

   if($emailAddress['EmailAddress']['revision'] == 0) {
     return true;
   }

   // Bail if there is no actor_identifier field.
   if(empty($emailAddress['EmailAddress']['actor_identifier'])) {
     return true;
   }

   $actorIdentifier = $emailAddress['EmailAddress']['actor_identifier'];

   // Pull the full CoPerson record and associated OrgIdentities and their
   $args = array();
   $args['conditions']['CoPerson.id'] = $coPersonId;
   $args['contain'] = array();
   $args['contain']['CoOrgIdentityLink']['OrgIdentity'] = 'Identifier';

   $coPerson = $model->CoPerson->find('first', $args);

   // We should only fire if the actor is the CoPerson.
   $verify = false;
   foreach ($coPerson['CoOrgIdentityLink'] as $link) {
     foreach ($link['OrgIdentity']['Identifier'] as $i) {
       if(($i['identifier'] == $actorIdentifier) && $i['login']) {
         $verify = true;
         $orgIdentityId = $link['OrgIdentity']['id'];
         break;
       }
     }
   }

   if($verify) {
     $email = $emailAddress['EmailAddress']['mail'];
     $model->CoInvite->send($coPersonId, $orgIdentityId, $coPersonId, $email, null, 'Users',   _txt('em.invite.subject.ver'), _txt('em.invite.body.ver'), $emailAddress['EmailAddress']['id']);
   }

   // Always return true so as not to interfere with the save action.
   return true;
 }
}
