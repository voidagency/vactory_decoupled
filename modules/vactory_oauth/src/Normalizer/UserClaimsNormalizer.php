<?php

namespace Drupal\vactory_oauth\Normalizer;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\serialization\Normalizer\NormalizerBase;
use Drupal\vactory_oauth\Entities\UserEntityWithClaims;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes a user entity to extract the claims.
 */
final class UserClaimsNormalizer extends NormalizerBase implements NormalizerInterface
{

  protected $supportedInterfaceOrClass = UserEntityWithClaims::class;

  protected $format = 'json';

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  private $userStorage;

  /**
   * The claims.
   *
   * @var string[]
   */
  private $claims;

  /**
   * UserClaimsNormalizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param string[] $claims
   *   The list of claims being selected.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, array $claims)
  {
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->claims = $claims;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($user_entity, $format = NULL, array $context = [])
  {
    assert($user_entity instanceof UserEntityWithClaims);
    $identifier = $user_entity->getIdentifier();
    // Check if the account is in $context. If not, load it from the database.
    $account = $context[$identifier] instanceof AccountInterface
      ? $context[$identifier]
      : $this->userStorage->load($identifier);
    assert($account instanceof AccountInterface);
    return $this->getClaimsFromAccount($account);
  }

  /**
   * Gets the claims for a given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return array
   *   The claims key/values.
   */
  private function getClaimsFromAccount(AccountInterface $account)
  {
    $full_name = $account->getDisplayName();
    $first_name = $account->get('field_first_name')->getString();
    $last_name = $account->get('field_last_name')->getString();
    if (!empty($first_name) && !empty($last_name)) {
      $full_name = $first_name . ' ' . $last_name;
    }

    $avatar = NULL;

    if (user_picture_enabled() && !$account->get('user_picture')->isEmpty()) {
      $pictureUri = $account->get('user_picture')->entity->getFileUri();
      $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('avatar');
      $avatar = $style->buildUrl($pictureUri);
    }

    $claim_values = [
      'name' => $account->getDisplayName(),
      'preferred_username' => $account->getAccountName(),
      'first_name' => $first_name,
      'last_name' => $last_name,
      'name_initial' => $this->generate($full_name),
      'full_name' => $full_name,
      'avatar' => $avatar,
      'email' => $account->getEmail(),
      'email_verified' => TRUE,
      'locale' => $account->getPreferredLangcode(),
    ];
    $keys = \Drupal::getContainer()
      ->getParameter('vactory_oauth.claims');
    if ($account instanceof EntityChangedInterface) {
      $claim_values['updated_at'] = $account->getChangedTime();
    }
    return array_intersect_key($claim_values, array_flip($this->claims));
  }

  /**
   * Generate initials from a name
   *
   * @param string $name
   * @return string
   */
  public function generate($name)
  {
    $words = explode(' ', $name);
    if (count($words) >= 2) {
      return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
    }
    return $this->makeInitialsFromSingleWord($name);
  }

  /**
   * Make initials from a word with no spaces
   *
   * @param string $name
   * @return string
   */
  protected function makeInitialsFromSingleWord($name)
  {
    preg_match_all('#([A-Z]+)#', $name, $capitals);
    if (count($capitals[1]) >= 2) {
      return substr(implode('', $capitals[1]), 0, 2);
    }
    return strtoupper(substr($name, 0, 2));
  }

}
