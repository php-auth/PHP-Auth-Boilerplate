<br><br><br>

<p align="center"><img src="https://raw.githubusercontent.com/php-auth/PHP-Auth-Boilerplate/refs/heads/main/docs/php-auth.png" style="width:200px;"></p>

<p align="center">
Authentication for PHP. Simple, lightweight and secure.
</p>

<br><br>

# 

<p align="center"><img src="https://raw.githubusercontent.com/php-auth/PHP-Auth-Boilerplate/refs/heads/main/docs/login.png"></p>
<p align="center"><img src="https://raw.githubusercontent.com/php-auth/PHP-Auth-Boilerplate/refs/heads/main/docs/create_new_account.png"></p>
<p align="center"><img src="https://raw.githubusercontent.com/php-auth/PHP-Auth-Boilerplate/refs/heads/main/docs/reset_password.png"></p>
<p align="center"><img src="https://raw.githubusercontent.com/php-auth/PHP-Auth-Boilerplate/refs/heads/main/docs/manage_users.png"></p>

### Requirements:

- PHP
- Node.js

<hr>

### 1. [Download](https://github.com/php-auth/PHP-Auth-Boilerplate/archive/refs/heads/main.zip)

### 2. Install (Via Terminal):

```debian@debian:~$ **cd PHP-Auth-Boilerplate**```

debian@debian:~/PHP-Auth-Boilerplate$ **php install.php**

### 3. Database configuration:

