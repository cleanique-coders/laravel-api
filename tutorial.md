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