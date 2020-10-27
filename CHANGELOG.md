# Change Log

All notable changes to this project will be documented in this file.
See [Conventional Commits](https://conventionalcommits.org) for commit guidelines.

# Dev (xxxx-xx-xx)

### Upgrade

```bash
$ drush updb -y
$ drush fr -y vactory_page
$ drush pmu -y search
$ drush en -y vactory_widgets_ui
```

### Bug Fixes

* **Dynamic Field:** Render dynamic_views form element ([69210f7](https://github.com/voidagency/vactory_decoupled/commit/eb0d34b324530f161df9886f89e83a6fcf4f3382))
* **Core:** node_settings field should not be required. ([02d7c25](https://github.com/voidagency/vactory_decoupled/commit/02d7c259b450843d74b1382240a09dad57343bdd))

### New Features

* **Dynamic Field:** Introduced vactory_widgets_ui, a sub-module which contain a helpful list of new widgets ([0f48c33](https://github.com/voidagency/vactory_decoupled/commit/0f48c33f24cb40b9a71c1299ea57cefe8545fcf7))

### Other Changes

* **Vactory Page:** Display paragraphs ([eb0d34b](https://github.com/voidagency/vactory_decoupled/commit/69210f7d2bcc69f6a8f236988e5bac58f1f0c772))

### BREAKING CHANGES

* Removed search core module which was never used ([cfdbf96](https://github.com/voidagency/vactory_decoupled/commit/cfdbf96ab41078e5ea5aca4638e38a6bf11fed7d))

# 1.0.2 (2020-10-24)

### Upgrade

```bash
$ drush en media_library_form_element -y
$ drush updb -y
```

### Bug Fixes

* **Dynamic Field:** Fix few warnings ([48b4536](https://github.com/voidagency/vactory_decoupled/commit/48b45364c41075e801da65c30f1d9b39bc9820e3))

### New Features

* **Node Settings:** Ability to expose extra data from Drupal (API) using a special JSON field in nodes page, use case like `{"isHomepage": true}` ([b931b6b](https://github.com/voidagency/vactory_decoupled/commit/b931b6b84015d2bb9d2c7a1b1d9fc25d3c37673f))

### Other Changes

* **Dynamic Field:** field type "file" now uses [media_library_form_element](https://www.drupal.org/project/media_library_form_element) ([5cbb3dc](https://github.com/voidagency/vactory_decoupled/commit/5cbb3dc5c19982a31ef63b93d90824b349898e71))
