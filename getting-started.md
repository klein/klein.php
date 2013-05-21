---
layout: klein
title: Getting Started
github_name: klein.php
---

1. PHP 5.3.x is required
2. Install Klein using [Composer](#composer-installation) (recommended) or manually
3. Setup [URL rewriting](https://gist.github.com/874000) so that all requests are handled by **index.php**
4. (Optional) Throw in some [APC](http://pecl.php.net/package/APC) for good measure

## Composer Installation

1. Get [Composer](http://getcomposer.org/)
2. Require Klein with `php composer.phar require klein/klein v2.0.x`
3. Install dependencies with `php composer.phar install`
