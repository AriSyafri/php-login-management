<?php 

    namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

    use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Domain\User;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidationException;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginResponse;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterResponse;
    use ProgrammerZamanNow\Belajar\PHP\MVC\Repository\UserRepository;


    class UserService {
        
        private UserRepository $userRepository;

        public function __construct(UserRepository $userRepository){
            $this->userRepository = $userRepository;
        }

        public function register(UserRegisterRequest $request): UserRegisterResponse { 
            $this->validateUserRegistrationRequest($request);

            try {   
                Database::beginTransaction();
                $user = $this->userRepository->findById($request->id);
                if($user != null) {
                    throw new ValidationException("User id already exist");
                }

                $user = new User();
                $user->id = $request->id;
                $user->name = $request->name;
                $user->password = password_hash($request->password, PASSWORD_BCRYPT);

                $this->userRepository->save($user);

                $response = new UserRegisterResponse();
                $response->user = $user;

                Database::commitTransaction();
                return $response;
            } catch (\Exception $exception) {
                Database::rollbackTransaction();
                throw $exception;
            }

        }

        private function validateUserRegistrationRequest(UserRegisterRequest $request){
            if($request->id == null || $request->name == null || $request->password == null || 
                trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == "") {
                throw new ValidationException("Id, Name, Password can not blank");
            }
        }

        public function login(UserLoginRequest $request): UserLoginResponse {
            $this->validateUserLoginRequest($request);

            $user = $this->userRepository->findById($request->id);
            if($user == null) {
                throw new ValidationException("Id or password is wrong");
            }

            if(password_verify($request->password, $user->password)) {
                $response = new UserLoginResponse();
                $response->user = $user;
                return $response;
            } else {
                throw new ValidationException("Id or password is wrong");
            }

        }

        public function validateUserLoginRequest(UserLoginRequest $request) {
            if($request->id == null || $request->password == null || 
                trim($request->id) == "" || trim($request->password) == "") {
                throw new ValidationException("Id, Password can not blank");
            }
        }
    }


?>