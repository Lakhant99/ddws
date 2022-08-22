<?php

namespace Drupal\anonymoussession\Services;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManager;

class AnonymousSessionService {

  /**
   * @var \Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  public function __construct(SessionManager $sessionManager, AccountProxyInterface $currentUser) {
    $session = \Drupal::request()->getSession();
    // print_r($session);
    // die();
    $this->sessionManager = $sessionManager;
    $this->currentUser = $currentUser;
  }

  public function apply() {
    // Initialize a consistent service for anonymous users.
    if ($this->currentUser->isAnonymous() && !isset($_SESSION['AnonymousSessionService'])) {
      $_SESSION['AnonymousSessionService'] = TRUE;
      $this->sessionManager->start();
    }
  }

}
