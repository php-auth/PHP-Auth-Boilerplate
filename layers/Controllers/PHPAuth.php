<?php
/**
 * PHPAuth
 *
 * @version 1.0.0
 */
/*
| ------------------------------------------------------------------------------
| PHP-Auth - https://github.com/delight-im/PHP-Auth
| ------------------------------------------------------------------------------
*/
namespace Layer\Controller;

use Core\Language;
use Core\Route;
use Core\Template;

class PHPAuth
{
    private $instance = null;
    private $model    = null;
    private $Plugin   = null;
    private $route    = '';
    private $redirect = '';

    /**
     * init
     *
     * @access public
     */
    public function init()
    {
        $this->model    = model('PHPAuth');
        $this->instance = $this->instance();
        $auth           = $this->getRoute();
        $this->route    = $auth['route'];
        $this->redirect = $auth['redirect'];
        // http://localhost/account
        Route::add($this->route(), fn() =>
            $this->execute(isset($_GET['auth']) ? $_GET['auth'] : 'login'));
    }

    /**
     * render
     *
     * @param string $file
     * @param array $data
     * @return mixed
     * @access private
     */
    private function render($file, $data) : mixed
    {
        $data['route'] = $this->route();
        return Template::render("php-auth/$file.html", $data);
    }

    /**
     * execute
     *
     * @param string $action
     * @return mixed
     * @access private
     */
    private function execute($action) : mixed
    {
        // These actions can only be performed through authentication
        if ($this->instance->isLoggedIn()) {
            // Admin only
            if ($this->isAdmin()) {
                switch ($action) {
                  case 'manage-users':
                    return $this->manageUsers();
                    break;
                  case 'manage-users.get-data':
                    return $this->getData();
                    break;
                  case 'manage-users.update-status':
                    return $this->updateStatus();
                    break;
                  case 'settings':
                    return $this->settings();
                    break;
                }
            }
            switch ($action) {
              case 'change-name':
                return $this->changeName();
                break;
              case 'change-email':
                return $this->changeEmail();
                break;
              case 'change-password':
                return $this->changePassword();
                break;
              case 'logout':
                return $this->logOut();
                break;
            }
        }
        switch ($action) {
          case 'login':
            return $this->login();
            break;
          case 'create-account':
            return $this->createAccount();
            break;
          case 'forgot-password':
            return $this->forgotPassword();
            break;
          case 'reset-password':
            return $this->resetPassword();
            break;
          case 'confirm-email':
            return $this->confirmEmail();
            break;
          default:
            return header('Location: ' . $this->route());
        }
    }

    /**
     * instance
     *
     * @return object
     * @access private
     */
    private function instance() : object
    {
        return new \Delight\Auth\Auth($this->model->connection(), null, null,
                                      ini_get('display_errors') ? false : true);
    }

    /**
     * route
     *
     * @param string $action
     * @return string
     * @access private
     */
    private function route($action = '') : string
    {
        $route = '/';
        if ($this->route !== $route) {
            $route .= Language::translate(substr($this->route, 1));
        }
        return $route . $action;
    }

    /**
     * status
     *
     * @return string
     * @access private
     */
    private function status() : string
    {
        /*
        database field: status
        Default value: 0
        List of values:
        0 - User is in default state
        1 - User has been archived
        2 - User has been banned
        3 - User has been locked
        4 - User is pending review
        5 - User has been suspended
        */
        if ($this->instance->isArchived()) {
            return 'User has been archived';
        } elseif ($this->instance->isBanned()) {
            return 'User has been banned';
        } elseif ($this->instance->isLocked()) {
            return 'User has been locked';
        } elseif ($this->instance->isPendingReview()) {
            return 'User is pending review';
        } elseif ($this->instance->isSuspended()) {
            return 'User has been suspended';
        }
        return '';
    }

    /**
     * isAdmin
     *
     * @param int $id
     * @return bool
     * @access private
     */
    private function isAdmin($id = 0) : bool
    {
        try {
            if ($id === 0) {
                $id = $this->instance->getUserId();
            }
            if ($this->instance->admin()->
                doesUserHaveRole($id, \Delight\Auth\Role::ADMIN)) {
                return true;
            } else {
                return false;
            }
        } catch (\Delight\Auth\UnknownIdException $e) {
            return false;
        }
    }

    /**
     * group
     *
     * @param string $roles
     * @param callable $callback
     * @return mixed
     * @access public
     */
    public function group($roles, $callback) : mixed
    {
        if ($this->instance->isLoggedIn()) {
            $group = explode(',', strtoupper(str_replace(' ', '', $roles)));
            $roles = $this->instance->getRoles();
            array_push($roles, 'ALL');
            foreach($group as $role) {
                if (in_array($role, $roles) or in_array('ADMIN', $roles)) {
                    return $callback(['auth' => $this->authInfo()]);
                }
            }
            return Route::error(401);
        }
        return header('Location: ' . $this->route());
    }

