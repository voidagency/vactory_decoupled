<?php

namespace Drupal\vactory_keycloak\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\social_auth\Event\UserFieldsEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Site\Settings;

/**
 * Reacts on Social Auth events.
 */
class SocialAuthSubscriber implements EventSubscriberInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   *
   * Returns an array of event names this subscriber wants to listen to.
   * For this case, we are going to subscribe for user creation and login
   * events and call the methods to react on these events.
   */
  public static function getSubscribedEvents() {
    $events[SocialAuthEvents::USER_FIELDS] = ['onUserFields'];

    return $events;
  }
  
  /**
   * Alters the user name.
   *
   * @param \Drupal\social_auth\Event\SocialAuthUserEvent $event
   *   The Social Auth user event object.
   */
  public function onUserFields(UserFieldsEvent $event) {
    if ($event->getPluginId() !== "vactory_keycloak") {
        return;
    }

    $userFields = $event->getUserFields();

    $endpoint = Settings::get('SELFCARE_API_URL') . '/v1/profiles/me';
    $client = \Drupal::httpClient();
    $proxy = Settings::get('PROXY_URL') ? ['https' => Settings::get('PROXY_URL')] : '';
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $event->getSocialAuthUser()->getToken(),
      ],
      'verify' => false,
      'proxy' => $proxy,
    ];

    $response = $client->request("GET", $endpoint, $options);
    $data = json_decode($response->getBody());
    // $user_data = $user->toArray();
    $userFields['field_last_name'] = $data->customer_name;

    $event->setUserFields($userFields);
    // Adds a prefix with the implementer id on username.
    // $user->setUsername($event->getPluginId() . ' ' . $user->getDisplayName())->save();
  }
  
}
