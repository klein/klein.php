# Contributing

Contributing is absolutely encouraged, but a few things should be taken into account:

- Always test any bug-fixes or changes with [unit testing][unit-testing]
- When adding or changing a feature, make sure to write a **new** [unit test][unit-testing]
- This project adheres to the [PSR-2][psr-2] standards. Please make sure your contributions [comply][code-sniffer].
- Code and comment styles should be made as consistent as possible with the rest of the project
- Make sure to document your code with the [PHPDoc syntax][php-doc]
- Pull Requests and Issues should contain no more than **1** bug-fix, feature, or documentation change
- Keep the number of lines changed in a pull request to a minimum necessary to complete the PR's subject
- Pull requests shouldn't contain commits from other pull requests. They should be separate, independent branches
- When creating pull requests
   - make sure to create useful/verbose PR messages
   - don't be afraid to squash your commits
   - rebase onto the parent's upstream branch before pushing your remote

Klein is an open library designed for a specific purpose. You may find that a certain requested feature or change may not be accepted. Please don't take those actions personally, as the controlling contributors are simply just trying to keep the project's purpose clear and designated.

 [unit-testing]: README.md#unit-testing
 [psr-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
 [code-sniffer]: https://github.com/squizlabs/PHP_CodeSniffer
 [php-doc]: http://www.phpdoc.org/docs/latest/for-users/phpdoc-reference.html