    /**
     * login
     *
     * @return mixed
     * @access private
     */
    private function login() : mixed
    {
        $message = [];
        $data    = [];
        try {
            if ($_POST) {
                $data     = $_POST;
                $remember = null;
                if (isset($data['remember']) && $data['remember'] === 'on') {
                    // keep logged in for one year
                    $remember = (int) (60 * 60 * 24 * 365.25);
                }
                $this->instance->login($data['email'],
                                       $data['password'],
                                       $remember);
                if ($this->instance->isLoggedIn()) {
                    if ($this->status()) {
                        $message['error'] = $this->status();
                        $this->instance->logOut();
                    } else {
                        // Redirects to admin-defined page upon login.
                        $data['script'] = '<script>$_["http"].ajax("", "' .
                                          $this->redirect . '");</script>';
                    }
                }
            }
        } catch (\Delight\Auth\InvalidEmailException $e) {
            $message['error'] = 'Wrong email address';
            $message['input'] = 'email';
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            $message['error'] = 'Wrong password';
            $message['input'] = 'password';
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            $message['error'] = 'Email not verified';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            /*
             Redirects to the admin-defined page if the user refreshes the page.
             Ex. http://localhost:8000/account to http://localhost:8000/
            */
            if (!isset($data['script']) && $this->instance->isLoggedIn()) {
                return $this->render('redirect', ['url' => $this->redirect]);
            }
            $data['message'] = $message;
            return $this->render('login', $data);
        }
    }

    /**
     * authInfo
     *
     * @return array
     * @access private
     */
    private function authInfo() : array
    {
        return [
          'admin' => $this->isAdmin(),
          'login' => $this->instance->isLoggedIn(),
          'ip'    => $this->instance->getIpAddress(),
          'id'    => $this->instance->getUserId(),
          'user'  => $this->instance->getUsername(),
          'email' => $this->instance->getEmail()
        ];
    }

    /**
     * manageUsers
     *
     * @return mixed
     * @access private
     */
    private function manageUsers() : mixed
    {
        return $this->render('manage_users', [
          'auth' => $this->authInfo()
        ]);
    }

