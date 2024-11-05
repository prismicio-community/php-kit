# Contributing

This package is primarily maintained by the community. External contributions are always welcome. If you need assistance, feel free to [open an issue](https://github.com/prismicio-community/php-kit/issues/new/choose) or request a review by opening a pull request.

## Setup

To get started working on this project, you’ll need the following set up on your local machine:

-   PHP 8.0+
-   Composer 2.0+
-   The [APCu](https://www.php.net/manual/en/ref.apcu.php) extension for PHP

Clone this GitHub repository, then run the following command to install all dependencies:

```bash
$ composer install
```

## Project-specific Notes

Some of the SDK’s tests require APCu (Alternative PHP Cache) to function correctly from the command line. If you have APC installed and enabled, but cache-related tests still fail, please try the following:

-   **Verify APC is enabled for CLI:** Run `php -i | grep apc` to check if APC is active for the command line. If no output appears, APC may be enabled only for the web server (e.g., Apache) and not for CLI. Consult your OS documentation to enable APC for CLI. If it involves modifying a `php.ini` file, ensure you're editing the correct one, as there may be separate `php.ini` files for the web server and CLI.

-   **Enable the `apc.enable_cli` setting:** If APC is enabled for CLI but tests still fail, verify that `apc.enable_cli` is set to ‘On’ in the output of `php -i | grep apc`. If it isn’t, add `apc.enable_cli = 1` at the end of the appropriate `php.ini` file. Again, confirm you are editing the correct `php.ini` file for CLI, not the one used by Apache.

## Develop

Once your environment is set up, you can start developing new features or fixing bugs. Please ensure that you follow the coding standards and conventions in the existing codebase.

Before starting on a large change, it’s recommended that you open an issue to discuss your proposed changes. This allows you to receive early feedback and helps determine the best way to proceed.

If you want to test your changes in an example you can either create a new file in the `samples` directory or modify an existing one (locally). In case you want to test the changes in a real project, you can link your local package to the project using [Composer](https://getcomposer.org/doc/05-repositories.md#path).

In any case please make sure to write test cases for your changes.

**Useful commands:**

| Command             | Description                                                                      |
| ------------------- | -------------------------------------------------------------------------------- |
| `composer test`     | This command will run the test suite with PHPUnit (requires the APCu extension). |
| `composer cs-check` | This command will check the code style with PHP CS Fixer.                        |

## Tests

Please ensure that you write tests for any new features or bug fixes you implement.

If you find existing code that could benefit from additional testing, we appreciate contributions to improve test coverage. In such cases, please create a dedicated branch and submit a separate pull request to document and review your testing improvements.

You can run the tests with the following command:

```bash
$ ./vendor/bin/phpunit
```

## Submit a pull request

When you are ready to submit your changes, push your branch to the repository and open a pull request. Please include a detailed description of your changes and any relevant information for reviewers.

Your pull request will be reviewed by a maintainer. If changes are requested, please make them on your branch and push the updates to the repository. The pull request will be updated automatically.

## Publish

Publishing a new version of the package is restricted to maintainers.