/PHP-Auth-Boilerplate/config/**connection.ini**

### 4. Run:

debian@debian:~/PHP-Auth-Boilerplate$ **php server.php**

http://localhost:3000/

### Login

Email: **admin@email.local**

Password: **12345678**

<p align="center"><img src="https://raw.githubusercontent.com/php-auth/PHP-Auth-Boilerplate/refs/heads/main/docs/home.png"></p>

<div class="my-5">
  <hr>
  <h3 class="fs-3">Routes</h3>
  <a class="btn btn-primary border-0 shadow fs-5" href="javascript:history.back()">
    <i class="fa fa-arrow-left" aria-hidden="true"></i>
  </a>
  <a class="btn btn-primary border-0 shadow fs-5" href="/docs/php-framework">
    <i class="fa fa-home" aria-hidden="true"></i>
  </a>
</div>

<p class="text-start">Route is the path (URL) and are responsible for calling the controllers.</p>

<a href="https://github.com/steampixel/simplePHPRouter/tree/master">
  simplePHPRouter - https://github.com/steampixel/simplePHPRouter/tree/master
</a>

```php

<?php

use Core\Route;

Route::add('/', function () {
    controller('MyController')->myMethod();
}); // http://localhost

Route::add('/my-route', function () {
    controller('MyController')->myMethod();
}); // http://localhost/my-route

Route::add('/my/route', function () {
    controller('MyController')->myMethod();
}); // http://localhost/my/route

Route::add('/' . translate('my-route'), function () {
    controller('MyController')->myMethod();
}); // http://localhost/my-route - http://localhost/minha-rota

Route::add(translate('/my/route/(.*)'), function ($arg) {
    controller('MyController')->myMethod($arg);
}); // http://localhost/my/route/abc - http://localhost/minha/rota/abc

Route::add('/this-route-is-defined', function () {
    controller('MyController')->myMethod('You need to patch this route to see this content');
}, 'patch');

Route::add('/my-route', function () {
    controller('MyController')->myMethod($_GET);
}, 'get'); // http://localhost/my-route?param=123

Route::add('/my-route', function () {
    controller('MyController')->myMethod($_POST);
}, 'post'); // <form action="http://localhost/my-route" method="POST"></form>

Route::add('/my-route', function () {
    controller('MyController')->myMethod($_REQUEST); // $_GET and $_POST
});

Route::add('/my-route/(.*)', function ($arg) { // (.*) Required argument
    controller('MyController')->myMethod($arg);
}); // http://localhost/my-route/test

Route::add('/my-route/?(.*)', function ($arg) { // ?(.*) Optional argument
    controller('MyController')->myMethod($arg);
}); // http://localhost/my-route/test | http://localhost/my-route

Route::add('/my/([0-9]*)/route', function ($arg) {
    controller('MyController')->myMethod($arg);
}); // http://localhost/my/1/route

Route::add('/my/(.*)/route', function ($arg) {
    controller('MyController')->myMethod($arg);
}); // http://localhost/my/string/route | http://localhost/my/integer/route

Route::add('/my-route/(.*)/(.*)/(.*)/(.*)', function ($n1, $n2, $n3, $n4) {
    controller('MyController')->myMethod($n1, $n2, $n3, $n4);
}); // http://localhost/my-route/a/b/c/1

```

<h3 class="fs-3">Authenticated Route</h3>

```php

<?php

use Core\Route;
use Core\Auth;

Route::add('/my-route', function () {
    // ADMIN - Allow only the ADMIN group to access this route
    Auth::group('ADMIN', function ($data) {
        controller('MyController')->myMethod($data);
    });
});

Route::add('/my-route', function () {
    // Groups that can access this route
    Auth::group('AUTHOR, COLLABORATOR, CREATOR, EDITOR', function ($data) {
        controller('MyController')->myMethod($data);
    });
});

Route::add('/my-route', function () {
    // ALL - Allow any group to access this route
    Auth::group('ALL', function ($data) {
        controller('MyController')->myMethod($data);
    });
});

Route::add('/my-route/(.*)', function ($arg) {
    // ALL - Allow any group to access this route
    Auth::group('ALL', function ($data) use ($arg) {
        controller('MyController')->myMethod($data, $arg);
    });
});

```

<h3>Roles: <a href="https://github.com/delight-im/PHP-Auth/blob/master/README.md#roles-or-groups">PHP-Auth</a></h3>

```php

<?php

\Delight\Auth\Role::ADMIN;
\Delight\Auth\Role::AUTHOR;
\Delight\Auth\Role::COLLABORATOR;
\Delight\Auth\Role::CONSULTANT;
\Delight\Auth\Role::CONSUMER;
\Delight\Auth\Role::CONTRIBUTOR;
\Delight\Auth\Role::COORDINATOR;
\Delight\Auth\Role::CREATOR;
\Delight\Auth\Role::DEVELOPER;
\Delight\Auth\Role::DIRECTOR;
\Delight\Auth\Role::EDITOR;
\Delight\Auth\Role::EMPLOYEE;
\Delight\Auth\Role::MAINTAINER;
\Delight\Auth\Role::MANAGER;
\Delight\Auth\Role::MODERATOR;
\Delight\Auth\Role::PUBLISHER;
\Delight\Auth\Role::REVIEWER;
\Delight\Auth\Role::SUBSCRIBER;
\Delight\Auth\Role::SUPER_ADMIN;
\Delight\Auth\Role::SUPER_EDITOR;
\Delight\Auth\Role::SUPER_MODERATOR;
\Delight\Auth\Role::TRANSLATOR;

/*
  Database field: roles_mask
  Default value: 0
  List of values:
*/

Array
(
  [1]       => ADMIN
  [2]       => AUTHOR
  [4]       => COLLABORATOR
  [8]       => CONSULTANT
  [16]      => CONSUMER
  [32]      => CONTRIBUTOR
  [64]      => COORDINATOR
  [128]     => CREATOR
  [256]     => DEVELOPER
  [512]     => DIRECTOR
  [1024]    => EDITOR
  [2048]    => EMPLOYEE
  [4096]    => MAINTAINER
  [8192]    => MANAGER
  [16384]   => MODERATOR
  [32768]   => PUBLISHER
  [65536]   => REVIEWER
  [131072]  => SUBSCRIBER
  [262144]  => SUPER_ADMIN
  [524288]  => SUPER_EDITOR
  [1048576] => SUPER_MODERATOR
  [2097152] => TRANSLATOR
)

```
<div class="alert alert-primary text-wrap text-start mt-5 p-4" role="alert">
  <h5><i class="fa-solid fa-circle-info me-2"></i><strong>Note</strong></h5>
  <p class="m-0 p-0">- To view formatted data use the function: <strong>print_f();</strong> instead of <strong>print_r();</strong></p>
</div>

## License

This project is licensed under the terms of the <a href="https://opensource.org/license/MIT">MIT License</a>.
