Anonymous session toolkit
---

Add a consistent way to the website to
ensure a session for anonymous users for
reliable session related functionality.

Include the service in your application where the
session is important, and call the 'apply' method before
using the session

```
  $anonymousSession = \Drupal::service('anonymoussession');
  $anonymousSession->apply();

  // Use session variables:
  //$_SESSION[...]

```

Use cases include:
* PrivateTempStore
* Functionality that depends on Drupal::currentRequest()->getSession()
