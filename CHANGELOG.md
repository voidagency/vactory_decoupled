# Change Log

All notable changes to this project will be documented in this file.
See [Conventional Commits](https://conventionalcommits.org) for commit guidelines.

# 1.0.15 (2021-02-28)

### New Features

* **Mailchimp:** Mailchimp support ([PR-70](https://github.com/voidagency/vactory_decoupled/pull/70))

# 1.0.14 (2021-02-28)

### Bug Fixes

* **Oauth:** Redirect no matter the role of the user ([e9c6a70](https://github.com/voidagency/vactory_decoupled/commit/e9c6a70154db35f70d5e39a8f5642ac6732a3892))



# 1.0.13 (2021-02-27)

### Bug Fixes

* **DF:** Views dynamic field value override ([7ac0e9e](https://github.com/voidagency/vactory_decoupled/commit/7ac0e9e57ea67aaa90ba4ed809c2eb8191e3ce54))


# 1.0.12 (2021-02-26)

### Bug Fixes

* **Forum:** Missing date field ([ca3182](https://github.com/voidagency/vactory_decoupled/commit/ca31825c4b322bf9866ec3d0a1d9a08402413291))

* **Forum:** Format values ([da8ad9](https://github.com/voidagency/vactory_decoupled/commit/da8ad9c14345ff739465035083d1fa7ce05a917d))


### Other Changes

* **Core:** Internal user fullname. ([55e9f8](https://github.com/voidagency/vactory_decoupled/commit/55e9f81429bd0e43859e38ebb6d793c07db2a0a4))

* **Core:** Node comment internal field. ([aeca86](https://github.com/voidagency/vactory_decoupled/commit/aeca86d0013e640be02621979f813e4f43e67d6f))

# 1.0.11 (2021-02-24)

### Bug Fixes

* **Webform:** Confirmation URL ([db47ff](https://github.com/voidagency/vactory_decoupled/commit/db47ff14614f50a78c85a9a05e4158e929ff8645))
* **Webform:** Added date time fields ([PR-69](https://github.com/voidagency/vactory_decoupled/pull/69))

### New Features

* **FAQ:** New feature module ([PR-68](https://github.com/voidagency/vactory_decoupled/pull/68))

# 1.0.10 (2021-02-22)

### Bug Fixes

* **Auth:** Fix routing ([PR-66](https://github.com/voidagency/vactory_decoupled/pull/66))
* **Forum:** Fix fields order, mapping & added roles ([PR-67](https://github.com/voidagency/vactory_decoupled/pull/67))

# 1.0.9 (2021-02-18)

### Bug Fixes

* **Forum:** Fix routing ([516f68](https://github.com/voidagency/vactory_decoupled/commit/516f68d758d56fe5dac13c1d4321ed4834bf8da6))

# 1.0.8 (2021-02-18)

### Bug Fixes

* **Core:** Make node revision log message optional ([PR-63](https://github.com/voidagency/vactory_decoupled/pull/63))

### New Features

* **Forum:** New feature module ([PR-59](https://github.com/voidagency/vactory_decoupled/pull/59))
* **Sendinblue:** Exposed an endpoint API for adding new subscribers to sendinblue ([PR-64](https://github.com/voidagency/vactory_decoupled/pull/64))
* **Core:** URL Resolver service which allow converting URI's to URL's ([330e3b](https://github.com/voidagency/vactory_decoupled/commit/330e3b7cd0792f86a79d4cc9d16868520e0b4791))

### Other Changes

* **Core:** Upgrade contrib modules to latest available ([PR-65](https://github.com/voidagency/vactory_decoupled/pull/65))


# 1.0.7 (2021-01-29)

### Bug Fixes

* **DF:** Images meta data ([1cc833c](https://github.com/voidagency/vactory_decoupled/commit/1cc833cce1c4f3915b7e1bd6fc939a18f667ce65))

### New Features

* **Core:** Blocks per region ([PR-60](https://github.com/voidagency/vactory_decoupled/pull/60))

# 1.0.6 (2020-11-20)

### Upgrade
```bash
$ drush updb
$ drush fr -y vactory_core
```

### Bug Fixes

* **Webform:** Format webform submission URL ([fba1198](https://github.com/voidagency/vactory_decoupled/commit/fba119825ae5194c6df25083f770490337a590f5))
* **DF:** Request-URI too long ([582307e](https://github.com/voidagency/vactory_decoupled/commit/582307e0b61408de27304e77c85b131a1f728de2))

### Breaking

* **Core:** Paragraphs Template Multiple Introduction field using text_long instead of string_long ([6a7960b](https://github.com/voidagency/vactory_decoupled/commit/6a7960bd0fd525177a360eefdc20689d6b1b2e9e))

### New Features

* **JSON:API:** Image fields are now exposing their respective width and height ([b3264d5](https://github.com/voidagency/vactory_decoupled/commit/b3264d5e1849b519ffc0d91fac9edaacbc7eb5a0))

# 1.0.5 (2020-11-15)

### Bug Fixes

* **Dynamic Field:** Check for media file ([206a547](https://github.com/voidagency/vactory_decoupled/commit/206a5470cd17d4a883b914cb4068002c98100ed1))
* **Core:** A feature revert on `vactory_core` should not override reCaptcha & SwiftMailer settings ([128d17c](https://github.com/voidagency/vactory_decoupled/commit/128d17c1772b03c734c083ee78e0a92d5aa90090))

### New Features

* **DF:** Dump data in `vactory_widgets_ui` templates for search index ([0c8675b](https://github.com/voidagency/vactory_decoupled/commit/0c8675b261bcb8793e8e6ae7e63c3f9de2b9d3e9))

# 1.0.4 (2020-11-14)

### Upgrade
```bash
$ drush en webform_ui rest restui
$ drush cr
$ drush vactory_webform
```
### Bug Fixes

* **Events:** Missing template.html.twig ([034a55d](https://github.com/voidagency/vactory_decoupled/commit/034a55dc736e67998e5024f1cf7bfc88a7280ce9))
* **Core:** All content modules dump data into their corresponding template.html.twig > allowing us to index their content in search API  ([b7f8a4c](https://github.com/voidagency/vactory_decoupled/commit/b7f8a4c92e93eeedb458efe1d3533fdd02b3c2c9))
* **Dynamic Field:** g_title key should not be considered as a field ([1490635](https://github.com/voidagency/vactory_decoupled/commit/1490635f101f47e6d30c33e35d93549750c3d371))
* **Dynamic Field:** Removed use of isXmlHttpRequest as it blocked Search API. ([5376f46](https://github.com/voidagency/vactory_decoupled/commit/5376f4629ca6eead0df00be97d10a01b02047802))

### New Features

* **Core:** New module ([Vactory Webform](https://github.com/voidagency/vactory_decoupled/tree/master/modules/vactory_webform))
* **Core:** Add string context and location filters to the translate interface ([42e3cb0](https://github.com/voidagency/vactory_decoupled/commit/42e3cb09dca73bd9c1a08e018d48b8a532360849))
* **Core:** Added Jenkins Build Log to /admin/deployment & Improved messages ([5ec309d](https://github.com/voidagency/vactory_decoupled/commit/5ec309deb72ad6d95b67d31973f6ada95d6a5c07))

# 1.0.3 (2020-10-27)

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
