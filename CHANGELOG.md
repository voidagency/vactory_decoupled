# Change Log

All notable changes to this project will be documented in this file.
See [Conventional Commits](https://conventionalcommits.org) for commit guidelines.

# 1.0.2 (2020-10-24)

### Bug Fixes

* **Dynamic Field:** Fix few warnings ([48b4536](https://github.com/voidagency/vactory_decoupled/commit/48b45364c41075e801da65c30f1d9b39bc9820e3))

### New Features

```bash
$ drush updb -y
```

* **Node Settings:** Ability to expose extra data from Drupal (API) using a special JSON field in nodes page, use case like `{"isHomepage": true}` ([b931b6b](https://github.com/voidagency/vactory_decoupled/commit/b931b6b84015d2bb9d2c7a1b1d9fc25d3c37673f))

### Other Changes

```bash
$ drush en media_library_form_element -y
```

* **Dynamic Field:** field type "file" now uses [media_library_form_element](https://www.drupal.org/project/media_library_form_element) ([5cbb3dc](https://github.com/voidagency/vactory_decoupled/commit/5cbb3dc5c19982a31ef63b93d90824b349898e71))
