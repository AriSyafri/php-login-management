<?php 

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller;

use ProgrammerZamanNow\Belajar\PHP\MVC\App\View;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidationException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Service\UserService;

class UserController {

    private UserService $userService;

    public function __construct() {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $this->userService = new UserService($userRepository);
    }

    // menampilkan halaman registrasi
    public function register() {
        View::render('User/register', [
            'title' => 'Register New User'
        ]);
    }

    // aksi register
    public function postRegister() {

        $request = new UserRegisterRequest();
        $request->id = $_POST['id'];
        $request->name = $_POST['name'];
        $request->password = $_POST['password'];

        try {
            $this->userService->register($request);
            // redirect to /users/login
            View::redirect('/users/login');
        } catch (ValidationException $exception) {
            View::render('User/register', [
                'title' => 'Register new User',
                'error' => $exception->getMessage()
            ]);
        }
    }

    // menampilkan halaman login
    public function login(){
        View::render('User/login', [
            "title" => "Login user"
        ]);
        
    }

    public function postLogin(){
        $request = new UserLoginRequest();
        $request->id = $_POST['id'];
        $request->password = $_POST['password'];

        try {
            $this->userService->login($request);
            View::redirect('/');
        } catch (ValidationException $exception) {
            View::render('User/login' , [
                'title' => 'Login User',
                'error' => $exception->getMessage()
            ]);
        }


        $this->userService->login($request);

    }

}