<?php 

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidationException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\SessionRepository;
use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;

class UserServiceTest extends TestCase {
    private UserService $userService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp():void {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);
        $this->sessionRepository = new SessionRepository($connection);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testRegisterSucces() {
        $request = new UserRegisterRequest();
        $request->id = "ari";
        $request->name = "Ari";
        $request->password = "rahasia";

        $response = $this->userService->register($request);

        
        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        self::assertNotEquals($request->password, $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));


    }

    public function testRegisterFailed() {

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "";
        $request->name = "";
        $request->password = "";

        $this->userService->register($request);

    }

    public function testRegisterDuplicate() {
        $user = new User();
        $user->id = "ari";
        $user->name = "Ari";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);
        $request = new UserRegisterRequest();
        $request->id = "ari";
        $request->name = "Ari";
        $request->password = "rahasia";

        $this->userService->register($request);

    }

    public function testLoginNotFound() {
        
        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "ari";
        $request->password = "rahasia";

        $this->userService->login($request);
    }

    public function testLoginWrongPassword() {
        $user = new User();
        $user->id = "ari";
        $user->password = password_hash("ari", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "ari";
        $request->password = "salah";
        $this->userService->login($request);
    
    }

    public function testLoginSuccess() {
        $user = new User();
        $user->id = "ari";
        $user->password = password_hash("ari", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "ari";
        $request->password = "ari";

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSucces() {
        
        $user = new User();
        $user->id = "ari";
        $user->name = "Ari";
        $user->password = password_hash("ari", PASSWORD_BCRYPT);
        $this->userRepository->save($user);
        
        $request = new UserProfileUpdateRequest();
        $request->id = "ari";
        $request->name = "Budi";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->name, $result->name);

    }

    public function testUpdateValidationError() {

        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";

        $this->userService->updateProfile($request);

    }

    public function testUpdateNotFound() {

        $this->expectException(ValidationException::class);

        
        $request = new UserProfileUpdateRequest();
        $request->id = "ari";
        $request->name = "Budi";

        $this->userService->updateProfile($request);
    }


    public function testUpdatePasswordSucces() {

        $user = new User();
        $user->id = "ari";
        $user->name = "Ari";
        $user->password = password_hash("ari", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request =  new UserPasswordUpdateRequest();
        $request->id = "ari";
        $request->oldPassword = "ari";
        $request->newPassword = "new";

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);
        self::assertTrue(password_verify($request->newPassword, $result->password));


    }

    public function testUpdatePasswordValidationError() {

        $this->expectException(ValidationException::class);

        $request =  new UserPasswordUpdateRequest();
        $request->id = "ari";
        $request->oldPassword = "";
        $request->newPassword = "";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongOldPassword() {

        $this->expectException(ValidationException::class);
        
        $user = new User();
        $user->id = "ari";
        $user->name = "Ari";
        $user->password = password_hash("ari", PASSWORD_BCRYPT);
        $this->userRepository->save($user);

        $request =  new UserPasswordUpdateRequest();
        $request->id = "ari";
        $request->oldPassword = "salah";
        $request->newPassword = "new";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFound() {
        $this->expectException(ValidationException::class);


        $request =  new UserPasswordUpdateRequest();
        $request->id = "ari";
        $request->oldPassword = "ari";
        $request->newPassword = "new";

        $this->userService->updatePassword($request);
    }



}