    /**
     * getData
     *
     * @return void
     * @access private
     */
    private function getData() : void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (isset($_POST['term'])) {
            echo json_encode($this->model->select($_POST['term']));
            return;
        }
        echo json_encode($this->model->select());
        return;
    }

    /**
     * updateStatus
     *
     * @return void
     * @access private
     */
    private function updateStatus() : void
    {
        try {
            if ($_POST && isset($_POST['id']) && isset($_POST['status'])) {
                if ($this->isAdmin($_POST['id'])) {
                    throw new \Exception('Administrator status ' .
                                         'cannot be changed');
                    return;
                }
                if ($this->model->update($_POST['id'],
                                         $_POST['status'],
                                         'status')) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo '{"result": true, "message": "Status changed"}';
                    return;
                } else {
                    throw new \Exception('Error changing status');
                }
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo '{"result": false, "message": "' . $e->getMessage() . '"}';
            return;
        }
    }

    /**
     * createAccount
     *
     * @return mixed
     * @access private
     */
    private function createAccount()
    {
        $message = [];
        $data    = [];
        try {
            if ($_POST) {
                $data           = $_POST;
                $check_password = $this->checkPassword($data['password1'],
                                                       $data['password2']);
                if ($check_password['result']) {
                    $data['username'] = $data['fullname'];
                    $data['password'] = $check_password['password'];
                    $data['message']  = &$message;
                } else {
                    $message = $check_password['message'];
                    return;
                }
                $this->instance->register($data['email'],
                                          $data['password'],
                                          $data['username'],
                                          function ($selector, $token)
                                          use ($data) {
                    // Send the link to the user's email
                    $data['message'] = $this->sendEmail($data['email'],
                                                        $selector,
                                                        $token,
                                                        'confirm-email') ?
                    ['success' => 'A link has been sent to your email'] :
                    ['error'   => 'Unable to send a link to this email'];
                });
            }
        } catch (\Delight\Auth\InvalidEmailException $e) {
            $message['error'] = 'Invalid email address';
            $message['input'] = 'email';
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            $message['error'] = 'Invalid password';
            $message['input'] = 'password1, password2';
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $message['error'] = 'User already exists';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            $data['message'] = $message;
            return $this->render('create_account', $data);
        }
    }

    /**
     * confirmEmail
     *
     * @return mixed
     * @access private
     */
    private function confirmEmail()
    {
        $message = [];
        $data    = [];
        try {
            if (isset($_GET['selector']) && isset($_GET['token'])) {
                $this->instance->confirmEmail($_GET['selector'],
                                              $_GET['token']);
                $message['success'] = 'Email address has been verified';
            }
        } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            $message['error'] = 'Invalid token';
        } catch (\Delight\Auth\TokenExpiredException $e) {
            $message['error'] = 'Token expired';
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $message['error'] = 'Email address already exists';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            $data['auth']    = $this->authInfo();
            $data['message'] = $message;
            return $this->render('confirm_email', $data);
        }
    }

    /**
     * forgotPassword
     *
     * @return mixed
     * @access private
     */
    private function forgotPassword()
    {
        $message = [];
        $data    = [];
        try {
            if ($_POST) {
                $data            = $_POST;
                $data['message'] = &$message;
                $this->instance->forgotPassword($data['email'],
                                                function ($selector, $token)
                                                use ($data) {
                    // Send the link to the user's email
                    $data['message'] = $this->sendEmail($data['email'],
                                                        $selector,
                                                        $token,
                                                        'reset-password') ?
                    ['success' => 'A link has been sent to your email'] :
                    ['error'   => 'Unable to send a link to this email'];
                });
            }
        } catch (\Delight\Auth\InvalidEmailException $e) {
            $message['error'] = 'Invalid email address';
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            $message['error'] = 'Email not verified';
        } catch (\Delight\Auth\ResetDisabledException $e) {
            $message['error'] = 'Password reset is disabled';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            $data['message'] = $message;
            return $this->render('forgot_password', $data);
        }
    }

    /**
     * resetPassword
     *
     * @return mixed
     * @access private
     */
    private function resetPassword()
    {
        $message = [];
        $data    = [];
        try {
            $this->instance->canResetPasswordOrThrow($_GET['selector'],
                                                     $_GET['token']);
            $data['selector'] = $_GET['selector'];
            $data['token']    = $_GET['token'];
            if ($_POST) {
                $data           = $_POST;
                $check_password = $this->checkPassword($data['password1'],
                                                       $data['password2']);
                if ($check_password['result']) {
                    $data['password'] = $check_password['password'];
                } else {
                    $message = $check_password['message'];
                    return;
                }
                $this->instance->resetPassword($data['selector'],
                                               $data['token'],
                                               $data['password']);
                $message['success'] = 'Password has been reset';
            }
        } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            $message['error'] = 'Invalid token';
        } catch (\Delight\Auth\TokenExpiredException $e) {
            $message['error'] = 'Token expired';
        } catch (\Delight\Auth\ResetDisabledException $e) {
            $message['error'] = 'Password reset is disabled';
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            $message['error'] = 'Invalid password';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            $data['message'] = $message;
            return $this->render('reset_password', $data);
        }
    }

    /**
     * changePassword
     *
     * @return mixed
     * @access private
     */
    private function changePassword()
    {
        $message = [];
        $data    = [];
        try {
            if ($_POST) {
                $data           = $_POST;
                $check_password = $this->checkPassword($data['password1'],
                                                       $data['password2']);
                if ($check_password['result']) {
                    $data['password'] = $check_password['password'];
                } else {
                    $message = $check_password['message'];
                    return;
                }
                $this->instance->changePassword($data['old_password'],
                                                $data['password']);
                $message['success'] = 'Password has been changed';
            }
        } catch (\Delight\Auth\NotLoggedInException $e) {
            $message['error'] = 'Not logged in';
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            $message['error'] = 'Invalid password';
            $message['input'] = 'old_password';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            $data['auth']    = $this->authInfo();
            $data['message'] = $message;
            return $this->render('change_password', $data);
        }
    }

    /**
     * changeEmail
     *
     * @return mixed
     * @access private
     */
    private function changeEmail()
    {
        $message = [];
        $data    = [];
        try {
            if ($_POST) {
                $data = $_POST;
                if ($this->instance->reconfirmPassword($data['password'])) {
                    $data['message'] = &$message;
                    $this->instance->changeEmail($data['new_email'],
                                                 function ($selector, $token)
                                                 use ($data) {
                        // Send the link to the user's email
                        $data['message'] = $this->sendEmail($data['new_email'],
                                                            $selector,
                                                            $token,
                                                            'confirm-email') ?
                        ['success' => 'The change will take effect ' .
                                      'as soon as the new email ' .
                                      'address has been confirmed'] :
                        ['error'   => 'Unable to send a link to this email'];
                    });
                } else {
                    $message['error'] = 'We can\'t say if the user ' .
                                        'is who they claim to be';
                }
            }
        } catch (\Delight\Auth\InvalidEmailException $e) {
            $message['error'] = 'Invalid email address';
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $message['error'] = 'Email address already exists';
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            $message['error'] = 'Account not verified';
        } catch (\Delight\Auth\NotLoggedInException $e) {
            $message['error'] = 'Not logged in';
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            $message['error'] = 'Too many requests';
        } finally {
            $data['auth']    = $this->authInfo();
            $data['message'] = $message;
            return $this->render('change_email', $data);
        }
    }

    /**
     * sendEmail
     *
     * @param mixed $args
     * @return bool
     * @access private
     */
    private function sendEmail(...$args) : bool
    {
        $recipient = $args[0];
        $selector  = $args[1];
        $token     = $args[2];
        $type      = $args[3];
        $subject   = Language::translate($type === 'confirm-email' ?
                                    'Confirmation link'  :
                                    'Password reset link');
        $link      = Settings::host() . $this->route() . $type .
                     '?selector=' . \urlencode($selector) .
                     '&token=' . \urlencode($token);
        $header    = "From: " .
                     explode('.', ucfirst($_SERVER['SERVER_NAME']))[0] . "\r\n";
        $header   .= "MIME-Version: 1.0\r\n";
        $header   .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message   = '<h1>' . $subject . '</h1>' .
                     '<h2><a href="' . $link . '" target="_blank">' . $link .
                     '</a></h2>';
        return mail($recipient, $subject, $message, $header) ? true : false;
    }

    /**
     * changeName
     *
     * @return mixed
     * @access private
     */
    private function changeName()
    {
        $message      = [];
        $data         = [];
        $data['auth'] = $this->authInfo();
        try {
            if ($_POST) {
                if (isset($_POST['new_name'])) {
                    $data['new_name'] = trim($_POST['new_name']);
                    if (preg_match('|^[\pL\s]+$|u', $data['new_name']) &&
                        $data['new_name']) {
                        if ($this->model->update($data['auth']['id'],
                                                 $data['new_name'],
                                                 'username')) {
                            $message['success']        = 'Name changed';
                            $_SESSION['auth_username'] = $data['new_name'];
                        } else {
                            throw new \Exception('Error changing name');
                        }
                    } else {
                        throw new \Exception('Invalid name');
                    }
                }
            }
        } catch (\Exception $e) {
            $message['error'] = $e->getMessage();
            $message['input'] = 'new_name';
        } finally {
            $data['message'] = $message;
            return $this->render('change_name', $data);
        }
    }

    /**
     * checkPassword
     *
     * @param string $password1
     * @param string $password2
     * @return array
     * @access private
     */
    private function checkPassword($password1, $password2) : array
    {
        $message = [];
        if ($password1 && $password2) {
            if (\strlen($password1) < 8) {
                $message['error'] = 'Password must be at least ' .
                                    '8 characters long';
                $message['input'] = 'password1';
                return ['message' => $message, 'result' => false];
            }
            if ($password1 === $password2) {
                return ['password' => $password2, 'result' => true];
            } else {
                $message['error'] = 'Passwords do not match';
                $message['input'] = 'password1, password2';
                return ['message' => $message, 'result' => false];
            }
        }
        $message['error'] = 'Invalid password';
        $message['input'] = 'password1, password2';
        return ['message' => $message, 'result' => false];
    }

    /**
     * logOut
     *
     * @return mixed
     * @access private
     */
    private function logOut() : mixed
    {
        $this->instance->logOut();
        return header('Location: ' . $this->route());
    }

    /**
     * getRoute
     *
     * @return array
     * @access private
     */
    private function getRoute() : array
    {
        if (!isset($_SESSION['auth_route']) &&
            !isset($_SESSION['auth_redirect'])) {
            $route = $this->model->selectRoute()[0];
            $_SESSION['auth_route']    = $route['base'];
            $_SESSION['auth_redirect'] = $route['dest'];
        }
        return [
          'route'    => $_SESSION['auth_route'],
          'redirect' => $_SESSION['auth_redirect']
        ];
    }

    /**
     * settings
     *
     * @return mixed
     * @access private
     */
    private function settings() : mixed
    {
        $data = [];
        if ($_POST) {
            $data['base_route'] = trim($_POST['base_route']);
            $data['dest_route'] = trim($_POST['dest_route']);
            if (!$data['base_route'] ||
                substr($data['base_route'],0 ,1) !== '/') {
                $data['message']['error'] = 'Invalid base route';
                $data['message']['input'] = 'base_route';
            } else if (!$data['dest_route'] ||
                       substr($data['dest_route'],0 ,1) !== '/') {
                $data['message']['error'] = 'Invalid destination route';
                $data['message']['input'] = 'destination_route';
            } else {
                if ($this->model->updateRoute($data['base_route'],
                                              $data['dest_route'])) {
                    $_SESSION['auth_route']    = $data['base_route'];
                    $_SESSION['auth_redirect'] = $data['dest_route'];
                    $data['message']['success'] = 'Saved successfully!';
                } else {
                    $data['message']['error'] = "It wasn't possible to save!";
                }
            }
        }
        return $this->render('settings', $data);
    }
}
