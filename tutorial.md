# API Development Tutorial

## JWT Installation

1. Add tymon/jwt-auth in `composer.json` and run `composer update`

	```
	"require": {
	    "tymon/jwt-auth": "0.5.*"
	}
	```

2. Add `providers` in `config/app.php`

	```
    /*
     * JWT
     */
    Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class
    ```

3. Add `aliases` in `config/app.php`

	```
	'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
    'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class
    ```

4. Publish the package configuration

	```
	php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"
	```

5. Generate JWT key

	```
	php artisan jwt:generate
	```

## CORS Installation

1. Install `barryvdh/laravel-cors` package via composer

	```
	composer require barryvdh/laravel-cors
	```

2. Add `providers` in `config/app.php`

	```
	/*
     * CORS
     */
	Barryvdh\Cors\ServiceProvider::class
	```

3. Publish the package configuration

	```
	php artisan vendor:publish --provider="Barryvdh\Cors\ServiceProvider"
	```

4. Add to middleware group for `api` in `app/Http/Kernel.php`

	```
	\Barryvdh\Cors\Middleware\HandleCors::class
	```

5. Disable CSRF for API in `App\Http\Middleware\VerifyCsrfToken.php`

	```
	protected $except = [
	  'api/*'
	];
	```

## Signup & Login

1. Do migration

	```
	php artisan migrate
	```

2. Create route for API
	
	```
	Route::group([
		'middleware' => 'api',
		'prefix' => 'api', 
		'namespace' => '\Api\V1'
		],function(){

		Route::group(['prefix' => 'v1'],function(){
			Route::post('/login','Auth\AuthenticateController@login');
			Route::post('/signup','Auth\AuthenticateController@signup');
		});
	});
	```

3. Create AuthenticateController

	```
	php artisan make:controller Api/V1/Auth/AuthenticateController
	```

4. Open up `app/Http/Controller/Api/V1/Auth/AuthenticateController` and add in two methods below

	```
	public function login(Request $request)
    {
		$credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }

    public function signup(Request $request)
    {
    	$credentials = $request->only('name', 'email', 'password');
    	$credentials['password'] = bcrypt($credentials['password']);

		try {
		   $user = User::create($credentials);
		} catch (Exception $e) {
		   return response()->json(['error' => 'User already exists.']);
		}

		$token = JWTAuth::fromUser($user);

		return response()->json(compact('token'));
    }
    ```

## Managing Exception - Token expired, Invalid token & Missing Token

1. Add JWT Exception Handler at `app/Exceptions/Handler.php` in `render()` method

	```
	if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
        return response()->json(['error' => 'Token expired'], $e->getStatusCode());
    } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
        return response()->json(['error' => 'Invalid token provided'], $e->getStatusCode());
    } else if ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
        return response()->json(['error' => 'Token is missing.'], $e->getStatusCode());
    }
    ```
## Test Your API

Install API Test Tool [Postman](http://getpostman.com/) and open it up once you're done with installation. You may want to go through Postman Documentation on how to use Postman [here](https://www.getpostman.com/docs/).

### User signup

1. Change the method to `POST`

2. Enter the URL to your API, in this tutorial, I have `http://[your-api-domain]/api/v1/signup`

3. On the Headers tab, add header types as following in key-value editor

	- Accept : application/json

4. On the Body tab, add the following details in key-value editor to create new account

	- name : [your name]
	- email : [your email]
	- password : [your password]

5. Once you're done with all steps above, click on `Send` button. You should get your result in Response panel - you will get a token upon successful registration, otherwise you will get error message.

### User Login

Similar with steps in signup, except the URL need to change to `http://[your-api-domain]/api/v1/login` and on the Body tab, remove the `name` key. Once you're done, you can submit the request. You should receive the result - which is a token(copy the token value, we need for next section).

### Get User Profile

1. Change the method to `GET`

2. Enter the URL to get User Profile - `http://[your-api-domain]/api/v1/user/profile`

3. In Headers tab, add one more key:

	- Authorization : Bearer [token]

4. Click on Send once you are done. You should received the result like the following:

	```
	{
	  "user": {
	    "id": 1,
	    "name": "Lorem Ipsum",
	    "email": "lorem@ipsum.com",
	    "created_at": "2016-07-01 04:05:41",
	    "updated_at": "2016-07-01 04:05:41"
	  }
	}
	```