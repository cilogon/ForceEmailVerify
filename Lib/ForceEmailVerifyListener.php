<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeEvent', 'Event');

class ForceEmailVerifyListener implements CakeEventListener {

 public function implementedEvents() {
   return array(
     'Controller.beforeRedirect' => 'redirectVerifyEmailAddress'
   );
 }

 public function redirectVerifyEmailAddress(CakeEvent $event) {
   // The subject of the event is a Controller object.
   $controller = $event->subject();

   // We only intend to intercept the EmailAddressesController.
   if(!($controller->name === 'EmailAddresses')) {
     return true;
   }

   // We only intend to intercept the edit action.
   if(!($controller->action === "edit")) {
     return true;
   }

   // We only intend to intercept the PUT method.
   $method = $controller->request->method();
   if(!($method === 'PUT')) {
     return true;
   }

   // We do not intercept any REST calls.
   $restful = $controller->request->is('restful');
   if($restful) {
     return true;
   }

   $emailAddress = $controller->data;

   // We only intercept EmailAddresses attached to CoPerson records.
   if(empty($emailAddress['EmailAddress']['co_person_id'])) {
     return true;
   }

   $coPersonId = $emailAddress['EmailAddress']['co_person_id'];

   // Pull the full CoPerson record and associated OrgIdentities and their
   // Identifiers.
   $args = array();
   $args['conditions']['CoPerson.id'] = $coPersonId;
   $args['contain'] = array();
   $args['contain']['CoOrgIdentityLink']['OrgIdentity'] = 'Identifier';

   $coPerson = $controller->EmailAddress->CoPerson->find('first', $args);

   $actorIdentifier = $controller->Session->read('Auth.User.username');

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
     // We redirect to the CoInvites controller with the verifyEmailAddress
     // action and pass the EmailAddress ID to verify.
     $url = array();
     $url['plugin'] = null;
     $url['controller'] = 'co_invites';
     $url['action'] = 'verifyEmailAddress';
     $url['email_address_id'] = $emailAddress['EmailAddress']['id'];

     // We need to set the response details here because we are going
     // to stop propagation of the CakeEvent and therefore cause the
     // redirect() method of the controller that was in process to
     // short circuit before setting the header and status.
     $controller->response->header('Location', Router::url($url, true));
     $controller->response->statusCode(302);
     $controller->response->send();

     // Stop the event propagation.
     $event->stopPropagation();
   }

   // Always return true so as not to interfere with call stack.
   return true;
 }
}
