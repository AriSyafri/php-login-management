<?php 

namespace ProgrammerZamanNow\Belajar\PHP\MVC\App {
   
    function header(string $value) {
        echo $value;
    }
}

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Controller {
        
    use PHPUnit\Framework\TestCase;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;

    class UserControllerTest extends TestCase {
        
        private UserController $userController;
        private UserRepository $userRepository;

        public function setUp(): void {

            $this->userController = new UserController();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();

            putenv("mode=test");
        }

        public function testRegister() {
            $this->userController->register();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register New User]");
        }

        public function testPostRegisterSucces() {
            $_POST['id'] = 'ari';
            $_POST['name'] = 'Ari';
            $_POST['password'] = 'rahasia';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Location: /users/login]");
        }

        public function testPostRegisterValidationError() {
            $_POST['id'] = '';
            $_POST['name'] = '';
            $_POST['password'] = '';

            $this->userController->postRegister();

            
            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[Id, Name, Password can not blank]");

        }

        public function testPostRegisterDuplicate() {

            $user = new User();
            $user->id = "ari";
            $user->name = "Ari";
            $user->password = "rahasia";
            
            $this->userRepository->save($user);

            $_POST['id'] = 'ari';
            $_POST['name'] = 'Ari';
            $_POST['password'] = 'rahasia';

            $this->userController->postRegister();

            
            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[User id already exist]");

        }

        public function testLogin() {
            $this->userController->login();

            $this->expectOutputRegex("[Login user]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");

        }

        public function testLoginSucces() {

            $user = new User();
            $user->id = "ari";
            $user->name = "Ari";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            
            $this->userRepository->save($user);

            $_POST['id'] = 'ari';
            $_POST['password'] = 'rahasia';

            $this->userController->postLogin();

            $this->expectOutputRegex("[Location: /]");

        }

        public function testLoginValidationError() {

            $_POST['id'] = '';
            $_POST['password'] = '';

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id, Password can not blank]");


        }

        public function testLoginUserNotFound() {

            $_POST['id'] = 'notfound';
            $_POST['password'] = 'notfound';

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");

        }

        public function testLoginWrongPassword() {

            $user = new User();
            $user->id = "ari";
            $user->name = "Ari";
            $user->password = password_hash("rahasia", PASSWORD_BCRYPT);

            
            $this->userRepository->save($user);


            $_POST['id'] = 'ari';
            $_POST['password'] = 'salah';

            $this->userController->postLogin();

            $this->expectOutputRegex("[Login User]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id or password is wrong]");

        }

    }
}
